@extends('Dashboard.Layouts.layout')
@section('content')
<div class="page-wrapper">
<div class="content container-fluid">
    <div class="mb-3">
        <h6 class="ps-2">Unit Master</h6>
    </div>
    <div class="row">
        <!-- Left: Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3" id="formTitle">Add Unit</h6>
                    <input type="hidden" id="unitId">
                    <div class="mb-3">
                        <label class="form-label">Unit Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="unitName" maxlength="25" autocomplete="off">
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="saveUnit">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Table -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Unit Master List</h6>
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th><th>Unit Name</th><th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($units as $key => $unit)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $unit->Unit_Name }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm editRow"
                                            data-id="{{ $unit->Unit_Id }}"
                                            data-name="{{ $unit->Unit_Name }}">Edit</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#unitTable').DataTable();

    $(document).on('click', '.editRow', function () {
        $('#unitId').val($(this).data('id'));
        $('#unitName').val($(this).data('name'));
        $('#formTitle').text('Edit Unit');
        $('#saveUnit').text('Update');
    });

    $('#cancelBtn').on('click', clearForm);

    $('#saveUnit').click(function () {
        if (!$('#unitName').val().trim()) { Swal.fire('Validation Error', 'Unit Name required', 'error'); return; }
        $(this).prop('disabled', true).text('Saving...');
        let id = $('#unitId').val();
        $.ajax({
            url: "{{ route('unit-master.store') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", unit_id: id, unit_name: $('#unitName').val().trim() },
            success: res => {
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            },
            error: xhr => {
                $('#saveUnit').prop('disabled', false).text(id ? 'Update' : 'Save');
                let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    function clearForm() {
        $('#unitId').val('');
        $('#unitName').val('');
        $('#formTitle').text('Add Unit');
        $('#saveUnit').prop('disabled', false).text('Save');
    }
});
</script>
@endpush
