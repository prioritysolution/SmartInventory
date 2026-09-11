@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
    <style>
        #supplierLedgerScroll {
            max-height: 220px;
            overflow-y: auto;
        }
        #supplierLedgerScroll thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="mb-3">
                <h6 class="ps-2">{{ $pageTitle }}</h6>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3" id="formTitle">Add Voucher</h6>
                            <input type="hidden" id="vouchId" value="0">

                            <div class="mb-3" id="vouNoDiv" style="display:none;">
                                <label class="form-label">Voucher No</label>
                                <input type="text" class="form-control" id="voucherNo" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Voucher Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="voucherDate"
                                    min="{{ session('year_start') }}"
                                    max="{{ min(date('Y-m-d'), session('year_end')) }}"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $partyLabel }}<span class="text-danger">*</span></label>
                                <select class="form-select" id="partyId">
                                    <option value="">-- {{ $partyLabel }} --</option>
                                    @foreach ($parties as $party)
                                        <option value="{{ $party->Party_Id }}">
                                            {{ $party->Party_Code }} - {{ $party->Party_Name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (in_array($routePrefix, ['supplier-payment', 'customer-collection'], true))
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted" id="supplierDueHint">Due: —</small>
                                        <button type="button" class="btn btn-link btn-sm p-0" id="viewSupplierLedger" disabled>View Ledger</button>
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" step="0.01" min="0.01"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Particulars<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="particulars" maxlength="200"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ref Voucher No</label>
                                <input type="text" class="form-control" id="refVouchNo" maxlength="50"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">Trans Mode<span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transMode" id="transCash"
                                        value="1" checked>
                                    <label class="form-check-label" for="transCash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transMode" id="transBank"
                                        value="2">
                                    <label class="form-check-label" for="transBank">Bank</label>
                                </div>
                            </div>
                            <div class="mb-3" id="bankSelectDiv" style="display:none;">
                                <label class="form-label">Select Bank<span class="text-danger">*</span></label>
                                <select class="form-select" id="bankAccountId">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->Account_Id }}">
                                            {{ $bank->Ledger_Name ?? $bank->Account_Desc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveVoucher">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Last 10 Transactions</h6>
                            <div class="table-responsive">
                                <table class="table table-nowrap" id="voucherTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sl</th>
                                            <th>Date</th>
                                            <th>Voucher No</th>
                                            <th>Name</th>
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
                                                <td>{{ number_format($row->Amount, 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-primary btn-sm editRow"
                                                        data-id="{{ $row->Voucher_Id }}">Edit</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No voucher found</td>
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

    @if (in_array($routePrefix, ['supplier-payment', 'customer-collection'], true))
        @php
            $ledgerTitle = $routePrefix === 'customer-collection' ? 'Customer Ledger' : 'Supplier Ledger';
            $ledgerDueCaption = $routePrefix === 'customer-collection'
                ? 'Amount still receivable (Due)'
                : 'Amount still payable (Due)';
            $ledgerEmpty = $routePrefix === 'customer-collection' ? 'Select a customer' : 'Select a supplier';
        @endphp
        <div class="modal fade" id="supplierLedgerModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="supplierLedgerTitle">{{ $ledgerTitle }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted" id="supplierLedgerPeriod"></div>
                                <div class="fw-semibold">{{ $ledgerDueCaption }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold" id="supplierDueAmount">0.00</div>
                            </div>
                        </div>
                        <div class="table-responsive" id="supplierLedgerScroll">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Date</th>
                                        <th>Particulars</th>
                                        <th>Mode</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="supplierLedgerBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">{{ $ledgerEmpty }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="useSupplierDue" disabled>Use Due Amount</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let skipSupplierLedger = false;
        let partyPayable = 0;
        let editOriginalAmount = 0;
        $(document).ready(function() {
            $('#partyId').select2({
                placeholder: '-- {{ $partyLabel }} --',
                allowClear: true
            });
            $('#bankAccountId').select2({
                placeholder: '-- Select Bank --',
                allowClear: true
            });

            $('input[name="transMode"]').on('change', toggleBank);
            $('#cancelBtn').on('click', resetForm);

            @if (in_array($routePrefix, ['supplier-payment', 'customer-collection'], true))
            const ledgerUrl = "{{ route($routePrefix . '.ledger') }}";
            const ledgerTitlePrefix = "{{ $routePrefix === 'customer-collection' ? 'Customer Ledger' : 'Supplier Ledger' }}";

            function fmtLedgerAmt(n) {
                const v = parseFloat(n);
                if (!v) return '';
                return v.toFixed(2);
            }

            function fmtLedgerDate(val) {
                if (!val) return '';
                if (window.siDate && siDate.toDisplay) return siDate.toDisplay(val);
                const parts = String(val).substring(0, 10).split('-');
                return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : val;
            }

            function openSupplierLedger() {
                const partyId = $('#partyId').val();
                if (!partyId) return;
                const name = $('#partyId option:selected').text();
                $('#supplierLedgerTitle').text(ledgerTitlePrefix + ' — ' + name);
                $('#supplierLedgerPeriod').text('');
                $('#supplierDueAmount').text('...');
                $('#supplierLedgerBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
                $('#useSupplierDue').prop('disabled', true);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierLedgerModal')).show();

                $.get(ledgerUrl, {
                    party_id: partyId,
                    as_on_date: $('#voucherDate').val()
                }).done(function(res) {
                    partyPayable = parseFloat(res.payable) || 0;
                    $('#supplierDueAmount').text(partyPayable.toFixed(2));
                    $('#supplierDueHint').text('Due: ₹ ' + partyPayable.toFixed(2));
                    $('#supplierLedgerPeriod').text('From ' + fmtLedgerDate(res.from_date) + ' to ' + fmtLedgerDate(res.as_on_date));
                    const rows = res.rows || [];
                    if (!rows.length) {
                        $('#supplierLedgerBody').html('<tr><td colspan="7" class="text-center text-muted">No ledger found</td></tr>');
                        return;
                    }
                    let html = '';
                    rows.forEach(function(row, idx) {
                        const bold = parseInt(row.Row_Kind, 10) === 1 ? ' class="fw-bold"' : '';
                        html += `<tr${bold}>
                            <td>${idx + 1}</td>
                            <td>${fmtLedgerDate(row.Trans_Date)}</td>
                            <td>${row.Particulars || ''}</td>
                            <td>${row.Mode_Name || ''}</td>
                            <td class="text-end">${fmtLedgerAmt(row.Debit_Amt)}</td>
                            <td class="text-end">${fmtLedgerAmt(row.Credit_Amt)}</td>
                            <td class="text-end">${row.Balance_Label || ''}</td>
                        </tr>`;
                    });
                    $('#supplierLedgerBody').html(html);
                    $('#useSupplierDue').prop('disabled', partyPayable <= 0);
                }).fail(function(xhr) {
                    $('#supplierDueAmount').text('0.00');
                    $('#supplierDueHint').text('Due: —');
                    $('#supplierLedgerBody').html('<tr><td colspan="7" class="text-center text-danger">' +
                        (xhr.responseJSON?.message || 'Failed to load ledger') + '</td></tr>');
                });
            }

            $('#partyId').on('change', function() {
                const partyId = $(this).val();
                $('#viewSupplierLedger').prop('disabled', !partyId);
                if (!partyId) {
                    partyPayable = 0;
                    $('#supplierDueHint').text('Due: —');
                    return;
                }
                if (skipSupplierLedger) return;
                openSupplierLedger();
            });

            $('#viewSupplierLedger').on('click', openSupplierLedger);
            $('#useSupplierDue').on('click', function() {
                if (partyPayable > 0) {
                    $('#amount').val(partyPayable.toFixed(2));
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierLedgerModal')).hide();
            });
            @endif

            $(document).on('click', '.editRow', function() {
                const id = $(this).data('id');
                $.get("{{ url($routePrefix . '/details') }}/" + id, function(d) {
                    $('#vouchId').val(d.Voucher_Id);
                    $('#voucherNo').val(d.Vou_No);
                    $('#vouNoDiv').show();
                    $('#voucherDate').val(d.Vou_Date);
                    skipSupplierLedger = true;
                    $('#partyId').val(d.Party_Id).trigger('change');
                    skipSupplierLedger = false;
                    editOriginalAmount = parseFloat(d.Amount) || 0;
                    $('#amount').val(d.Amount);
                    $('#particulars').val(d.Particulars);
                    $('#refVouchNo').val(d.Ref_Vou_No);
                    if (String(d.Vou_Mode) === '2') {
                        $('#transBank').prop('checked', true);
                        $('#bankSelectDiv').show();
                        $('#bankAccountId').val(d.Bank_Ledg).trigger('change');
                    } else {
                        $('#transCash').prop('checked', true);
                        $('#bankSelectDiv').hide();
                        $('#bankAccountId').val('').trigger('change');
                    }
                    $('#formTitle').text('Edit Voucher');
                    $('#saveVoucher').text('Update');
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load voucher', 'error');
                });
            });

            $('#saveVoucher').on('click', function() {
                if (!$('#voucherDate').val()) {
                    Swal.fire('Error', 'Voucher Date is required', 'error');
                    return;
                }
                if (!$('#partyId').val()) {
                    Swal.fire('Error', '{{ $partyLabel }} is required', 'error');
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
                if ($('input[name="transMode"]:checked').val() === '2' && !$('#bankAccountId').val()) {
                    Swal.fire('Error', 'Please select a Bank', 'error');
                    return;
                }

                const isEdit = parseInt($('#vouchId').val() || 0, 10) > 0;
                const saveData = {
                    _token: "{{ csrf_token() }}",
                    vouch_id: $('#vouchId').val() || 0,
                    voucher_date: $('#voucherDate').val(),
                    party_id: $('#partyId').val(),
                    trans_mode: $('input[name="transMode"]:checked').val(),
                    amount: $('#amount').val(),
                    particulars: $('#particulars').val().trim(),
                    ref_vouch_no: $('#refVouchNo').val().trim(),
                    bank_id: $('#bankAccountId').val() || 0
                };

                function postVoucher() {
                    $('#saveVoucher').prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
                    $.ajax({
                        url: "{{ route($routePrefix . '.store') }}",
                        type: 'POST',
                        data: saveData,
                        success: function(res) {
                            Swal.fire('Success', res.message, 'success').then(() => location.reload());
                        },
                        error: function(xhr) {
                            $('#saveVoucher').prop('disabled', false).text(isEdit ? 'Update' : 'Save');
                            Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message ||
                                'Failed to save', 'error');
                        }
                    });
                }

                @if (in_array($routePrefix, ['supplier-payment', 'customer-collection'], true))
                const dueWord = "{{ $routePrefix === 'customer-collection' ? 'receivable' : 'payable' }}";
                $.get(ledgerUrl, {
                    party_id: saveData.party_id,
                    as_on_date: saveData.voucher_date
                }).done(function(res) {
                    const currentDue = parseFloat(res.payable) || 0;
                    const allowed = currentDue + (isEdit ? editOriginalAmount : 0);
                    partyPayable = currentDue;
                    $('#supplierDueHint').text('Due: ₹ ' + currentDue.toFixed(2));
                    if (amt > allowed + 0.009) {
                        Swal.fire({
                            title: 'Amount is more than due',
                            text: 'Due is ₹ ' + allowed.toFixed(2) + '. You entered ₹ ' + amt.toFixed(2) +
                                '. Do you want to save more than the amount still ' + dueWord + '?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'OK',
                            cancelButtonText: 'Cancel'
                        }).then(function(result) {
                            if (result.isConfirmed) postVoucher();
                        });
                        return;
                    }
                    postVoucher();
                }).fail(function() {
                    Swal.fire('Error', 'Failed to check due amount', 'error');
                });
                @else
                postVoucher();
                @endif
            });
        });

        function toggleBank() {
            if ($('input[name="transMode"]:checked').val() === '2') {
                $('#bankSelectDiv').show();
            } else {
                $('#bankSelectDiv').hide();
                $('#bankAccountId').val('').trigger('change');
            }
        }

        function resetForm() {
            $('#vouchId').val(0);
            $('#voucherNo').val('');
            $('#vouNoDiv').hide();
            $('#voucherDate').val('{{ date('Y-m-d') }}');
            skipSupplierLedger = true;
            $('#partyId, #bankAccountId').val('').trigger('change');
            skipSupplierLedger = false;
            partyPayable = 0;
            editOriginalAmount = 0;
            if ($('#supplierDueHint').length) {
                $('#supplierDueHint').text('Due: —');
                $('#viewSupplierLedger').prop('disabled', true);
            }
            $('#amount, #particulars, #refVouchNo').val('');
            $('#transCash').prop('checked', true);
            $('#bankSelectDiv').hide();
            $('#formTitle').text('Add Voucher');
            $('#saveVoucher').prop('disabled', false).text('Save');
        }
    </script>
@endpush
