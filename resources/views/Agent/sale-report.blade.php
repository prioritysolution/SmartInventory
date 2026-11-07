@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Sale Report</h6>
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
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Amount</th>
                            <th>Trans Mode</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                        <tr><td colspan="10" class="text-center text-muted">Select dates and click Search</td></tr>
                    </tbody>
                    <tfoot id="reportFoot" class="d-none">
                        <tr class="fw-bold">
                            <td colspan="8" class="text-end">Total (incl. round-off)</td>
                            <td class="text-end" id="netTotalCell">0.00</td>
                            <td></td>
                        </tr>
                        <tr id="roundOffRow" class="d-none">
                            <td colspan="8" class="text-end text-muted">Round Off</td>
                            <td class="text-end text-muted" id="roundOffCell">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 sale-summary">
        <div class="col-md-4">
            <div class="sale-mode-box sale-mode-cash">
                <div class="mode-label">Cash Total</div>
                <div class="mode-amount" id="cashTotal">₹ 0.00</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sale-mode-box sale-mode-bank">
                <div class="mode-label">Bank Total</div>
                <div class="mode-amount" id="bankTotal">₹ 0.00</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sale-mode-box sale-mode-credit">
                <div class="mode-label">Credit Total</div>
                <div class="mode-amount" id="creditTotal">₹ 0.00</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 sale-summary">
        <div class="col-md-6">
            <div class="sale-mode-box sale-mode-credit-settled">
                <div class="mode-label">Credit Paid (Customer)</div>
                <div class="mode-amount" id="creditSettledTotal">₹ 0.00</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="sale-mode-box sale-mode-credit-unsettled">
                <div class="mode-label">Credit Outstanding</div>
                <div class="mode-amount" id="creditUnsettledTotal">₹ 0.00</div>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@push('style')
