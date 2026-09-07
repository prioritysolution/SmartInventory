@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Indent Report</h6>
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
                <table id="indentTable" class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Indent No</th>
                            <th>Date</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-end">Req Qty</th>
                            <th class="text-end">Issue Qty</th>
                            <th class="text-end">Reject Qty</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="indentBody">
                        <tr><td colspan="10" class="text-center text-muted">Select dates and click Search</td></tr>
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
    const searchUrl = "{{ route('agent.report.indent.search') }}";
    const orgName = @json($org_name);
    const agentName = @json($agent_name);
    let printRows = [];

    function parseItems(raw) {
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        try { return JSON.parse(raw); } catch (e) { return []; }
    }

    function flattenRows(data) {
        const rows = [];
        (data || []).forEach(row => {
            const items = parseItems(row.Item_Data);
            const issued = Number(row.Is_Issued) === 1;
            const status = issued ? 'Issued' : 'Pending';
            const date = siDate.toDisplay(row.Indent_Date);
            if (!items.length) {
                rows.push({
                    indent_no: row.Indent_No ?? '',
                    date: date,
                    prod_code: '',
                    prod_name: '',
                    qty: '',
                    issue_qty: '',
                    reject_qty: '',
                    unit: '',
                    status: status
                });
                return;
            }
            items.forEach(item => {
                const reqQty = parseFloat(item.Quantity ?? 0) || 0;
                const rejectQty = parseFloat(item.Reject_Qty ?? 0) || 0;
                const issueQty = item.Issue_Qty != null ? (parseFloat(item.Issue_Qty) || 0) : Math.max(0, reqQty - rejectQty);
                rows.push({
                    indent_no: row.Indent_No ?? '',
                    date: date,
                    prod_code: item.Prod_Code ?? '',
                    prod_name: item.Prod_ShortNm ?? '',
                    qty: reqQty,
                    issue_qty: issued ? issueQty : 0,
                    reject_qty: issued ? rejectQty : 0,
                    unit: item.Unit_Name ?? '',
                    status: status
                });
            });
        });
        return rows;
    }

    function printIndent() {
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
                <td>${row.indent_no}</td>
                <td>${row.date}</td>
                <td>${row.prod_code}</td>
                <td>${row.prod_name}</td>
                <td style="text-align:right;">${row.qty}</td>
                <td style="text-align:right;">${row.issue_qty}</td>
                <td style="text-align:right;">${row.reject_qty}</td>
                <td>${row.unit}</td>
                <td>${row.status}</td>
            </tr>`;
        });
        const w = window.open('', '_blank');
        w.document.write(`<!DOCTYPE html><html><head><title>Indent Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Indent Report</h4>
            <p>Agent: ${agentName || ''} &nbsp;|&nbsp; From: ${frm} &nbsp; To: ${to}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Indent No</th><th>Date</th>
                    <th>Item Code</th><th>Item Name</th>
                    <th>Req Qty</th><th>Issue Qty</th><th>Reject Qty</th>
                    <th>Unit</th><th>Status</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`);
        w.document.close();
        w.focus();
        w.print();
    }

    $('#printBtn').on('click', printIndent);

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
        $('#indentBody').html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');

        $.get(searchUrl, { frm_date: frmDate, to_date: toDate }, function (data) {
            printRows = flattenRows(data);
            if (!printRows.length) {
                $('#indentBody').html('<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>');
                return;
            }
            let html = '';
            printRows.forEach((row, idx) => {
                const statusHtml = row.status === 'Issued'
                    ? '<span class="badge bg-success">Issued</span>'
                    : '<span class="badge bg-warning text-dark">Pending</span>';
                html += `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.indent_no}</td>
                    <td>${row.date}</td>
                    <td>${row.prod_code}</td>
                    <td>${row.prod_name}</td>
                    <td class="text-end">${row.qty}</td>
                    <td class="text-end">${row.issue_qty}</td>
                    <td class="text-end">${row.reject_qty}</td>
                    <td>${row.unit}</td>
                    <td>${statusHtml}</td>
                </tr>`;
            });
            $('#indentBody').html(html);
            $('#printBtn').prop('disabled', false);
        }).fail(function (xhr) {
            const msg = xhr.responseJSON?.message || 'Failed to load indent report. Run USP_AGENT_REPORTS.sql on the organisation database.';
            Swal.fire('Error', msg, 'error');
            $('#indentBody').html('<tr><td colspan="10" class="text-center text-danger">Failed to load</td></tr>');
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
