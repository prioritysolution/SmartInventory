@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        #scrollTable { width: 100%; }
        #scrollTable th,
        #scrollTable td { vertical-align: top; }
        #scrollTable .col-sl { width: 42px; }
        #scrollTable .col-amt { width: 92px; }
        #scrollTable td.part,
        #scrollTable td.doc { white-space: normal; }
        #scrollBody td.item-span { padding: 6px 8px 8px; background: #fff; }
        #scrollBody .item-lines {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }
        #scrollBody .item-lines th,
        #scrollBody .item-lines td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 500;
            background: #fff;
        }
        #scrollBody .item-lines .item-name { white-space: normal; }
        #scrollBody .item-lines .num { white-space: nowrap; text-align: right; }
        .scroll-summary th,
        .scroll-summary td { border: 1px solid #dee2e6; padding: 4px 8px; }
        .scroll-summary th { background: #f8f9fa; }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">{{ $pageTitle ?? 'User Scroll' }}</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="asOnDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User<span class="text-danger">*</span></label>
                            <select class="form-select" id="userId">
                                <option value="0">All</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->User_Id }}"
                                        @selected((int) $user_id === (int) $user->User_Id)>
                                        {{ $user->User_FullName }} ({{ $user->User_Code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="printBtn" disabled>
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (!empty($showCashBalance))
                    <div class="d-flex justify-content-end mb-2">
                        <div class="text-end">
                            <div class="text-muted small">Opening Balance</div>
                            <div class="fw-bold fs-5" id="openingBalance">0.00</div>
                        </div>
                    </div>
                    @endif
                    <div class="table-responsive report-scroll">
                        <table id="scrollTable" class="table table-bordered mb-0">
                            <colgroup>
                                <col class="col-sl">
                                <col class="col-part">
                                <col class="col-doc">
                                <col class="col-amt">
                                <col class="col-amt">
                                <col class="col-amt">
                                <col class="col-amt">
                                <col class="col-amt">
                            </colgroup>
                            <thead class="thead-light">
                                <tr>
                                    <th rowspan="2">Sl</th>
                                    <th rowspan="2">Particulars</th>
                                    <th rowspan="2">Invoice No / Voucher No</th>
                                    <th colspan="2" class="text-center">Receipt</th>
                                    <th colspan="2" class="text-center">Payment</th>
                                    <th rowspan="2" class="text-end">Credit</th>
                                </tr>
                                <tr>
                                    <th class="text-end">Cash</th>
                                    <th class="text-end">Bank</th>
                                    <th class="text-end">Cash</th>
                                    <th class="text-end">Bank</th>
                                </tr>
                            </thead>
                            <tbody id="scrollBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Select date and user, then Search</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3" id="scrollSummary" style="display:none;"></div>
                    @if (!empty($showCashBalance))
                    <div class="d-flex justify-content-end mt-3">
                        <div class="text-end">
                            <div class="text-muted small">Closing Balance</div>
                            <div class="fw-bold fs-5" id="closingBalance">0.00</div>
                            <div class="small" id="closingBalanceWords"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const orgName = @json($org_name);
        const branchName = @json($branch_name);
        const searchUrl = @json($searchUrl ?? route('account-user-scroll.search'));
        const reportTitle = @json($pageTitle ?? 'User Scroll');
        const showCashBalance = @json(!empty($showCashBalance));
        let printRows = [];
        let printOpening = 0;
        let printClosing = 0;

        function fmtAmt(n) {
            const v = parseFloat(n) || 0;
            return v ? v.toFixed(2) : '';
        }

        function fmtTot(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function numberToWords(amount) {
            var num = Math.round((parseFloat(amount) || 0) * 100) / 100;
            var negative = num < 0;
            num = Math.abs(num);
            var rupees = Math.floor(num);
            var paise = Math.round((num - rupees) * 100);
            var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function twoDigits(n) {
                if (n < 20) return ones[n];
                return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
            }
            function threeDigits(n) {
                if (n < 100) return twoDigits(n);
                var rest = n % 100;
                return ones[Math.floor(n / 100)] + ' Hundred' + (rest ? ' and ' + twoDigits(rest) : '');
            }
            function toWords(n) {
                if (n === 0) return 'Zero';
                var crore = Math.floor(n / 10000000);
                var lakh = Math.floor((n % 10000000) / 100000);
                var thousand = Math.floor((n % 100000) / 1000);
                var rest = n % 1000;
                var parts = [];
                if (crore) parts.push(twoDigits(crore) + ' Crore');
                if (lakh) parts.push(twoDigits(lakh) + ' Lakh');
                if (thousand) parts.push(twoDigits(thousand) + ' Thousand');
                if (rest) parts.push(threeDigits(rest));
                return parts.join(' ');
            }

            var text = (negative ? 'Minus ' : '') + (rupees === 0 && paise > 0 ? '' : toWords(rupees));
            if (paise > 0) {
                text += (text.trim() ? ' and ' : '') + twoDigits(paise) + ' Paise';
            }
            return 'Closing Cash Balance in Word : ' + text.trim() + ' Only';
        }

        function fmtQty(n) {
            const v = parseFloat(n);
            if (isNaN(v)) return '';
            return Number.isInteger(v) ? String(v) : v.toFixed(2);
        }

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function emptyRow(message, isError) {
            const cls = isError ? 'text-danger' : 'text-muted';
            return `<tr><td colspan="8" class="text-center ${cls}">${esc(message)}</td></tr>`;
        }

        function amountCells(row, align, span) {
            const extra = span ? ' rowspan="2"' : '';
            const style = align === 'print'
                ? ` style="text-align:right; vertical-align:top;"${extra}`
                : ` class="text-end align-top"${extra}`;
            return `<td${style}>${fmtAmt(row.Receipt_Cash)}</td>
                <td${style}>${fmtAmt(row.Receipt_Bank)}</td>
                <td${style}>${fmtAmt(row.Payment_Cash)}</td>
                <td${style}>${fmtAmt(row.Payment_Bank)}</td>
                <td${style}>${fmtAmt(row.Credit)}</td>`;
        }

        function itemTable(items) {
            if (!items || !items.length) return '';
            let body = '';
            items.forEach(function(item) {
                body += `<tr>
                    <td>${item.Sl}</td>
                    <td class="item-name">${esc(item.Item)}</td>
                    <td>${esc(item.HSN)}</td>
                    <td>${esc(item.Unit)}</td>
                    <td class="num">${fmtQty(item.Qty)}</td>
                    <td class="num">${fmtTot(item.Rate)}</td>
                    <td class="num">${fmtTot(item.MRP)}</td>
                    <td class="num">${fmtTot(item.Disc)}</td>
                    <td class="num">${fmtTot(item.Taxable)}</td>
                    <td class="num">${fmtTot(item.GST_Prcnt)}</td>
                    <td class="num">${fmtTot(item.GST)}</td>
                    <td class="num">${fmtTot(item.Amount)}</td>
                </tr>`;
            });
            return `<table class="item-lines" style="width:100%; margin:0; border-collapse:collapse;">
                <thead><tr>
                    <th>Sl</th>
                    <th class="item-name">Item</th>
                    <th>HSN</th>
                    <th>Unit</th>
                    <th class="num">Qty</th>
                    <th class="num">Rate</th>
                    <th class="num">MRP</th>
                    <th class="num">Disc %</th>
                    <th class="num">Taxable</th>
                    <th class="num">GST %</th>
                    <th class="num">GST</th>
                    <th class="num">Amount</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>`;
        }

        function movementSummary(rows) {
            const s = {
                cashPurchase: 0, bankPurchase: 0, creditPurchase: 0,
                cashSale: 0, bankSale: 0, creditSale: 0
            };
            (rows || []).forEach(function(row) {
                if (row.Kind === 'purchase') {
                    s.cashPurchase += parseFloat(row.Payment_Cash) || 0;
                    s.bankPurchase += parseFloat(row.Payment_Bank) || 0;
                    s.creditPurchase += parseFloat(row.Credit) || 0;
                } else if (row.Kind === 'sale') {
                    s.cashSale += parseFloat(row.Receipt_Cash) || 0;
                    s.bankSale += parseFloat(row.Receipt_Bank) || 0;
                    s.creditSale += parseFloat(row.Credit) || 0;
                }
            });
            s.totalPurchase = s.cashPurchase + s.bankPurchase + s.creditPurchase;
            s.totalSale = s.cashSale + s.bankSale + s.creditSale;
            return s;
        }

        function summaryLine(label, value, strong) {
            const weight = strong ? 'font-weight:bold;' : '';
            return `<tr><td style="${weight}">${esc(label)}</td><td style="text-align:right;${weight}">${fmtTot(value)}</td></tr>`;
        }

        function summaryBox(rows) {
            const s = movementSummary(rows);
            return `<table class="scroll-summary" style="width:420px; border-collapse:collapse;">
                <thead><tr><th colspan="2" style="text-align:left;">Summary</th></tr></thead>
                <tbody>
                    ${summaryLine('Total Cash Purchase', s.cashPurchase)}
                    ${summaryLine('Bank Purchase', s.bankPurchase)}
                    ${summaryLine('Total Purchase', s.totalPurchase, true)}
                    ${summaryLine('Total Cash Sale', s.cashSale)}
                    ${summaryLine('Bank Sale', s.bankSale)}
                    ${summaryLine('Total Sale', s.totalSale, true)}
                    ${summaryLine('Total Credit Purchase', s.creditPurchase)}
                    ${summaryLine('Total Credit Sale', s.creditSale)}
                </tbody>
            </table>`;
        }

        function billRow(row, idx, align) {
            const topStyle = align === 'print' ? ' style="vertical-align:top;"' : '';
            const items = itemTable(row.Items);
            const span = items ? ' rowspan="2"' : '';
            let html = `<tr>
                <td class="align-top"${topStyle}${span}>${idx + 1}</td>
                <td class="part align-top"${topStyle}>${esc(row.Particulars)}</td>
                <td class="doc align-top"${topStyle}>${esc(row.Doc_No)}</td>
                ${amountCells(row, align, !!items)}
            </tr>`;
            if (items) {
                html += `<tr><td colspan="2" class="item-span">${items}</td></tr>`;
            }
            return html;
        }

        function buildBody(rows) {
            if (!rows.length) {
                return emptyRow('No records found', false);
            }
            let html = '';
            rows.forEach(function(row, idx) {
                html += billRow(row, idx);
            });
            return html;
        }

        function resetTable(message, isError) {
            $('#scrollBody').html(emptyRow(message, isError));
            $('#scrollSummary').hide().empty();
            if (showCashBalance) {
                $('#openingBalance').text('0.00');
                $('#closingBalance').text('0.00');
                $('#closingBalanceWords').text('');
            }
            $('#printBtn').prop('disabled', true);
            printRows = [];
            printOpening = 0;
            printClosing = 0;
        }

        function printScroll() {
            if (!printRows.length && !(showCashBalance && (printOpening || printClosing))) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            const asOn = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay($('#asOnDate').val())
                : $('#asOnDate').val();
            const userText = $('#userId option:selected').text();
            let body = '';
            printRows.forEach(function(row, idx) {
                body += billRow(row, idx, 'print');
            });
            siPrint(`<!DOCTYPE html><html><head><title>${reportTitle}</title>
                <style>
                    @page { size: landscape; }
                    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
                    h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                    table.scroll-main, table.scroll-summary { border-collapse: collapse; }
                    table.scroll-main { width: 100%; margin-top: 12px; }
                    table.scroll-summary { width: 420px; margin-top: 12px; }
                    table.scroll-main > thead > tr > th,
                    table.scroll-main > tbody > tr > td,
                    table.scroll-summary th,
                    table.scroll-summary td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
                    table.scroll-main th { text-align: center; }
                    table.item-lines { width: 100%; margin-top: 4px; border-collapse: collapse; }
                    table.item-lines th, table.item-lines td { border: 1px solid #000; padding: 2px 5px; font-size: 10px; }
                    table.item-lines .num { text-align: right; white-space: nowrap; }
                </style></head><body>
                <h3>${esc(orgName || 'Smart Inventory')}</h3>
                <h4>${esc(reportTitle)}</h4>
                <p>${branchName ? esc(branchName) + ' &nbsp;|&nbsp; ' : ''}Date: ${esc(asOn)} &nbsp;|&nbsp; ${esc(userText)}</p>
                ${showCashBalance ? `<p style="text-align:right; font-weight:bold;">Opening Balance: ${fmtTot(printOpening)}</p>` : ''}
                <table class="scroll-main">
                    <colgroup>
                        <col class="col-sl"><col class="col-part"><col class="col-doc">
                        <col class="col-amt"><col class="col-amt"><col class="col-amt"><col class="col-amt"><col class="col-amt">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2">Sl</th>
                            <th rowspan="2">Particulars</th>
                            <th rowspan="2">Invoice No / Voucher No</th>
                            <th colspan="2">Receipt</th>
                            <th colspan="2">Payment</th>
                            <th rowspan="2">Credit</th>
                        </tr>
                        <tr>
                            <th>Cash</th><th>Bank</th><th>Cash</th><th>Bank</th>
                        </tr>
                    </thead>
                    <tbody>${body}</tbody>
                </table>
                ${summaryBox(printRows)}
                ${showCashBalance ? `<p style="text-align:right; font-weight:bold; margin-bottom:0;">Closing Balance: ${fmtTot(printClosing)}</p><p style="text-align:right;">${esc(numberToWords(printClosing))}</p>` : ''}
                </body></html>`);
        }

        $('#printBtn').on('click', printScroll);

        $('#searchBtn').on('click', function() {
            const asOnDate = $('#asOnDate').val();
            if (!asOnDate) {
                Swal.fire('Error', 'Select date', 'error');
                return;
            }
            resetTable('Loading...', false);
            $.get(searchUrl, { as_on_date: asOnDate, user_id: $('#userId').val() || 0 }, function(data) {
                const isObject = data && !Array.isArray(data);
                printRows = isObject ? (data.rows || []) : (data || []);
                printOpening = isObject ? (parseFloat(data.opening) || 0) : 0;
                printClosing = isObject ? (parseFloat(data.closing) || 0) : 0;
                $('#scrollBody').html(buildBody(printRows));
                $('#scrollSummary').html(summaryBox(printRows)).show();
                if (showCashBalance) {
                    $('#openingBalance').text(fmtTot(printOpening));
                    $('#closingBalance').text(fmtTot(printClosing));
                    $('#closingBalanceWords').text(numberToWords(printClosing));
                }
                $('#printBtn').prop('disabled', !printRows.length && !(showCashBalance));
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load user scroll';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