<style>
    .sale-mode-box {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px 18px;
        background: #fff;
        border-left-width: 4px;
    }
    .sale-mode-cash { border-left-color: #16a34a; }
    .sale-mode-bank { border-left-color: #2563eb; }
    .sale-mode-credit { border-left-color: #ea580c; }
    .sale-mode-credit-settled { border-left-color: #0d9488; }
    .sale-mode-credit-unsettled { border-left-color: #dc2626; }
    .sale-mode-box .mode-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
    }
    .sale-mode-box .mode-amount {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const searchUrl = "{{ route('agent.report.sale.search') }}";
    const orgName = @json($org_name);
    const agentName = @json($agent_name);
    let printRows = [];
    let printSummary = emptySummary();

    function emptySummary() {
        return { cash: 0, bank: 0, credit: 0, credit_settled: 0, credit_unsettled: 0, net: 0, round_off: 0 };
    }

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function updateSummary(summary) {
        printSummary = summary || emptySummary();
        $('#cashTotal').text('₹ ' + fmtAmt(printSummary.cash));
        $('#bankTotal').text('₹ ' + fmtAmt(printSummary.bank));
        $('#creditTotal').text('₹ ' + fmtAmt(printSummary.credit));
        $('#creditSettledTotal').text('₹ ' + fmtAmt(printSummary.credit_settled));
        $('#creditUnsettledTotal').text('₹ ' + fmtAmt(printSummary.credit_unsettled));
        $('#netTotalCell').text(fmtAmt(printSummary.net));
        const roundOff = parseFloat(printSummary.round_off) || 0;
        $('#roundOffCell').text(fmtAmt(roundOff));
        if (Math.abs(roundOff) > 0.0001) {
            $('#roundOffRow').removeClass('d-none');
        } else {
            $('#roundOffRow').addClass('d-none');
        }
        if (printRows.length) {
            $('#reportFoot').removeClass('d-none');
        } else {
            $('#reportFoot').addClass('d-none');
        }
    }

    function renderRows(data, summary) {
        if (!data.length) {
            printRows = [];
            $('#reportBody').html('<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>');
            $('#printBtn').prop('disabled', true);
            updateSummary(summary || emptySummary());
            return;
        }
        printRows = data;
        let html = '';
        data.forEach((row, idx) => {
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Doc_No ?? ''}</td>
                <td>${siDate.toDisplay(row.Doc_Date)}</td>
                <td>${row.Cust_Name ?? ''}</td>
                <td>${row.Prod_Code ?? ''}</td>
                <td>${row.Prod_ShortNm ?? ''}</td>
                <td class="text-end">${siFmtQty(row.Quantity)}</td>
                <td>${row.Unit_Name ?? ''}</td>
                <td class="text-end">${fmtAmt(row.Amount)}</td>
                <td>${row.Trans_Mode ?? '-'}</td>
            </tr>`;
        });
        $('#reportBody').html(html);
        updateSummary(summary);
        $('#printBtn').prop('disabled', false);
    }

    function buildSaleCsv(rows) {
        const frm = siDate.toDisplay($('#frmDate').val());
        const to = siDate.toDisplay($('#toDate').val());
        const totals = printSummary;

        const meta = [
            ['Organization', orgName || 'Smart Inventory'],
            ['Report', 'Sale Report'],
            ['Agent', agentName || ''],
            ['Period', (frm && to ? `From ${frm} To ${to}` : '')],
            ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
        ];

        const headers = ['Sl', 'Invoice No', 'Date', 'Customer', 'Item Code', 'Item Name', 'Qty', 'Unit', 'Amount', 'Trans Mode'];

        let totQty = 0;
        let totAmt = 0;
        const dataRows = (rows || []).map(function(row, idx) {
            const q = parseFloat(row.Quantity) || 0;
            const a = parseFloat(row.Amount) || 0;
            totQty += q;
            totAmt += a;
            return [
                String(idx + 1),
                row.Doc_No || '',
                siDate.toDisplay(row.Doc_Date),
                row.Cust_Name || '',
                row.Prod_Code || '',
                row.Prod_ShortNm || '',
                siFmtQty(q),
                row.Unit_Name || '',
                fmtAmt(a),
                row.Trans_Mode || '-'
            ];
        });

        const totalsRow = ['Total (incl. round-off)', '', '', '', '', '', siFmtQty(totQty), '', fmtAmt(totals.net || totAmt), ''];

        const summaryRows = [
            ['Summary by Payment Mode'],
            ['Cash Total', fmtAmt(totals.cash || 0)],
            ['Bank Total', fmtAmt(totals.bank || 0)],
            ['Credit Total', fmtAmt(totals.credit || 0)],
            ['Credit Paid (Customer)', fmtAmt(totals.credit_settled || 0)],
            ['Credit Outstanding', fmtAmt(totals.credit_unsettled || 0)]
        ];

        const csv1 = siBuildCsv({
            meta: meta,
            headers: headers,
            rows: dataRows,
            totals: totalsRow
        });

        return csv1 + '\r\n\r\n' + summaryRows.map(function(r) {
            return r.map(function(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\r\n');
    }

    function buildSaleHtml(rows) {
        const frm = siDate.toDisplay($('#frmDate').val());
        const to = siDate.toDisplay($('#toDate').val());
        const totals = printSummary;
        let body = '';
        (rows || []).forEach((row, idx) => {
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Doc_No ?? ''}</td>
                <td>${siDate.toDisplay(row.Doc_Date)}</td>
                <td>${row.Cust_Name ?? ''}</td>
                <td>${row.Prod_Code ?? ''}</td>
                <td>${row.Prod_ShortNm ?? ''}</td>
                <td style="text-align:right;">${siFmtQty(row.Quantity)}</td>
                <td>${row.Unit_Name ?? ''}</td>
                <td style="text-align:right;">${fmtAmt(row.Amount)}</td>
                <td>${row.Trans_Mode ?? '-'}</td>
            </tr>`;
        });
        body += `<tr>
            <td colspan="8" style="text-align:right;font-weight:bold;">Total (incl. round-off)</td>
            <td style="text-align:right;font-weight:bold;">${fmtAmt(totals.net)}</td>
            <td></td>
        </tr>`;
        if (Math.abs(parseFloat(totals.round_off) || 0) > 0.0001) {
            body += `<tr>
                <td colspan="8" style="text-align:right;">Round Off</td>
                <td style="text-align:right;">${fmtAmt(totals.round_off)}</td>
                <td></td>
            </tr>`;
        }
        return `<!DOCTYPE html><html><head><title>Sale Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
                .summary { margin-top: 14px; width: 100%; }
                .summary td { border: 1px solid #000; padding: 8px; text-align: center; }
                .summary .lbl { font-size: 11px; color: #444; }
                .summary .amt { font-size: 14px; font-weight: bold; margin-top: 4px; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Sale Report</h4>
            <p>Agent: ${agentName || ''} &nbsp;|&nbsp; From: ${frm} &nbsp; To: ${to}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Invoice No</th><th>Date</th><th>Customer</th>
                    <th>Item Code</th><th>Item Name</th><th>Qty</th><th>Unit</th><th>Amount</th>
                    <th>Trans Mode</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            <table class="summary">
                <tr>
                    <td><div class="lbl">Cash Total</div><div class="amt">₹ ${fmtAmt(totals.cash)}</div></td>
                    <td><div class="lbl">Bank Total</div><div class="amt">₹ ${fmtAmt(totals.bank)}</div></td>
                    <td><div class="lbl">Credit Total</div><div class="amt">₹ ${fmtAmt(totals.credit)}</div></td>
                </tr>
                <tr>
                    <td colspan="1"><div class="lbl">Credit Paid (Customer)</div><div class="amt">₹ ${fmtAmt(totals.credit_settled)}</div></td>
                    <td colspan="2"><div class="lbl">Credit Outstanding</div><div class="amt">₹ ${fmtAmt(totals.credit_unsettled)}</div></td>
                </tr>
            </table>
            </body></html>`;
    }

    function downloadSaleZip() {
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
                const csvContent = buildSaleCsv(printRows);
                const htmlContent = buildSaleHtml(printRows);
                const safeName = 'Sale_Report_' + siDateStamp();
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
            siWarnPrintLimit(printRows.length, downloadSaleZip);
            return;
        }
        const html = buildSaleHtml(printRows);
        siPrint(html);
    }

    $('#printBtn').on('click', printReport);
    $(document).on('click', '#downloadBtn', function() {
        downloadSaleZip();
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
        updateSummary(emptySummary());
        if (window._saleLazy) window._saleLazy.destroy();
        window._saleLazy = SiLazyScroll.create({
            scrollEl: $('#reportBody').closest('.report-scroll'),
            tbody: '#reportBody',
            url: searchUrl,
            pageSize: 40,
            showEndRow: false,
            colSpan: 10,
            params: function () { return { frm_date: frmDate, to_date: toDate }; },
            emptyHtml: '<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>',
            renderRow: function (row, idx) {
                return `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Doc_No ?? ''}</td>
                    <td>${siDate.toDisplay(row.Doc_Date)}</td>
                    <td>${row.Cust_Name ?? ''}</td>
                    <td>${row.Prod_Code ?? ''}</td>
                    <td>${row.Prod_ShortNm ?? ''}</td>
                    <td class="text-end">${siFmtQty(row.Quantity)}</td>
                    <td>${row.Unit_Name ?? ''}</td>
                    <td class="text-end">${fmtAmt(row.Amount)}</td>
                    <td>${row.Trans_Mode ?? '-'}</td>
                </tr>`;
            },
            onChunk: function (rows, payload, reset) {
                if (reset) printRows = [];
                printRows = printRows.concat(rows);
                if (payload && payload.summary) {
                    updateSummary(payload.summary);
                }
                if (printRows.length) {
                    $('#reportFoot').removeClass('d-none');
                }
                $('#printBtn').prop('disabled', printRows.length === 0);
                $('#downloadBtn').prop('disabled', printRows.length === 0);
            },
            onError: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report', 'error');
                updateSummary(emptySummary());
            }
        });
        window._saleLazy.resetAndLoad();
    });

    if (new URLSearchParams(window.location.search).get('today') === '1') {
        const today = @json(date('Y-m-d'));
        $('#frmDate').val(today);
        $('#toDate').val(today);
        $('#searchBtn').trigger('click');
    }
</script>
@endpush
