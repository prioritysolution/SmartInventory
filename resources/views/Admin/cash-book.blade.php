@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Cash Book</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="frmDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
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
                    <div class="table-responsive report-scroll">
                        <table id="cashBookTable" class="table table-bordered table-sm cash-book-table">
                            <thead class="thead-light">
                                <tr>
                                    <th colspan="4" class="text-center">Receipt</th>
                                    <th colspan="3" class="text-center">Payment</th>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <th>GL Head</th>
                                    <th>Particulars</th>
                                    <th class="text-end">Amount</th>
                                    <th>GL Head</th>
                                    <th>Particulars</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Select dates and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot id="reportFoot"></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const orgName = @json($org_name);
        const branchName = @json($branch_name);
        const searchUrl = @json(route('cash-book.search'));
        let printData = null;

        function fmtAmt(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function fmtDate(val) {
            return (window.siDate && siDate.toDisplay) ? siDate.toDisplay(val) : (val || '');
        }

        function resetTable(message, isError) {
            printData = null;
            $('#printBtn').prop('disabled', true);
            const cls = isError ? 'text-danger' : 'text-muted';
            $('#reportBody').html(`<tr><td colspan="7" class="text-center ${cls}">${message}</td></tr>`);
            $('#reportFoot').html('');
        }

        function pairByDate(rows) {
            const groups = [];
            const map = {};
            (rows || []).forEach(function (row) {
                const key = row.Vou_Date || '';
                if (!map[key]) {
                    map[key] = { date: key, receipts: [], payments: [] };
                    groups.push(map[key]);
                }
                if (row.Side === 'R') {
                    map[key].receipts.push(row);
                } else {
                    map[key].payments.push(row);
                }
            });
            return groups;
        }

        function buildPairs(data) {
            const groups = pairByDate(data.rows || []);
            const pairs = [];
            groups.forEach(function (group) {
                const max = Math.max(group.receipts.length, group.payments.length, 1);
                for (let i = 0; i < max; i++) {
                    pairs.push({
                        date: i === 0 ? group.date : '',
                        rec: group.receipts[i] || null,
                        pay: group.payments[i] || null
                    });
                }
            });
            return pairs;
        }

        function cell(row, key) {
            if (!row) return '';
            return row[key] == null ? '' : row[key];
        }

        function renderReport(data) {
            const pairs = buildPairs(data);
            let html = '';
            pairs.forEach(function (row) {
                html += `<tr>
                    <td>${row.date ? fmtDate(row.date) : ''}</td>
                    <td>${cell(row.rec, 'Gl_Head')}</td>
                    <td>${cell(row.rec, 'Particulars')}</td>
                    <td class="text-end">${row.rec ? fmtAmt(row.rec.Amount) : ''}</td>
                    <td>${cell(row.pay, 'Gl_Head')}</td>
                    <td>${cell(row.pay, 'Particulars')}</td>
                    <td class="text-end">${row.pay ? fmtAmt(row.pay.Amount) : ''}</td>
                </tr>`;
            });
            if (!html) {
                html = '<tr><td colspan="7" class="text-center text-muted">No records found</td></tr>';
            }
            $('#reportBody').html(html);

            const rec = parseFloat(data.receipt_total) || 0;
            const pay = parseFloat(data.payment_total) || 0;
            const opening = parseFloat(data.opening) || 0;
            const closing = parseFloat(data.closing) || 0;
            const grand = opening + rec;
            $('#reportFoot').html(`
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Total Receipt</td>
                    <td class="text-end">${fmtAmt(rec)}</td>
                    <td colspan="2" class="text-end">Total Payment</td>
                    <td class="text-end">${fmtAmt(pay)}</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Add Opening Balance</td>
                    <td class="text-end">${fmtAmt(opening)}</td>
                    <td colspan="2" class="text-end">Closing Balance</td>
                    <td class="text-end">${fmtAmt(closing)}</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Grand Total</td>
                    <td class="text-end">${fmtAmt(grand)}</td>
                    <td colspan="2" class="text-end">Grand Total</td>
                    <td class="text-end">${fmtAmt(pay + closing)}</td>
                </tr>
            `);
        }

        function printReport() {
            if (!printData) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            const frm = fmtDate($('#frmDate').val());
            const to = fmtDate($('#toDate').val());
            const pairs = buildPairs(printData);
            let body = '';
            pairs.forEach(function (row) {
                body += `<tr>
                    <td>${row.date ? fmtDate(row.date) : ''}</td>
                    <td>${cell(row.rec, 'Gl_Head')}</td>
                    <td>${cell(row.rec, 'Particulars')}</td>
                    <td style="text-align:right;">${row.rec ? fmtAmt(row.rec.Amount) : ''}</td>
                    <td>${cell(row.pay, 'Gl_Head')}</td>
                    <td>${cell(row.pay, 'Particulars')}</td>
                    <td style="text-align:right;">${row.pay ? fmtAmt(row.pay.Amount) : ''}</td>
                </tr>`;
            });
            const rec = parseFloat(printData.receipt_total) || 0;
            const pay = parseFloat(printData.payment_total) || 0;
            const opening = parseFloat(printData.opening) || 0;
            const closing = parseFloat(printData.closing) || 0;
            const grand = opening + rec;
            siPrint(`<!DOCTYPE html><html><head><title>Cash Book</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    h3, h4, p { margin: 0 0 4px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 4px 6px; }
                    th { background: #f0f0f0; }
                    .sign { margin-top: 36px; display: flex; justify-content: space-between; }
                </style></head><body>
                <h3>${orgName || 'Smart Inventory'}</h3>
                ${branchName ? `<p>${branchName}</p>` : ''}
                <h4>Cash Book From ${frm} To ${to}</h4>
                <table>
                    <thead>
                        <tr>
                            <th colspan="4">Receipt</th>
                            <th colspan="3">Payment</th>
                        </tr>
                        <tr>
                            <th>Date</th><th>GL Head</th><th>Particulars</th><th>Amount</th>
                            <th>GL Head</th><th>Particulars</th><th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>${body}
                        <tr>
                            <td colspan="3" style="text-align:right;font-weight:bold;">Total Receipt</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(rec)}</td>
                            <td colspan="2" style="text-align:right;font-weight:bold;">Total Payment</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(pay)}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:right;font-weight:bold;">Add Opening Balance</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(opening)}</td>
                            <td colspan="2" style="text-align:right;font-weight:bold;">Closing Balance</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(closing)}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:right;font-weight:bold;">Grand Total</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(grand)}</td>
                            <td colspan="2" style="text-align:right;font-weight:bold;">Grand Total</td>
                            <td style="text-align:right;font-weight:bold;">${fmtAmt(pay + closing)}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="sign"><span>Secretary / Treasurer</span><span>Accountant</span></div>
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
                const rows = data && data.rows ? data.rows : [];
                if (!rows.length && !(parseFloat(data.opening) || 0)) {
                    resetTable('No records found', false);
                    return;
                }
                printData = data;
                renderReport(data);
                $('#printBtn').prop('disabled', false);
            }).fail(function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load cash book';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
