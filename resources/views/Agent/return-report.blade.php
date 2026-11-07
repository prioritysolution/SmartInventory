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
                    <input type="date" class="form-control" id="frmDate" value="{{ $year_start }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="toDate"
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-success w-100" id="downloadBtn" disabled>
                        <i class="fas fa-download me-1"></i> Download
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                        <tr><td colspan="9" class="text-center text-muted">Select dates and click Search</td></tr>
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

    function buildReturnCsv(rows) {
        const frm = siDate.toDisplay($('#frmDate').val());
        const to = siDate.toDisplay($('#toDate').val());
        const meta = [
            ['Organization', orgName || 'Smart Inventory'],
            ['Report', 'Return Report'],
            ['Agent', agentName || ''],
            ['Period', (frm && to ? `From ${frm} To ${to}` : '')],
            ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
        ];

        const headers = ['Sl', 'Return No', 'Date', 'Type', 'Item Code', 'Item Name', 'Qty', 'Unit', 'Status'];

        let totQty = 0;
        const dataRows = (rows || []).map(function(row, idx) {
            const q = parseFloat(row.Quantity) || 0;
            totQty += q;
            return [
                String(idx + 1),
                row.Doc_No || '',
                siDate.toDisplay(row.Doc_Date),
                row.Return_Type || '',
                row.Prod_Code || '',
                row.Prod_ShortNm || '',
                siFmtQty(q),
                row.Unit_Name || '',
                row.Status || ''
            ];
        });

        const totals = ['Total', '', '', '', '', '', siFmtQty(totQty), '', ''];

        return siBuildCsv({
            meta: meta,
            headers: headers,
            rows: dataRows,
            totals: totals
        });
    }

    function buildReturnHtml(rows) {
        const frm = siDate.toDisplay($('#frmDate').val());
        const to = siDate.toDisplay($('#toDate').val());
        let body = '';
        let totQty = 0;
        (rows || []).forEach((row, idx) => {
            const q = parseFloat(row.Quantity) || 0;
            totQty += q;
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Doc_No ?? ''}</td>
                <td>${siDate.toDisplay(row.Doc_Date)}</td>
                <td>${row.Return_Type ?? ''}</td>
                <td>${row.Prod_Code ?? ''}</td>
                <td>${row.Prod_ShortNm ?? ''}</td>
                <td style="text-align:right;">${siFmtQty(q)}</td>
                <td>${row.Unit_Name ?? ''}</td>
                <td>${row.Status ?? ''}</td>
            </tr>`;
        });
        body += `<tr>
            <th colspan="6" style="text-align:right;">Total</th>
            <th style="text-align:right;">${siFmtQty(totQty)}</th>
            <th colspan="2"></th>
        </tr>`;

        return `<!DOCTYPE html><html><head><title>Return Report</title>
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
                    <th>Item Code</th><th>Item Name</th><th>Qty</th><th>Unit</th><th>Status</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`;
    }

    function downloadReturnZip() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (window.Swal) {
            Swal.fire({
                title: 'Generating ZIP...',
                text: 'Packaging records',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });
        }
        setTimeout(function() {
            try {
                const csvContent = buildReturnCsv(printRows);
                const htmlContent = buildReturnHtml(printRows);
                const safeName = 'Return_Report_' + siDateStamp();
                const zipName = safeName + '.zip';
                const files = [
                    { name: safeName + '.csv', content: csvContent },
                    { name: safeName + '.html', content: htmlContent }
                ];
                siDownloadZip(zipName, files);
                if (window.Swal) Swal.close();
            } catch (e) {
                if (window.Swal) {
                    Swal.close();
                    Swal.fire('Error', 'Failed to generate ZIP: ' + (e.message || e), 'error');
                }
            }
        }, 50);
    }

    function printReport() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (printRows.length > 1000) {
            siWarnPrintLimit(printRows.length, downloadReturnZip);
            return;
        }
        const html = buildReturnHtml(printRows);
        siPrint(html);
    }

    $('#printBtn').on('click', printReport);
    $(document).on('click', '#downloadBtn', function() {
        downloadReturnZip();
    });

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
        $('#downloadBtn').prop('disabled', true);
        if (window._returnLazy) window._returnLazy.destroy();
        window._returnLazy = SiLazyScroll.create({
            scrollEl: $('#reportBody').closest('.report-scroll'),
            tbody: '#reportBody',
            url: searchUrl,
            pageSize: 40,
            showEndRow: false,
            colSpan: 9,
            params: function () { return { frm_date: frmDate, to_date: toDate }; },
            emptyHtml: '<tr><td colspan="9" class="text-center text-muted">No records found</td></tr>',
            renderRow: function (row, idx) {
                const status = row.Status || '';
                const statusHtml = status === 'Pending'
                    ? '<span class="badge bg-warning text-dark">Pending</span>'
                    : (status === 'Accepted'
                        ? '<span class="badge bg-success">Accepted</span>'
                        : (status
                            ? '<span class="badge bg-info text-dark">' + status + '</span>'
                            : ''));
                return `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Doc_No ?? ''}</td>
                    <td>${siDate.toDisplay(row.Doc_Date)}</td>
                    <td>${row.Return_Type ?? ''}</td>
                    <td>${row.Prod_Code ?? ''}</td>
                    <td>${row.Prod_ShortNm ?? ''}</td>
                    <td class="text-end">${siFmtQty(row.Quantity)}</td>
                    <td>${row.Unit_Name ?? ''}</td>
                    <td>${statusHtml}</td>
                </tr>`;
            },
            onChunk: function (rows, payload, reset) {
                if (reset) printRows = [];
                printRows = printRows.concat(rows);
                $('#printBtn').prop('disabled', printRows.length === 0);
                $('#downloadBtn').prop('disabled', printRows.length === 0);
            },
            onError: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report', 'error');
            }
        });
        window._returnLazy.resetAndLoad();
    });

    if (new URLSearchParams(window.location.search).get('today') === '1') {
        const today = @json(date('Y-m-d'));
        $('#frmDate').val(today);
        $('#toDate').val(today);
        $('#searchBtn').trigger('click');
    }
</script>
@endpush
