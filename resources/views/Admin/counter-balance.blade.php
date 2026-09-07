@extends('Dashboard.Layouts.layout')
@section('content')
<div class="page-wrapper">
<div class="content container-fluid">
    <div class="mb-3">
        <h6 class="ps-2">Counter Balance</h6>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3" id="formTitle">Add Counter Balance</h6>
                    <input type="hidden" id="rowId">
                    <div class="mb-3">
                        <label class="form-label">Counter<span class="text-danger">*</span></label>
                        <select class="form-select" id="counterId">
                            <option value="">Select Counter</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->User_Id }}">{{ $user->User_FullName }} ({{ $user->User_Code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Balance<span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="balance" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="balDate"
                            value="{{ $year_start }}" readonly>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Counter Balance List</h6>
                    <div class="table-responsive">
                        <table id="balanceTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Counter</th>
                                    <th class="text-end">Balance</th>
                                    <th>Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $key => $row)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $row->Counter_Name }} ({{ $row->Counter_Code }})</td>
                                    <td class="text-end">{{ number_format((float) $row->balance, 2) }}</td>
                                    <td>{{ dmy($row->date) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm editRow"
                                            data-id="{{ $row->id }}"
                                            data-counter="{{ $row->counter_id }}"
                                            data-balance="{{ $row->balance }}">Edit</button>
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
    $('#balanceTable').DataTable();

    $(document).on('click', '.editRow', function () {
        $('#rowId').val($(this).data('id'));
        $('#counterId').val($(this).data('counter'));
        $('#balance').val($(this).data('balance'));
        $('#balDate').val(@json($year_start));
        $('#formTitle').text('Edit Counter Balance');
        $('#saveBtn').text('Update');
    });

    $('#cancelBtn').on('click', clearForm);

    $('#saveBtn').click(function () {
        if (!$('#counterId').val()) {
            Swal.fire('Validation Error', 'Select counter', 'error');
            return;
        }
        if ($('#balance').val() === '' || isNaN($('#balance').val())) {
            Swal.fire('Validation Error', 'Enter balance', 'error');
            return;
        }
        $(this).prop('disabled', true).text('Saving...');
        const id = $('#rowId').val();
        $.ajax({
            url: "{{ route('counter-balance.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                counter_id: $('#counterId').val(),
                balance: $('#balance').val()
            },
            success: res => {
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            },
            error: xhr => {
                $('#saveBtn').prop('disabled', false).text(id ? 'Update' : 'Save');
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0][0]
                    : (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    function clearForm() {
        $('#rowId').val('');
        $('#counterId').val('');
        $('#balance').val('');
        $('#balDate').val(@json($year_start));
        $('#formTitle').text('Add Counter Balance');
        $('#saveBtn').prop('disabled', false).text('Save');
    }
});
</script>
@endpush
