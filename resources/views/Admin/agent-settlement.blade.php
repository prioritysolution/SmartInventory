@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2 mb-3">Agent Settlement</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Settlement Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="settleDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Token <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="settleToken" maxlength="20" placeholder="Enter agent token">
                        </div>
                        <div class="col-md-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-success" id="settleBtn" disabled>
                                <i class="fas fa-check me-1"></i> Settle
                            </button>
                        </div>
                    </div>
                    <div id="agentInfo" class="mt-2 text-muted small"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive report-scroll">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Payment Mode</th>
                                    <th class="text-end">Net Amount</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Enter token to load unsettled sales</td>
                                </tr>
                            </tbody>
                            <tfoot id="reportFoot" class="d-none">
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Total</td>
                                    <td class="text-end" id="netTotalCell">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3 settlement-summary">
                <div class="col-md-4">
                    <div class="settlement-mode-box settlement-mode-cash">
                        <div class="mode-label">Cash Total</div>
                        <div class="mode-amount" id="cashTotal">₹ 0.00</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="settlement-mode-box settlement-mode-bank">
                        <div class="mode-label">Bank Total</div>
                        <div class="mode-amount" id="bankTotal">₹ 0.00</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="settlement-mode-box settlement-mode-credit">
                        <div class="mode-label">Credit Total</div>
                        <div class="mode-amount" id="creditTotal">₹ 0.00</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 d-none" id="denomCard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h6 class="mb-2">Cash Denomination (from Agent)</h6>
                            <div class="denom-box" id="denomPipeLines"></div>
                            <div class="mt-2 fw-bold">Total: <span id="denomTotal">₹ 0.00</span></div>
                        </div>
                        <div class="text-muted small">
                            <div>Date: <span id="denomDate">-</span></div>
                            <div>Token: <span id="denomToken">-</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .settlement-mode-box {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px 18px;
        background: #fff;
        border-left-width: 4px;
    }
    .settlement-mode-cash { border-left-color: #16a34a; }
    .settlement-mode-bank { border-left-color: #2563eb; }
    .settlement-mode-credit { border-left-color: #ea580c; }
    .settlement-mode-box .mode-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
    }
    .settlement-mode-box .mode-amount {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
    }
    .denom-box {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        max-width: 240px;
    }
    .denom-box .denom-row {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        min-height: 40px;
        border-bottom: 1px solid #e2e8f0;
    }
    .denom-box .denom-row:last-child { border-bottom: 0; }
    .denom-box .denom-note {
        padding: 8px 12px;
        font-size: 1rem;
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        text-align: center;
        color: #0f172a;
    }
    .denom-box .denom-sep {
        width: 1px;
        align-self: stretch;
        background: #94a3b8;
    }
    .denom-box .denom-qty {
        padding: 8px 12px;
        text-align: center;
        font-size: 1rem;
        font-weight: 600;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: #0f172a;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const searchUrl = "{{ route('agent-settlement.search') }}";
    const settleUrl = "{{ route('agent-settlement.store') }}";
    const csrfToken = "{{ csrf_token() }}";
    let reportRows = [];

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function calcModeTotals(data) {
        const totals = { cash: 0, bank: 0, credit: 0, net: 0 };
        (data || []).forEach((row) => {
            const amt = parseFloat(row.Net_Amt) || 0;
            const mode = parseInt(row.Pay_Mode, 10) || 0;
            totals.net += amt;
            if (mode === 1) totals.cash += amt;
            else if (mode === 2) totals.bank += amt;
            else if (mode === 3) totals.credit += amt;
        });
        return totals;
    }

    function updateSummary(data) {
        const totals = calcModeTotals(data);
        $('#cashTotal').text('₹ ' + fmtAmt(totals.cash));
        $('#bankTotal').text('₹ ' + fmtAmt(totals.bank));
        $('#creditTotal').text('₹ ' + fmtAmt(totals.credit));
        $('#netTotalCell').text(fmtAmt(totals.net));
        if ((data || []).length) {
            $('#reportFoot').removeClass('d-none');
        } else {
            $('#reportFoot').addClass('d-none');
        }
    }

    function resetResults(message, isError) {
        reportRows = [];
        $('#agentInfo').text('');
        $('#reportBody').html(
            `<tr><td colspan="6" class="text-center ${isError ? 'text-danger' : 'text-muted'}">${message}</td></tr>`
        );
        updateSummary([]);
        $('#settleBtn').prop('disabled', true);
        $('#denomCard').addClass('d-none');
    }

    function renderDenomination(denom) {
        if (!denom) {
            $('#denomCard').addClass('d-none');
            return;
        }
        const rows = [
            [500, denom.rs_500],
            [200, denom.rs_200],
            [100, denom.rs_100],
            [50, denom.rs_50],
            [20, denom.rs_20],
            [10, denom.rs_10],
            [5, denom.rs_5],
            [1, denom.coin]
        ].map(([note, qty]) => `
            <div class="denom-row">
                <div class="denom-note">${note}</div>
                <div class="denom-sep"></div>
                <div class="denom-qty">${parseInt(qty, 10) || 0}</div>
            </div>
        `).join('');

        $('#denomPipeLines').html(rows);
        $('#denomDate').text(denom.settle_date ? siDate.toDisplay(denom.settle_date) : '-');
        $('#denomTotal').text('₹ ' + fmtAmt(denom.denom_total || 0));
        $('#denomToken').text(denom.token || '-');
        $('#denomCard').removeClass('d-none');
    }

    function renderRows(data) {
        reportRows = data || [];
        if (!reportRows.length) {
            resetResults('No unsettled transactions', false);
            return;
        }
        let html = '';
        reportRows.forEach((row, idx) => {
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Invoice_No ?? ''}</td>
                <td>${row.Invoice_Date ? siDate.toDisplay(row.Invoice_Date) : ''}</td>
                <td>${row.Customer_Name ?? ''}</td>
                <td>${row.Trans_Mode ?? '-'}</td>
                <td class="text-end">${fmtAmt(row.Net_Amt)}</td>
            </tr>`;
        });
        $('#reportBody').html(html);
        updateSummary(reportRows);
        $('#settleBtn').prop('disabled', false);
    }

    function loadByToken() {
        const token = ($('#settleToken').val() || '').trim();
        if (!token) {
            resetResults('Enter token to load unsettled sales', false);
            Swal.fire('Error', 'Enter settlement token', 'error');
            return;
        }

        $('#reportBody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
        $('#settleBtn').prop('disabled', true);
        $.get(searchUrl, { token: token }, function (data) {
            reportRows = data.rows || [];
            const agentName = data.agent_name || '';
            const agentCode = data.agent_code || '';
            if (agentName || agentCode) {
                $('#agentInfo').text(`Agent: ${agentName} (${agentCode})`);
            } else {
                $('#agentInfo').text('');
            }
            renderRows(reportRows);
            renderDenomination(data.denomination || null);
        }).fail(function (xhr) {
            resetResults('Failed to load', true);
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load settlement data', 'error');
        });
    }

    $('#searchBtn').on('click', loadByToken);
    $('#settleToken').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            loadByToken();
        }
    });

    $('#settleBtn').on('click', function () {
        const settleDate = $('#settleDate').val();
        const token = ($('#settleToken').val() || '').trim();

        if (!settleDate) {
            Swal.fire('Error', 'Select settlement date', 'error');
            return;
        }
        if (!token) {
            Swal.fire('Error', 'Enter settlement token', 'error');
            return;
        }
        if (!reportRows.length) {
            Swal.fire('Info', 'No unsettled sales to settle for this token', 'info');
            return;
        }

        const totals = calcModeTotals(reportRows);
        Swal.fire({
            title: 'Are you sure to settle?',
            html: `This will settle all pending sales for this agent.<br><br>
                   <div class="text-start small">
                     Cash: <b>₹ ${fmtAmt(totals.cash)}</b><br>
                     Bank: <b>₹ ${fmtAmt(totals.bank)}</b><br>
                     Credit: <b>₹ ${fmtAmt(totals.credit)}</b><br>
                     Total: <b>₹ ${fmtAmt(totals.net)}</b>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: settleUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    settle_date: settleDate,
                    token: token
                },
                success: function (res) {
                    Swal.fire('Success', res.message || 'Settlement completed', 'success');
                    $('#settleToken').val('');
                    resetResults('Enter token to load unsettled sales', false);
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Settlement failed', 'error');
                }
            });
        });
    });
</script>
@endpush
