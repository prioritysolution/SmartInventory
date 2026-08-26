@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
    <style>
        .calc-label {
            background-color: #f8f9fa;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <h5 class="ps-2">Member & Share Management</h5>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Member Info</h6>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Member Type<span class="text-danger">*</span></label>
                            <select class="form-select" id="mem_type">
                                <option value="">Select Member Type</option>
                                @foreach ($memberTypes as $type)
                                    <option value="{{ $type->Value_Id }}">{{ $type->Value_Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ref Member No</label>
                            <input type="text" class="form-control" id="ref_mem_no" maxlength="25">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Member Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mem_name" maxlength="50">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Guardian Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gur_name" maxlength="50">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address<span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" rows="2" maxlength="100"></textarea>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Mobile No<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mob_no" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Aadhar No</label>
                            <input type="text" class="form-control" id="adhar_no" maxlength="25">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Voter No</label>
                            <input type="text" class="form-control" id="voter_no" maxlength="25">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Pan Card</label>
                            <input type="text" class="form-control" id="pan_no" maxlength="25">
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="mb-3">Admission Block</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Admission Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="adm_date" max="{{ date('Y-m-d') }}"
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Admission Fees<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control calc-label" id="adm_fees" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">No Of Share<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="share_no" min="1" max="999999"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Rate Per Share</label>
                            <input type="number" step="0.01" class="form-control calc-label" id="rate_share" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Share Amount<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control calc-label" id="share_amt" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control calc-label" id="tot_amt" readonly>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Transaction Mode<span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trans_mode" id="trans_cash"
                                        value="1" checked>
                                    <label class="form-check-label" for="trans_cash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trans_mode" id="trans_bank"
                                        value="2">
                                    <label class="form-check-label" for="trans_bank">Bank</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ref Voucher No</label>
                            <input type="text" class="form-control" id="ref_voucher" maxlength="50">
                        </div>

                        <div class="col-md-4 mb-3" id="bank_dropdown_block" style="display:none;">
                            <label class="form-label">Bank</label>
                            <select class="form-select" id="bank_id">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->Account_Id }}">{{ $bank->Ledger_Name ?? $bank->Account_Desc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3" id="bank_remarks_block" style="display:none;">
                            <label class="form-label">Bank Remarks</label>
                            <input type="text" class="form-control" id="bank_remarks" maxlength="100">
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
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
        const ADM_FEES = {{ (float) ($shareConfig->adm_fees ?? 0) }};
        const RATE_SHARE = {{ (float) ($shareConfig->rate_share ?? 0) }};

        $(document).ready(function() {

            $('#adm_fees').val(ADM_FEES.toFixed(2));
            $('#rate_share').val(RATE_SHARE.toFixed(2));
            calcShareTotals();

            $('#mem_type').select2({
                placeholder: 'Select Member Type',
                allowClear: true
            });
            $('#bank_id').select2({
                placeholder: 'Select Bank',
                allowClear: true
            });

            $('#share_no').on('input', calcShareTotals);

            $('input[name="trans_mode"]').on('change', function() {
                if ($(this).val() == '2') {
                    $('#bank_dropdown_block, #bank_remarks_block').show();
                } else {
                    $('#bank_dropdown_block, #bank_remarks_block').hide();
                    $('#bank_id').val('').trigger('change');
                    $('#bank_remarks').val('');
                }
            });

            $('#saveBtn').on('click', function() {
                if (!validateForm()) return;

                $(this).prop('disabled', true).text('Saving...');

                $.ajax({
                    url: '/member-share/save',
                    type: 'POST',
                    data: {
                        mem_type: $('#mem_type').val(),
                        ref_mem_no: $('#ref_mem_no').val().trim(),
                        mem_name: $('#mem_name').val().trim(),
                        gur_name: $('#gur_name').val().trim(),
                        address: $('#address').val().trim(),
                        mob_no: $('#mob_no').val().trim(),
                        adhar_no: $('#adhar_no').val().trim(),
                        voter_no: $('#voter_no').val().trim(),
                        pan_no: $('#pan_no').val().trim(),
                        adm_date: $('#adm_date').val(),
                        adm_fees: $('#adm_fees').val(),
                        share_no: $('#share_no').val().trim(),
                        rate_share: $('#rate_share').val(),
                        share_amt: $('#share_amt').val(),
                        tot_amt: $('#tot_amt').val(),
                        trans_mode: $('input[name="trans_mode"]:checked').val(),
                        bank_id: $('#bank_id').val() || 0,
                        ref_voucher: $('#ref_voucher').val().trim(),
                        bank_remarks: $('#bank_remarks').val().trim(),
                        mode: 1,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#saveBtn').prop('disabled', false).text('Save');
                        if (response.message) {
                            Swal.fire('Success!', response.message, 'success');
                            clearForm();
                        }
                    },
                    error: function(xhr) {
                        $('#saveBtn').prop('disabled', false).text('Save');
                        if (xhr.status === 422) {
                            const message = xhr.responseJSON?.errors ?
                                Object.values(xhr.responseJSON.errors)[0][0] :
                                (xhr.responseJSON?.error || '');
                            Swal.fire('Validation Error', message, 'error');
                        } else {
                            Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON
                                ?.message || 'Failed to save member', 'error');
                        }
                    }
                });
            });

            $('#cancelBtn').on('click', clearForm);
        });

        function validateForm() {
            if (!$('#mem_type').val()) {
                Swal.fire('Validation Error', 'Member Type is required', 'error');
                return false;
            }
            if (!$('#mem_name').val().trim()) {
                Swal.fire('Validation Error', 'Member Name is required', 'error');
                return false;
            }
            if (!$('#gur_name').val().trim()) {
                Swal.fire('Validation Error', 'Guardian Name is required', 'error');
                return false;
            }
            if (!$('#address').val().trim()) {
                Swal.fire('Validation Error', 'Address is required', 'error');
                return false;
            }
            if (!$('#mob_no').val().trim()) {
                Swal.fire('Validation Error', 'Mobile No is required', 'error');
                return false;
            }
            if (!/^\d{10}$/.test($('#mob_no').val())) {
                Swal.fire('Validation Error', 'Mobile must be exactly 10 digits', 'error');
                return false;
            }
            if (!$('#adm_date').val()) {
                Swal.fire('Validation Error', 'Admission Date is required', 'error');
                return false;
            }
            if (!$('#adm_fees').val()) {
                Swal.fire('Validation Error', 'Admission Fees is required', 'error');
                return false;
            }
            if (!$('#share_no').val().trim()) {
                Swal.fire('Validation Error', 'No Of Share is required', 'error');
                return false;
            }
            if ($('input[name="trans_mode"]:checked').val() == '2' && !$('#bank_id').val()) {
                Swal.fire('Validation Error', 'Please select a bank', 'error');
                return false;
            }
            return true;
        }

        function calcShareTotals() {
            const shares = parseFloat($('#share_no').val()) || 0;
            const shareAmt = shares * RATE_SHARE;
            const totalAmt = ADM_FEES + shareAmt;
            $('#share_amt').val(shareAmt.toFixed(2));
            $('#tot_amt').val(totalAmt.toFixed(2));
        }

        function clearForm() {
            $('#mem_type, #bank_id').val('').trigger('change');
            $('#mem_name, #gur_name, #address, #mob_no, #adhar_no, #voter_no, #pan_no, #ref_mem_no').val('');
            $('#share_no, #ref_voucher, #bank_remarks').val('');
            $('#adm_fees').val(ADM_FEES.toFixed(2));
            $('#rate_share').val(RATE_SHARE.toFixed(2));
            calcShareTotals();
            $('#adm_date').val('{{ date('Y-m-d') }}');
            $('#trans_cash').prop('checked', true);
            $('#bank_dropdown_block, #bank_remarks_block').hide();
        }
    </script>
@endpush
