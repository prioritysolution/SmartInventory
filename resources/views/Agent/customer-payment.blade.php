@extends('AgentDashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                        <h4>Customer Payment</h4>
                        <h6>Collect customer dues by Cash or UPI/QR</h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3" id="formTitle">Add Payment</h6>
                        <input type="hidden" id="paymentId" value="0">

                        <div class="mb-3" id="vouNoDiv" style="display:none;">
                            <label class="form-label">Collection No</label>
                            <input type="text" class="form-control" id="voucherNo" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="voucherDate"
                                min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}"
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Customer<span class="text-danger">*</span></label>
                            <select class="form-select" id="partyId">
                                <option value="">-- Select Customer --</option>
                                @foreach ($customers as $party)
                                    <option value="{{ $party->Party_Id }}">
                                        {{ $party->Party_Code }} - {{ $party->Party_Name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="dueHint">Outstanding: ₹ 0.00</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" step="0.01" min="0.01" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Particulars<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="particulars" maxlength="150"
                                value="Customer payment received" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ref No</label>
                            <input type="text" class="form-control" id="refVouchNo" maxlength="50" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Payment Mode<span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transMode" id="transCash" value="1" checked>
                                <label class="form-check-label" for="transCash">Cash</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transMode" id="transBank" value="2">
                                <label class="form-check-label" for="transBank">Bank / UPI</label>
                            </div>
                        </div>

                        <div class="mb-3" id="qrInfoDiv" style="display:none;">
                            <small class="text-muted">For now, QR/UPI reference will be auto-generated if Ref No is blank.</small>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveVoucher">Save Payment</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Last 10 Payments</h6>
                        <div class="table-responsive">
                            <table class="table table-nowrap" id="voucherTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Date</th>
                                        <th>Collection No</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($vouchers as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ dmy($row->Vou_Date_Disp ?? $row->Vou_Date ?? '') }}</td>
                                            <td>{{ $row->Vou_No }}</td>
                                            <td>{{ $row->Party_Name }}</td>
                                            <td>{{ number_format((float) $row->Amount, 2) }}</td>
                                            <td class="text-center">
                                                @if ((int) ($row->Can_Edit ?? 1) === 1)
                                                    <button type="button" class="btn btn-primary btn-sm editRow"
                                                        data-id="{{ $row->Payment_Id ?? $row->Voucher_Id }}">Edit</button>
                                                @else
                                                    <span class="text-muted small">Legacy</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No payment found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
    let currentDue = 0;
    let currentPayable = 0;
    let editOriginalAmount = 0;

    $(document).ready(function () {
        if (typeof hideGlobalLoader === 'function') {
            hideGlobalLoader(true);
        }

        $('#partyId').select2({
            placeholder: '-- Select Customer --',
            allowClear: true
        });
        $('input[name="transMode"]').on('change', toggleBank);
        $('#cancelBtn').on('click', resetForm);
        $('#partyId').on('change', loadCustomerDue);
        $('#voucherDate').on('change', loadCustomerDue);

        $(document).on('click', '.editRow', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ route('agent.customer.payment.details', ['id' => '__ID__']) }}".replace('__ID__', id),
                type: 'GET',
                dataType: 'json',
                global: false,
                success: function (d) {
                    if (String(d.Can_Edit) === '0') {
                        Swal.fire('Info', 'Legacy voucher payments cannot be edited here', 'info');
                        return;
                    }
                    editOriginalAmount = parseFloat(d.Amount) || 0;
                    $('#paymentId').val(d.Payment_Id || d.Voucher_Id);
                    $('#voucherNo').val(d.Vou_No);
                    $('#vouNoDiv').show();
                    $('#voucherDate').val(d.Vou_Date);
                    $('#partyId').val(d.Party_Id).trigger('change');
                    $('#amount').val(d.Amount);
                    $('#particulars').val(d.Particulars);
                    $('#refVouchNo').val(d.Ref_Vou_No || '');
                    if (String(d.Vou_Mode) === '2') {
                        $('#transBank').prop('checked', true);
                        $('#qrInfoDiv').show();
                    } else {
                        $('#transCash').prop('checked', true);
                        $('#qrInfoDiv').hide();
                    }
                    $('#formTitle').text('Edit Payment');
                    $('#saveVoucher').text('Update Payment');
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load payment', 'error');
                }
            });
        });

        $('#saveVoucher').on('click', function () {
            if (!$('#voucherDate').val()) {
                Swal.fire('Error', 'Payment Date is required', 'error');
                return;
            }
            if (!$('#partyId').val()) {
                Swal.fire('Error', 'Please select Customer', 'error');
                return;
            }
            const amt = parseFloat($('#amount').val());
            if (!$('#amount').val() || isNaN(amt) || amt <= 0) {
                Swal.fire('Error', 'Amount must be greater than 0', 'error');
                return;
            }
            if (!$('#particulars').val().trim()) {
                Swal.fire('Error', 'Particulars is required', 'error');
                return;
            }

            const maxPayable = getMaxPayable();
            if (maxPayable <= 0) {
                Swal.fire('Error', 'Customer has no outstanding balance to collect', 'error');
                return;
            }
            if (amt > maxPayable + 0.0001) {
                Swal.fire('Error', 'Amount cannot exceed outstanding ₹ ' + maxPayable.toFixed(2), 'error');
                return;
            }

            const isEdit = parseInt($('#paymentId').val() || 0, 10) > 0;
            const btnLabel = isEdit ? 'Update Payment' : 'Save Payment';
            const payload = {
                _token: "{{ csrf_token() }}",
                payment_id: $('#paymentId').val() || 0,
                voucher_date: $('#voucherDate').val(),
                party_id: $('#partyId').val(),
                trans_mode: $('input[name="transMode"]:checked').val(),
                amount: $('#amount').val(),
                particulars: $('#particulars').val().trim(),
                ref_vouch_no: $('#refVouchNo').val().trim()
            };
            $(this).prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');

            $.ajax({
                url: "{{ route('agent.customer.payment.store') }}",
                type: 'POST',
                dataType: 'json',
                timeout: 60000,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                data: payload,
                success: function (res) {
                    if (typeof hideGlobalLoader === 'function') hideGlobalLoader(true);
                    Swal.fire('Success', res.message || 'Payment saved', 'success').then(() => location.reload());
                },
                error: function (xhr, status) {
                    if (typeof hideGlobalLoader === 'function') hideGlobalLoader(true);
                    let msg = xhr.responseJSON?.error
                        || xhr.responseJSON?.message
                        || (status === 'timeout' ? 'Request timed out. Check server/DB logs.' : null)
                        || 'Failed to save payment';
                    if (xhr.responseJSON?.errors) {
                        const first = Object.values(xhr.responseJSON.errors).flat()[0];
                        if (first) msg = first;
                    }
                    Swal.fire('Error', msg, 'error');
                },
                complete: function () {
                    if (typeof hideGlobalLoader === 'function') hideGlobalLoader(true);
                    $('#saveVoucher').prop('disabled', false).text(btnLabel);
                }
            });
        });
    });

    function getMaxPayable() {
        return Math.round(Math.max(0, currentPayable + editOriginalAmount) * 100) / 100;
    }

    function updateDueHint() {
        const maxPayable = getMaxPayable();
        const $hint = $('#dueHint');
        if (currentDue < -0.009) {
            $hint
                .removeClass('text-muted text-success')
                .addClass('text-danger')
                .text('Credit balance: ₹ ' + Math.abs(currentDue).toFixed(2) + ' (no collection due)');
            $('#amount').attr('max', 0);
            return;
        }
        if (maxPayable <= 0) {
            $hint
                .removeClass('text-danger text-success')
                .addClass('text-muted')
                .text('Outstanding: ₹ 0.00');
            $('#amount').attr('max', 0);
            return;
        }
        $hint
            .removeClass('text-muted text-danger')
            .addClass('text-success')
            .text('Outstanding: ₹ ' + maxPayable.toFixed(2));
        $('#amount').attr('max', maxPayable.toFixed(2));
    }

    function toggleBank() {
        if ($('input[name="transMode"]:checked').val() === '2') {
            $('#qrInfoDiv').show();
        } else {
            $('#qrInfoDiv').hide();
        }
    }

    function loadCustomerDue() {
        const partyId = $('#partyId').val();
        if (!partyId) {
            currentDue = 0;
            currentPayable = 0;
            updateDueHint();
            return;
        }
        $.ajax({
            url: "{{ route('agent.customer.payment.due') }}",
            type: 'GET',
            dataType: 'json',
            global: false,
            data: {
                party_id: partyId,
                as_on: $('#voucherDate').val()
            },
            success: function (res) {
                currentDue = parseFloat(res.due || 0);
                currentPayable = parseFloat(res.payable != null ? res.payable : Math.max(0, currentDue));
                updateDueHint();
            },
            error: function () {
                currentDue = 0;
                currentPayable = 0;
                updateDueHint();
            }
        });
    }

    function resetForm() {
        editOriginalAmount = 0;
        currentDue = 0;
        currentPayable = 0;
        $('#paymentId').val(0);
        $('#voucherNo').val('');
        $('#vouNoDiv').hide();
        $('#voucherDate').val('{{ date('Y-m-d') }}');
        $('#partyId').val('').trigger('change');
        $('#amount').val('');
        $('#particulars').val('Customer payment received');
        $('#refVouchNo').val('');
        $('#transCash').prop('checked', true);
        $('#qrInfoDiv').hide();
        updateDueHint();
        $('#formTitle').text('Add Payment');
        $('#saveVoucher').prop('disabled', false).text('Save Payment');
    }
</script>
@endpush
