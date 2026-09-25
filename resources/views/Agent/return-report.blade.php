@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Return Report</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="frmDate"
                        min="{{ $year_start }}" max="{{ $year_end }}" value="{{ $year_start }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="toDate"
                        min="{{ $year_start }}" max="{{ $year_end }}"
                        value="{{ min($year_end, date('Y-m-d')) }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="searchBtn">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="printBtn" disabled>
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="table-responsive report-scroll">
                <table id="reportTable" class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Return No</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                        <tr><td colspan="8" class="text-center text-muted">Select dates and click Search</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const searchUrl = "{{ route('agent.report.return.search') }}";
    const orgName = @json($org_name);
    const agentName = @json($agent_name);
    let printRows = [];

    function printReport() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        const frm = siDate.toDisplay($('#frmDate').val());
        const to = siDate.toDisplay($('#toDate').val());
        let body = '';
        printRows.forEach((row, idx) => {
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Doc_No ?? ''}</td>
                <td>${siDate.toDisplay(row.Doc_Date)}</td>
                <td>${row.Return_Type ?? ''}</td>
                <td>${row.Prod_Code ?? ''}</td>
                <td>${row.Prod_ShortNm ?? ''}</td>
                <td style="text-align:right;">${row.Quantity ?? 0}</td>
                <td>${row.Unit_Name ?? ''}</td>
            </tr>`;
        });
        siPrint(`<!DOCTYPE html><html><head><title>Return Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Return Report</h4>
            <p>Agent: ${agentName || ''} &nbsp;|&nbsp; From: ${frm} &nbsp; To: ${to}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Return No</th><th>Date</th><th>Type</th>
                    <th>Item Code</th><th>Item Name</th><th>Qty</th><th>Unit</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`);
    }

    $('#printBtn').on('click', printReport);

    $('#searchBtn').on('click', function () {
        const frmDate = $('#frmDate').val();
        const toDate  = $('#toDate').val();
        if (!frmDate || !toDate) {
            Swal.fire('Error', 'Select from date and to date', 'error');
            return;
        }
        if (frmDate > toDate) {
            Swal.fire('Error', 'From date cannot be after to date', 'error');
            return;
        }

        printRows = [];
        $('#printBtn').prop('disabled', true);
        $('#reportBody').html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');

        $.get(searchUrl, { frm_date: frmDate, to_date: toDate }, function (data) {
            if (!data.length) {
                $('#reportBody').html('<tr><td colspan="8" class="text-center text-muted">No records found</td></tr>');
                return;
            }
            printRows = data;
            let html = '';
            data.forEach((row, idx) => {
                html += `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Doc_No ?? ''}</td>
                    <td>${siDate.toDisplay(row.Doc_Date)}</td>
                    <td>${row.Return_Type ?? ''}</td>
                    <td>${row.Prod_Code ?? ''}</td>
                    <td>${row.Prod_ShortNm ?? ''}</td>
                    <td class="text-end">${row.Quantity ?? 0}</td>
                    <td>${row.Unit_Name ?? ''}</td>
                </tr>`;
            });
            $('#reportBody').html(html);
            $('#printBtn').prop('disabled', false);
        }).fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report', 'error');
            $('#reportBody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load</td></tr>');
        });
    });

    if (new URLSearchParams(window.location.search).get('today') === '1') {
        const today = @json(date('Y-m-d'));
        $('#frmDate').val(today);
        $('#toDate').val(today);
        $('#searchBtn').trigger('click');
    }
</script>
@endpush
