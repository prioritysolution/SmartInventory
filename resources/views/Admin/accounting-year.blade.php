@extends('Dashboard.Layouts.layout')
@section('content')
<div class="page-wrapper">
<div class="content container-fluid">
    <div class="mb-3">
        <h6 class="ps-2">Accounting Year</h6>
    </div>
    <div class="row">
        <!-- Left: Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Add Accounting Year</h6>
                    <input type="hidden" id="yearDesc">
                    <div class="mb-3">
                        <label class="form-label">Start Date (April 1st)<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="yearStart">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="yearEnd" readonly>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="saveYear">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Table -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Accounting Year List</h6>
                    <div class="table-responsive">
                        <table id="yearTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Description</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($years as $key => $year)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $year->Year_Desc ?? '' }}</td>
                                    <td>{{ dmy($year->Year_Start) }}</td>
                                    <td>{{ dmy($year->Year_End) }}</td>
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
    $('#yearTable').DataTable();

    $('#yearStart').on('change', function () {
        let val = $(this).val();
        if (!val) { $('#yearEnd').val(''); $('#yearDesc').val(''); return; }
        let d = new Date(val);
        let endYear = d.getFullYear() + 1;
        $('#yearEnd').val(endYear + '-03-31');
        $('#yearDesc').val(d.getFullYear() + '-' + endYear);
    });

    $('#cancelBtn').on('click', clearForm);

    $('#saveYear').click(function () {
        if (!IS_ADMIN) {
            Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'warning');
            return;
        }
        if (!$('#yearStart').val()) { Swal.fire('Validation Error', 'Start Date required', 'error'); return; }
        $(this).prop('disabled', true).text('Saving...');
        $.ajax({
            url: "{{ route('accounting-year.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                year_start: $('#yearStart').val(),
                year_end: $('#yearEnd').val(),
                year_desc: $('#yearDesc').val()
            },
            success: res => {
                Swal.fire('Success', res.message, 'success').then(() => {
                    window.location.href = "{{ route('login-index') }}";
                });
            },
            error: xhr => {
                $('#saveYear').prop('disabled', false).text('Save');
                let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    function clearForm() {
        $('#yearStart').val('');
        $('#yearEnd').val('');
        $('#yearDesc').val('');
        $('#saveYear').prop('disabled', false).text('Save');
    }
});
</script>
@endpush
