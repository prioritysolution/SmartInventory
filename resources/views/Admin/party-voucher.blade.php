@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
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

            $(document).on('click', '.editRow', function() {
                const id = $(this).data('id');
                $.get("{{ url($routePrefix . '/details') }}/" + id, function(d) {
                    $('#vouchId').val(d.Voucher_Id);
                    $('#voucherNo').val(d.Vou_No);
                    $('#vouNoDiv').show();
                    $('#voucherDate').val(d.Vou_Date);
                    $('#partyId').val(d.Party_Id).trigger('change');
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
                $(this).prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
                $.ajax({
                    url: "{{ route($routePrefix . '.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        vouch_id: $('#vouchId').val() || 0,
                        voucher_date: $('#voucherDate').val(),
                        party_id: $('#partyId').val(),
                        trans_mode: $('input[name="transMode"]:checked').val(),
                        amount: $('#amount').val(),
                        particulars: $('#particulars').val().trim(),
                        ref_vouch_no: $('#refVouchNo').val().trim(),
                        bank_id: $('#bankAccountId').val() || 0
                    },
                    success: function(res) {
                        Swal.fire('Success', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        $('#saveVoucher').prop('disabled', false).text(isEdit ? 'Update' : 'Save');
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message ||
                            'Failed to save', 'error');
                    }
                });
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
            $('#partyId, #bankAccountId').val('').trigger('change');
            $('#amount, #particulars, #refVouchNo').val('');
            $('#transCash').prop('checked', true);
            $('#bankSelectDiv').hide();
            $('#formTitle').text('Add Voucher');
            $('#saveVoucher').prop('disabled', false).text('Save');
        }
    </script>
@endpush
