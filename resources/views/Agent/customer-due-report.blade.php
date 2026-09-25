@extends('AgentDashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Customer Due Report</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">As on Date</label>
                    <input type="date" class="form-control" id="asOnDate"
                        min="{{ $year_start }}" max="{{ $year_end }}"
                        value="{{ min($year_end, date('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Customer</label>
                    <select class="form-select" id="partyId">
                        <option value="">-- Select Customer --</option>
                        @foreach ($customers as $party)
                            <option value="{{ $party->Party_Id }}">
                                {{ $party->Party_Code }} - {{ $party->Party_Name }}
                            </option>
                        @endforeach
                    </select>
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
                <table id="dueTable" class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Code</th>
                            <th>Customer Name</th>
                            <th class="text-end">Due Amount</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Remaining</th>
                            <th>Due Since</th>
                            <th class="text-center">History</th>
                        </tr>
                    </thead>
                    <tbody id="dueBody">
                        <tr><td colspan="8" class="text-center text-muted">Select a customer and click Search</td></tr>
                    </tbody>
                    <tfoot id="dueFoot" class="d-none">
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end" id="totDue">0.00</td>
                            <td class="text-end" id="totPaid">0.00</td>
                            <td class="text-end" id="totRem">0.00</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card d-none" id="historyCard">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0" id="historyTitle">Credit Payment History</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="closeHistoryBtn">Close</button>
            </div>
            <div class="mb-2 text-muted" id="historyRemaining"></div>
            <div class="table-responsive report-scroll">
                <table class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Ref No</th>
                            <th>Mode</th>
                            <th>Particulars</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <tr><td colspan="7" class="text-center text-muted">No history</td></tr>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const searchUrl = "{{ route('agent.report.customer.due.search') }}";
    const historyUrl = "{{ route('agent.report.customer.due.history', ['id' => '__ID__']) }}";
    const orgName = @json($org_name);
    const agentName = @json($agent_name);
    let printRows = [];

    $('#partyId').select2({
        placeholder: '-- Select Customer --',
        allowClear: true,
        width: '100%'
    });

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function remainingClass(n) {
        const v = parseFloat(n) || 0;
        if (v > 0.009) return 'text-danger fw-semibold';
        if (v < -0.009) return 'text-success fw-semibold';
        return '';
    }

    function updateTotals(data) {
        let due = 0, paid = 0, rem = 0;
        (data || []).forEach((r) => {
            due += parseFloat(r.Due_Amt) || 0;
            paid += parseFloat(r.Paid_Amt) || 0;
            rem += parseFloat(r.Remaining_Amt) || 0;
        });
        $('#totDue').text(fmtAmt(due));
        $('#totPaid').text(fmtAmt(paid));
        $('#totRem').text(fmtAmt(rem));
        if ((data || []).length) {
            $('#dueFoot').removeClass('d-none');
        } else {
            $('#dueFoot').addClass('d-none');
        }
    }

    function renderRows(data) {
        if (!data.length) {
            printRows = [];
            $('#dueBody').html('<tr><td colspan="8" class="text-center text-muted">No customers found</td></tr>');
            $('#printBtn').prop('disabled', true);
            updateTotals([]);
            return;
        }
        printRows = data;
        let html = '';
        data.forEach((row, idx) => {
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Party_Code ?? ''}</td>
                <td>${row.Party_Name ?? ''}</td>
                <td class="text-end">${fmtAmt(row.Due_Amt)}</td>
                <td class="text-end">${fmtAmt(row.Paid_Amt)}</td>
                <td class="text-end ${remainingClass(row.Remaining_Amt)}">${fmtAmt(row.Remaining_Amt)}</td>
                <td>${row.Due_Date ? siDate.toDisplay(row.Due_Date) : '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary historyBtn"
                        data-id="${row.Party_Id}">
                        View
                    </button>
                </td>
            </tr>`;
        });
        $('#dueBody').html(html);
        updateTotals(data);
        $('#printBtn').prop('disabled', false);
    }

    function loadHistory(partyId, code, name) {
        $('#historyCard').removeClass('d-none');
        $('#historyTitle').text(`History — ${code} ${name}`);
        $('#historyRemaining').text('Loading...');
        $('#historyBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

        $.get(historyUrl.replace('__ID__', partyId), { as_on: $('#asOnDate').val() }, function (res) {
            const rem = parseFloat(res.remaining) || 0;
            $('#historyRemaining').html(
                `Remaining outstanding: <strong class="${remainingClass(rem)}">₹ ${fmtAmt(rem)}</strong>`
            );
            const rows = res.history || [];
            if (!rows.length) {
                $('#historyBody').html('<tr><td colspan="7" class="text-center text-muted">No credit / payment history</td></tr>');
                return;
            }
            let html = '';
            rows.forEach((row, idx) => {
                const amtClass = row.Trans_Type === 'C' ? 'text-success' : 'text-danger';
                const sign = row.Trans_Type === 'C' ? '-' : '+';
                html += `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Trans_Date ? siDate.toDisplay(row.Trans_Date) : ''}</td>
                    <td>${row.Entry_Type ?? ''}</td>
                    <td>${row.Ref_No ?? ''}</td>
                    <td>${row.Mode_Name ?? '-'}</td>
                    <td>${row.Particulars ?? ''}</td>
                    <td class="text-end ${amtClass}">${sign} ${fmtAmt(row.Amount)}</td>
                </tr>`;
            });
            $('#historyBody').html(html);
            $('html, body').animate({ scrollTop: $('#historyCard').offset().top - 80 }, 300);
        }).fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load history', 'error');
            $('#historyBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load</td></tr>');
        });
    }

    function printReport() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        const asOn = siDate.toDisplay($('#asOnDate').val());
        let due = 0, paid = 0, rem = 0;
        let body = '';
        printRows.forEach((row, idx) => {
            due += parseFloat(row.Due_Amt) || 0;
            paid += parseFloat(row.Paid_Amt) || 0;
            rem += parseFloat(row.Remaining_Amt) || 0;
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Party_Code ?? ''}</td>
                <td>${row.Party_Name ?? ''}</td>
                <td style="text-align:right;">${fmtAmt(row.Due_Amt)}</td>
                <td style="text-align:right;">${fmtAmt(row.Paid_Amt)}</td>
                <td style="text-align:right;">${fmtAmt(row.Remaining_Amt)}</td>
                <td>${row.Due_Date ? siDate.toDisplay(row.Due_Date) : '-'}</td>
            </tr>`;
        });
        body += `<tr>
            <td colspan="3" style="text-align:right;font-weight:bold;">Total</td>
            <td style="text-align:right;font-weight:bold;">${fmtAmt(due)}</td>
            <td style="text-align:right;font-weight:bold;">${fmtAmt(paid)}</td>
            <td style="text-align:right;font-weight:bold;">${fmtAmt(rem)}</td>
            <td></td>
        </tr>`;
        siPrint(`<!DOCTYPE html><html><head><title>Customer Due Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Customer Due Report</h4>
            <p>Agent: ${agentName || ''} &nbsp;|&nbsp; As on: ${asOn}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Code</th><th>Customer Name</th>
                    <th>Due Amount</th><th>Paid Amount</th><th>Remaining</th><th>Due Since</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`);
    }

    function runSearch() {
        const partyId = $('#partyId').val();
        if (!partyId) {
            printRows = [];
            $('#historyCard').addClass('d-none');
            $('#dueBody').html('<tr><td colspan="8" class="text-center text-muted">Select a customer and click Search</td></tr>');
            $('#printBtn').prop('disabled', true);
            updateTotals([]);
            return;
        }

        $('#historyCard').addClass('d-none');
        $('#dueBody').html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
        $('#printBtn').prop('disabled', true);
        $.get(searchUrl, {
            as_on: $('#asOnDate').val(),
            party_id: partyId
        }, function (data) {
            renderRows(data || []);
        }).fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report', 'error');
            $('#dueBody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load</td></tr>');
            updateTotals([]);
        });
    }

    $('#searchBtn').on('click', function () {
        if (!$('#partyId').val()) {
            Swal.fire('Error', 'Please select a customer', 'error');
            return;
        }
        runSearch();
    });
    $('#partyId').on('change', function () {
        if ($('#partyId').val()) {
            runSearch();
        } else {
            printRows = [];
            $('#historyCard').addClass('d-none');
            $('#dueBody').html('<tr><td colspan="8" class="text-center text-muted">Select a customer and click Search</td></tr>');
            $('#printBtn').prop('disabled', true);
            updateTotals([]);
        }
    });

    $(document).on('click', '.historyBtn', function () {
        const partyId = $(this).data('id');
        const row = printRows.find((r) => String(r.Party_Id) === String(partyId));
        loadHistory(partyId, row?.Party_Code || '', row?.Party_Name || '');
    });

    $('#closeHistoryBtn').on('click', function () {
        $('#historyCard').addClass('d-none');
    });

    $('#printBtn').on('click', printReport);
</script>
@endpush
