@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Stock Report</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">As on Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="asOnDate"
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive report-scroll">
                <table id="stockTable" class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th class="text-end">Qty</th>
                        </tr>
                    </thead>
                    <tbody id="stockBody">
                        <tr><td colspan="5" class="text-center text-muted">Select as on date and click Search</td></tr>
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
    const searchUrl = "{{ route('agent.report.stock.search') }}";
    const orgName = @json($org_name);
    const agentName = @json($agent_name);
    let printRows = [];

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function buildStockCsv(rows) {
        const asOn = siDate.toDisplay($('#asOnDate').val());
        const meta = [
            ['Organization', orgName || 'Smart Inventory'],
            ['Report', 'Stock Report'],
            ['Agent', agentName || ''],
            ['As on Date', asOn],
            ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
        ];

        const headers = ['Sl', 'Item Code', 'Item Name', 'Unit', 'Qty'];

        let totQty = 0;
        const dataRows = (rows || []).map(function(row, idx) {
            const q = parseFloat(row.Qty) || 0;
            totQty += q;
            return [
                String(idx + 1),
                row.Prod_Code || '',
                row.Prod_ShortNm || '',
                row.Unit_Name || '',
                siFmtQty(q)
            ];
        });

        const totals = ['Total', '', '', '', siFmtQty(totQty)];

        return siBuildCsv({
            meta: meta,
            headers: headers,
            rows: dataRows,
            totals: totals
        });
    }

    function buildStockHtml(rows) {
        const asOn = siDate.toDisplay($('#asOnDate').val());
        let body = '';
        let totQty = 0;
        (rows || []).forEach((row, idx) => {
            const q = parseFloat(row.Qty) || 0;
            totQty += q;
            body += `<tr>
                <td>${escapeHtml(idx + 1)}</td>
                <td>${escapeHtml(row.Prod_Code)}</td>
                <td>${escapeHtml(row.Prod_ShortNm)}</td>
                <td>${escapeHtml(row.Unit_Name)}</td>
                <td style="text-align:right;">${escapeHtml(siFmtQty(q))}</td>
            </tr>`;
        });
        body += `<tr>
            <th colspan="4" style="text-align:right;">Total</th>
            <th style="text-align:right;">${escapeHtml(siFmtQty(totQty))}</th>
        </tr>`;

        return `<!DOCTYPE html><html><head><title>Stock Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${escapeHtml(orgName || 'Smart Inventory')}</h3>
            <h4>Stock Report</h4>
            <p>Agent: ${escapeHtml(agentName || '')} &nbsp;|&nbsp; As on: ${escapeHtml(asOn)}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Item Code</th><th>Item Name</th><th>Unit</th><th>Qty</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`;
    }

    function downloadStockZip() {
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
                const csvContent = buildStockCsv(printRows);
                const htmlContent = buildStockHtml(printRows);
                const safeName = 'Stock_Report_' + siDateStamp();
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

    function printStock() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (printRows.length > 1000) {
            siWarnPrintLimit(printRows.length, downloadStockZip);
            return;
        }
        const html = buildStockHtml(printRows);
        siPrint(html);
    }

    $('#printBtn').on('click', printStock);
    $(document).on('click', '#downloadBtn', function() {
        downloadStockZip();
    });

    $('#searchBtn').on('click', function () {
        const asOnDate = $('#asOnDate').val();
        if (!asOnDate) {
            Swal.fire('Error', 'Select date', 'error');
            return;
        }

        printRows = [];
        $('#printBtn').prop('disabled', true);
        $('#downloadBtn').prop('disabled', true);
        if (window._stockLazy) window._stockLazy.destroy();
        window._stockLazy = SiLazyScroll.create({
            scrollEl: $('#stockBody').closest('.report-scroll'),
            tbody: '#stockBody',
            url: searchUrl,
            pageSize: 40,
            showEndRow: false,
            colSpan: 5,
            params: function () { return { as_on_date: asOnDate }; },
            emptyHtml: '<tr><td colspan="5" class="text-center text-muted">No stock found</td></tr>',
            renderRow: function (row, idx) {
                return `<tr>
                    <td>${idx + 1}</td>
                    <td>${$('<div>').text(row.Prod_Code ?? '').html()}</td>
                    <td>${$('<div>').text(row.Prod_ShortNm ?? '').html()}</td>
                    <td>${$('<div>').text(row.Unit_Name ?? '').html()}</td>
                    <td class="text-end">${siFmtQty(row.Qty)}</td>
                </tr>`;
            },
            onChunk: function (rows, payload, reset) {
                if (reset) printRows = [];
                printRows = printRows.concat(rows);
                $('#printBtn').prop('disabled', printRows.length === 0);
                $('#downloadBtn').prop('disabled', printRows.length === 0);
            },
            onError: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load stock report. Run USP_AGENT_REPORTS.sql on the organisation database.';
                Swal.fire('Error', msg, 'error');
            }
        });
        window._stockLazy.resetAndLoad();
    });

    if (new URLSearchParams(window.location.search).get('today') === '1') {
        $('#searchBtn').trigger('click');
    }
</script>
@endpush
