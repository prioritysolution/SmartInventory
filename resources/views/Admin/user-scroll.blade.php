@extends('Dashboard.Layouts.layout')

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
                        <table class="table table-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl No</th>
                                    <th>{{ $docLabel ?? 'Voucher No' }}</th>
                                    <th>Particular</th>
                                    <th class="text-end">{{ $leftAmtLabel ?? 'Receipt' }}</th>
                                    <th class="text-end">{{ $rightAmtLabel ?? 'Payment' }}</th>
                                </tr>
                            </thead>
                            <tbody id="scrollBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Select date and user, then Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Grand Total</th>
                                    <th class="text-end" id="receiptTotal">0.00</th>
                                    <th class="text-end" id="paymentTotal">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if (!empty($showCashBalance))
                    <div class="d-flex justify-content-end mt-3">
                        <div class="text-end">
                            <div class="text-muted small">Closing Balance</div>
                            <div class="fw-bold fs-5" id="closingBalance">0.00</div>
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
        const searchUrl = @json($searchUrl ?? route('user-scroll.search'));
        const reportTitle = @json($pageTitle ?? 'User Scroll');
        const docLabel = @json($docLabel ?? 'Voucher No');
        const leftAmtLabel = @json($leftAmtLabel ?? 'Receipt');
        const rightAmtLabel = @json($rightAmtLabel ?? 'Payment');
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

        function emptyRow(message, isError) {
            const cls = isError ? 'text-danger' : 'text-muted';
            return `<tr><td colspan="5" class="text-center ${cls}">${message}</td></tr>`;
        }

        function groupByLedger(rows) {
            const groups = [];
            (rows || []).forEach(function(row) {
                const name = row.Ledger_Name || 'Unallocated';
                if (!groups.length || groups[groups.length - 1].name !== name) {
                    groups.push({ name: name, rows: [] });
                }
                groups[groups.length - 1].rows.push(row);
            });
            return groups;
        }

        function buildBody(rows) {
            const groups = groupByLedger(rows);
            if (!groups.length) {
                return { html: emptyRow(showCashBalance ? 'No cash transactions for this date' : 'No records found', false), rec: 0, pay: 0 };
            }
            let html = '';
            let grandRec = 0;
            let grandPay = 0;
            groups.forEach(function(group) {
                html += `<tr><td colspan="5" class="fw-bold">${group.name}</td></tr>`;
                let rec = 0;
                let pay = 0;
                group.rows.forEach(function(row, idx) {
                    const r = parseFloat(row.Receipt_Amt) || 0;
                    const p = parseFloat(row.Payment_Amt) || 0;
                    rec += r;
                    pay += p;
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${row.Doc_No ?? ''}</td>
                        <td>${row.Particulars ?? ''}</td>
                        <td class="text-end">${fmtAmt(r)}</td>
                        <td class="text-end">${fmtAmt(p)}</td>
                    </tr>`;
                });
                html += `<tr class="fw-bold">
                    <td colspan="3">TOTAL :</td>
                    <td class="text-end">${fmtTot(rec)}</td>
                    <td class="text-end">${fmtTot(pay)}</td>
                </tr>`;
                grandRec += rec;
                grandPay += pay;
            });
            return { html: html, rec: grandRec, pay: grandPay };
        }

        function resetTable(message, isError) {
            $('#scrollBody').html(emptyRow(message, isError));
            $('#receiptTotal').text('0.00');
            $('#paymentTotal').text('0.00');
            if (showCashBalance) {
                $('#openingBalance').text('0.00');
                $('#closingBalance').text('0.00');
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
            const groups = groupByLedger(printRows);
            let body = '';
            let grandRec = 0;
            let grandPay = 0;
            groups.forEach(function(group) {
                body += `<tr><td colspan="5" style="font-weight:bold; padding-top:10px;">${group.name}</td></tr>`;
                let rec = 0;
                let pay = 0;
                group.rows.forEach(function(row, idx) {
                    const r = parseFloat(row.Receipt_Amt) || 0;
                    const p = parseFloat(row.Payment_Amt) || 0;
                    rec += r;
                    pay += p;
                    body += `<tr>
                        <td>${idx + 1}</td>
                        <td>${row.Doc_No ?? ''}</td>
                        <td>${row.Particulars ?? ''}</td>
                        <td style="text-align:right;">${fmtAmt(r)}</td>
                        <td style="text-align:right;">${fmtAmt(p)}</td>
                    </tr>`;
                });
                body += `<tr>
                    <td colspan="3" style="font-weight:bold;">TOTAL :</td>
                    <td style="text-align:right;font-weight:bold;">${fmtTot(rec)}</td>
                    <td style="text-align:right;font-weight:bold;">${fmtTot(pay)}</td>
                </tr>`;
                grandRec += rec;
                grandPay += pay;
            });
            body += `<tr>
                <td colspan="3" style="font-weight:bold;">Grand Total</td>
                <td style="text-align:right;font-weight:bold;">${fmtTot(grandRec)}</td>
                <td style="text-align:right;font-weight:bold;">${fmtTot(grandPay)}</td>
            </tr>`;
            const w = window.open('', '_blank');
            w.document.write(`<!DOCTYPE html><html><head><title>${reportTitle}</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                    th { border-bottom: 1px solid #000; padding: 4px 6px; text-align: left; }
                    td { padding: 3px 6px; }
                </style></head><body>
                <h3>${orgName || 'Smart Inventory'}</h3>
                <h4>${reportTitle}</h4>
                <p>${branchName ? branchName + ' &nbsp;|&nbsp; ' : ''}Date: ${asOn} &nbsp;|&nbsp; ${userText}</p>
                ${showCashBalance ? `<p style="text-align:right; font-weight:bold; margin: 0 0 8px 0;">Opening Balance: ${fmtTot(printOpening)}</p>` : ''}
                <table>
                    <thead><tr>
                        <th>Sl No</th><th>${docLabel}</th><th>Particular</th>
                        <th style="text-align:right;">${leftAmtLabel}</th>
                        <th style="text-align:right;">${rightAmtLabel}</th>
                    </tr></thead>
                    <tbody>${body}</tbody>
                ${showCashBalance ? `<p style="text-align:right; font-weight:bold; margin: 12px 0 0 0;">Closing Balance: ${fmtTot(printClosing)}</p>` : ''}
                </body></html>`);
            w.document.close();
            w.focus();
            w.print();
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
                const built = buildBody(printRows);
                $('#scrollBody').html(built.html);
                $('#receiptTotal').text(fmtTot(built.rec));
                $('#paymentTotal').text(fmtTot(built.pay));
                if (showCashBalance) {
                    $('#openingBalance').text(fmtTot(printOpening));
                    $('#closingBalance').text(fmtTot(printClosing));
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
