@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        .cash-account {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
            color: #000;
            background: #fff;
            font-size: 12px;
        }

        .cash-account th,
        .cash-account td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .cash-account thead th {
            background: #fff;
            font-weight: 700;
            text-align: center;
        }

        .cash-account td.part {
            text-align: left;
            white-space: normal;
            min-width: 140px;
        }

        .cash-account .num {
            text-align: right;
            white-space: nowrap;
        }

        .cash-account tfoot th {
            font-weight: 700;
            background: #fff;
        }

        .register-heading {
            text-align: center;
            color: #000;
            margin-bottom: 8px;
        }

        .register-heading h5,
        .register-heading p {
            margin: 0;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Cash A/c</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="frmDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ $year_start }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="toDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
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
                    <div id="registerHeading" class="register-heading" style="display:none;">
                        <h5>STATEMENT OF CASH ACCOUNT</h5>
                        <p id="registerPeriod"></p>
                    </div>
                    <div class="table-responsive report-scroll">
                        <table id="cashAccountTable" class="cash-account">
                            <thead>
                                <tr>
                                    <th colspan="5">Receipt</th>
                                    <th colspan="5">Payment</th>
                                </tr>
                                <tr>
                                    <th>Code</th>
                                    <th>Name of the Ledger</th>
                                    <th>Cash</th>
                                    <th>Transfer</th>
                                    <th>Total</th>
                                    <th>Code</th>
                                    <th>Name of the Ledger</th>
                                    <th>Cash</th>
                                    <th>Transfer</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Select dates and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot id="reportFoot"></tfoot>
                        </table>
                    </div>
                    <div class="text-end fw-semibold mt-3" id="closingWords"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const orgName = @json($org_name);
        const branchName = @json($branch_name);
        const searchUrl = @json(route('cash-account.search'));
        let printData = null;

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (ch) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
            });
        }

        function fmtAmt(n) {
            const value = parseFloat(n) || 0;
            return value ? value.toFixed(2) : '';
        }

        function fmtTotal(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function fmtDate(val) {
            if (!val) return '';
            return (window.siDate && siDate.toDisplay) ? siDate.toDisplay(val) : val;
        }

        function closingWords(amount) {
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

        function resetTable(message, isError) {
            printData = null;
            $('#printBtn').prop('disabled', true);
            $('#registerHeading').hide();
            $('#closingWords').text('');
            const cls = isError ? 'text-danger' : 'text-muted';
            $('#reportBody').html(`<tr><td colspan="10" class="text-center ${cls}">${esc(message)}</td></tr>`);
            $('#reportFoot').html('');
        }

        function sumField(rows, field) {
            return (rows || []).reduce(function (total, row) {
                return total + (parseFloat(row[field]) || 0);
            }, 0);
        }

        function sideAmount(row, prefix) {
            return Math.abs(parseFloat(row[prefix + '_cash']) || 0) + Math.abs(parseFloat(row[prefix + '_transfer']) || 0);
        }

        function sideCells(row, prefix) {
            if (!row) {
                return '<td></td><td></td><td></td><td></td><td></td>';
            }
            const cash = parseFloat(row[prefix + '_cash']) || 0;
            const transfer = parseFloat(row[prefix + '_transfer']) || 0;
            return `<td>${esc(row.Code)}</td>
                <td class="part">${esc(row.Ledger)}</td>
                <td class="num">${fmtAmt(cash)}</td>
                <td class="num">${fmtAmt(transfer)}</td>
                <td class="num">${fmtAmt(cash + transfer)}</td>`;
        }

        function moneyCell(amount, bold) {
            const text = Math.abs(amount) > 0.0001 ? fmtTotal(amount) : '';
            return bold
                ? `<th class="num">${text}</th>`
                : `<td style="text-align:right;">${text}</td>`;
        }

        function renderReport(data) {
            const ledgers = data.ledgers || [];
            const receipts = ledgers.filter(function (row) {
                return sideAmount(row, 'receipt') > 0.0001;
            });
            const payments = ledgers.filter(function (row) {
                return sideAmount(row, 'payment') > 0.0001;
            });
            const count = Math.max(receipts.length, payments.length);
            let html = '';
            for (let i = 0; i < count; i++) {
                html += `<tr>${sideCells(receipts[i], 'receipt')}${sideCells(payments[i], 'payment')}</tr>`;
            }
            if (!html) {
                html = '<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>';
            }
            $('#reportBody').html(html);

            const recCash = parseFloat(data.receipt_cash) || 0;
            const recXfer = parseFloat(data.receipt_transfer) || 0;
            const payCash = parseFloat(data.payment_cash) || 0;
            const payXfer = parseFloat(data.payment_transfer) || 0;
            const recTotal = recCash + recXfer;
            const payTotal = payCash + payXfer;
            const opening = parseFloat(data.opening) || 0;
            const closing = parseFloat(data.closing) || 0;
            let foot = `<tr>
                    <th colspan="2" class="text-end">Sub Total</th>
                    ${moneyCell(recCash, true)}
                    ${moneyCell(recXfer, true)}
                    <th class="num">${fmtTotal(recTotal)}</th>
                    <th colspan="2" class="text-end">Sub Total</th>
                    ${moneyCell(payCash, true)}
                    ${moneyCell(payXfer, true)}
                    <th class="num">${fmtTotal(payTotal)}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-end">Opening Balance</th>
                    <th class="num">${fmtTotal(opening)}</th>
                    <th colspan="4" class="text-end">Closing Balance</th>
                    <th class="num">${fmtTotal(closing)}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-end">Grand Total</th>
                    <th class="num">${fmtTotal(recTotal + opening)}</th>
                    <th colspan="4" class="text-end">Grand Total</th>
                    <th class="num">${fmtTotal(payTotal + closing)}</th>
                </tr>`;
            $('#reportFoot').html(foot);
            $('#closingWords').text(closingWords(closing));
        }

        function printReport() {
            if (!printData) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            const frm = fmtDate($('#frmDate').val());
            const to = fmtDate($('#toDate').val());
            const ledgers = printData.ledgers || [];
            const receipts = ledgers.filter(function (row) {
                return sideAmount(row, 'receipt') > 0.0001;
            });
            const payments = ledgers.filter(function (row) {
                return sideAmount(row, 'payment') > 0.0001;
            });
            const count = Math.max(receipts.length, payments.length);
            let body = '';
            function printSide(row, prefix) {
                if (!row) {
                    return '<td></td><td></td><td></td><td></td><td></td>';
                }
                const cash = parseFloat(row[prefix + '_cash']) || 0;
                const transfer = parseFloat(row[prefix + '_transfer']) || 0;
                return `<td>${esc(row.Code)}</td>
                    <td>${esc(row.Ledger)}</td>
                    <td style="text-align:right;">${fmtAmt(cash)}</td>
                    <td style="text-align:right;">${fmtAmt(transfer)}</td>
                    <td style="text-align:right;">${fmtAmt(cash + transfer)}</td>`;
            }
            for (let i = 0; i < count; i++) {
                body += `<tr>${printSide(receipts[i], 'receipt')}${printSide(payments[i], 'payment')}</tr>`;
            }
            const recCash = parseFloat(printData.receipt_cash) || 0;
            const recXfer = parseFloat(printData.receipt_transfer) || 0;
            const payCash = parseFloat(printData.payment_cash) || 0;
            const payXfer = parseFloat(printData.payment_transfer) || 0;
            const recTotal = recCash + recXfer;
            const payTotal = payCash + payXfer;
            const opening = parseFloat(printData.opening) || 0;
            const closing = parseFloat(printData.closing) || 0;
            const balanceHtml = `<tr>
                    <td colspan="4" style="text-align:right;font-weight:bold;">Opening Balance</td>
                    <td style="text-align:right;font-weight:bold;">${fmtTotal(opening)}</td>
                    <td colspan="4" style="text-align:right;font-weight:bold;">Closing Balance</td>
                    <td style="text-align:right;font-weight:bold;">${fmtTotal(closing)}</td>
                </tr>`;
            siPrint(`<!DOCTYPE html><html><head><title>Cash Account</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    h3, h4, p { margin: 0 0 4px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 4px 6px; }
                </style></head><body>
                <h3>${esc(orgName || 'Smart Inventory')}</h3>
                ${branchName ? `<p>${esc(branchName)}</p>` : ''}
                <h4>STATEMENT OF CASH ACCOUNT</h4>
                <p>FROM ${esc(frm)} TO ${esc(to)}</p>
                <table>
                    <thead>
                        <tr>
                            <th colspan="5">Receipt</th>
                            <th colspan="5">Payment</th>
                        </tr>
                        <tr>
                            <th>Code</th><th>Name of the Ledger</th><th>Cash</th><th>Transfer</th><th>Total</th>
                            <th>Code</th><th>Name of the Ledger</th><th>Cash</th><th>Transfer</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>${body}
                        <tr>
                            <td colspan="2" style="text-align:right;font-weight:bold;">Sub Total</td>
                            <td style="text-align:right;font-weight:bold;">${Math.abs(recCash) > 0.0001 ? fmtTotal(recCash) : ''}</td>
                            <td style="text-align:right;font-weight:bold;">${Math.abs(recXfer) > 0.0001 ? fmtTotal(recXfer) : ''}</td>
                            <td style="text-align:right;font-weight:bold;">${fmtTotal(recTotal)}</td>
                            <td colspan="2" style="text-align:right;font-weight:bold;">Sub Total</td>
                            <td style="text-align:right;font-weight:bold;">${Math.abs(payCash) > 0.0001 ? fmtTotal(payCash) : ''}</td>
                            <td style="text-align:right;font-weight:bold;">${Math.abs(payXfer) > 0.0001 ? fmtTotal(payXfer) : ''}</td>
                            <td style="text-align:right;font-weight:bold;">${fmtTotal(payTotal)}</td>
                        </tr>
                        ${balanceHtml}
                        <tr>
                            <td colspan="4" style="text-align:right;font-weight:bold;">Grand Total</td>
                            <td style="text-align:right;font-weight:bold;">${fmtTotal(recTotal + opening)}</td>
                            <td colspan="4" style="text-align:right;font-weight:bold;">Grand Total</td>
                            <td style="text-align:right;font-weight:bold;">${fmtTotal(payTotal + closing)}</td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align:right; font-weight:bold; margin-top:16px;">${esc(closingWords(closing))}</p>
                </body></html>`);
        }

        $('#printBtn').on('click', printReport);

        $('#searchBtn').on('click', function () {
            const frm = $('#frmDate').val();
            const to = $('#toDate').val();
            if (!frm || !to) {
                Swal.fire('Error', 'Select from date and to date', 'error');
                return;
            }
            resetTable('Loading...', false);
            $.get(searchUrl, { frm_date: frm, to_date: to }, function (data) {
                const ledgers = data && data.ledgers ? data.ledgers : [];
                if (!ledgers.length && !(parseFloat(data.opening) || 0) && !(parseFloat(data.closing) || 0)) {
                    resetTable('No records found', false);
                    return;
                }
                printData = data;
                $('#registerPeriod').text('FROM ' + fmtDate(frm) + ' TO ' + fmtDate(to));
                $('#registerHeading').show();
                renderReport(data);
                $('#printBtn').prop('disabled', false);
            }).fail(function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load cash account';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
