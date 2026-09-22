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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="ps-2">Member & Share Management</h5>
                <button type="button" class="btn btn-secondary" id="memberListBtn">
                    <i class="fa-solid fa-list me-1"></i> Member List
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="mb-1">Member Info</h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Member Type<span class="text-danger">*</span></label>
                            <select class="form-select" id="mem_type">
                                <option value="">Select Member Type</option>
                                @foreach ($memberTypes as $type)
                                    <option value="{{ $type->Value_Id }}">{{ $type->Value_Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ref Member No</label>
                            <input type="text" class="form-control" id="ref_mem_no" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Member Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mem_name" maxlength="50" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Guardian Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gur_name" maxlength="50" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" maxlength="100" autocomplete="off" placeholder="Enter address">
                        </div>

                        @include('Admin.partials.address-master-fields', ['colClass' => 'col-md-3'])

                        <div class="col-md-3">
                            <label class="form-label">Mobile No</label>
                            <input type="text" class="form-control" id="mob_no" maxlength="10" autocomplete="off"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Aadhar No</label>
                            <input type="text" class="form-control" id="adhar_no" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Voter No</label>
                            <input type="text" class="form-control" id="voter_no" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Pan Card</label>
                            <input type="text" class="form-control" id="pan_no" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="mb-1">Admission Block</h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Admission Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="adm_date" max="{{ date('Y-m-d') }}"
                                value="{{ date('Y-m-d') }}">
                            <input type="hidden" id="adm_date_hidden">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Admission Fees<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control calc-label" id="adm_fees" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">No Of Share</label>
                            <input type="number" class="form-control" id="share_no" min="0" max="999999"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Rate Per Share</label>
                            <input type="number" step="0.01" class="form-control calc-label" id="rate_share" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Share Amount</label>
                            <input type="number" step="0.01" class="form-control calc-label" id="share_amt" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control calc-label" id="tot_amt" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ref Voucher No</label>
                            <input type="text" class="form-control" id="ref_voucher" maxlength="50" autocomplete="off">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Transaction Mode<span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-3 pt-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="trans_mode" id="trans_cash"
                                        value="1" checked>
                                    <label class="form-check-label" for="trans_cash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="trans_mode" id="trans_bank"
                                        value="2">
                                    <label class="form-check-label" for="trans_bank">Bank</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3" id="bank_dropdown_block" style="display:none;">
                            <label class="form-label">Bank</label>
                            <select class="form-select" id="bank_id">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->Account_Id }}">{{ $bank->Ledger_Name ?? $bank->Account_Desc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3" id="bank_remarks_block" style="display:none;">
                            <label class="form-label">Bank Remarks</label>
                            <input type="text" class="form-control" id="bank_remarks" maxlength="100" autocomplete="off">
                        </div>

                        <input type="hidden" id="edit_mem_id" value="">
                        <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member List Modal -->
    <div class="modal fade" id="memberListModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" style="max-width:95%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="memberListLoader" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div class="table-responsive" id="memberListTableWrap" style="display:none;">
                        <table id="memberListTable" class="table table-nowrap table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Member No</th>
                                    <th>Member Name</th>
                                    <th>Guardian</th>
                                    <th>Mobile</th>
                                    <th>Adm Date</th>
                                    <th>Shares</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="memberListBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('template/assets/js/address-master-form.js') }}?v=2"></script>
    <script>
        const ADM_FEES  = {{ (float) ($shareConfig->adm_fees  ?? 0) }};
        const RATE_SHARE = {{ (float) ($shareConfig->rate_share ?? 0) }};
        let editMode     = false;

        $(document).ready(function() {

            $('#adm_fees').val(ADM_FEES.toFixed(2));
            $('#rate_share').val(RATE_SHARE.toFixed(2));
            calcShareTotals();

            $('#mem_type').select2({ placeholder: 'Select Member Type', allowClear: true });
            $('#bank_id').select2({ placeholder: 'Select Bank', allowClear: true });

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
                if (!IS_ADMIN && editMode) {
                    Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'warning');
                    return;
                }
                if (editMode) {
                    if (!validateForm()) return;
                    $(this).prop('disabled', true).text('Updating...');
                    const id = $('#edit_mem_id').val();
                    $.ajax({
                        url: `/member-share/${id}`,
                        type: 'POST',
                        data: Object.assign({
                            _token:    '{{ csrf_token() }}',
                            _method:   'PUT',
                            mem_type:  $('#mem_type').val(),
                            mem_name:  $('#mem_name').val().trim(),
                            gur_name:  $('#gur_name').val().trim(),
                            mob_no:    $('#mob_no').val().trim(),
                            address:   $('#address').val().trim(),
                            adhar_no:  $('#adhar_no').val().trim(),
                            voter_no:  $('#voter_no').val().trim(),
                            pan_no:    $('#pan_no').val().trim(),
                            adm_date:  $('#adm_date').prop('disabled') ? $('#adm_date_hidden').val() : $('#adm_date').val(),
                            share_no:  $('#share_no').val() || 0,
                            adm_fees:  $('#adm_fees').val(),
                            rate_share: $('#rate_share').val(),
                            share_amt: $('#share_amt').val(),
                            tot_amt:   $('#tot_amt').val(),
                            ref_mem_no: $('#ref_mem_no').val().trim(),
                            trans_mode: $('input[name="trans_mode"]:checked').val(),
                            bank_id:   $('#bank_id').val() || 0,
                            ref_voucher: $('#ref_voucher').val().trim(),
                            bank_remarks: $('#bank_remarks').val().trim(),
                        }, AddressMasterForm.collect('')),
                        success: function(res) {
                            $('#saveBtn').prop('disabled', false).text('Update');
                            if (res.message) {
                                Swal.fire('Success!', res.message, 'success');
                                clearForm();
                            }
                        },
                        error: function(xhr) {
                            $('#saveBtn').prop('disabled', false).text('Update');
                            const msg = xhr.responseJSON?.errors ?
                                Object.values(xhr.responseJSON.errors)[0][0] :
                                (xhr.responseJSON?.error || 'Failed to update');
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                } else {
                    if (!validateForm()) return;
                    $(this).prop('disabled', true).text('Saving...');
                    $.ajax({
                        url: '/member-share/save',
                        type: 'POST',
                        data: Object.assign({
                            mem_type:    $('#mem_type').val(),
                            ref_mem_no:  $('#ref_mem_no').val().trim(),
                            mem_name:    $('#mem_name').val().trim(),
                            gur_name:    $('#gur_name').val().trim(),
                            address:     $('#address').val().trim(),
                            mob_no:      $('#mob_no').val().trim(),
                            adhar_no:    $('#adhar_no').val().trim(),
                            voter_no:    $('#voter_no').val().trim(),
                            pan_no:      $('#pan_no').val().trim(),
                            adm_date:    $('#adm_date').val(),
                            adm_fees:    $('#adm_fees').val(),
                            share_no:    $('#share_no').val() || 0,
                            rate_share:  $('#rate_share').val(),
                            share_amt:   $('#share_amt').val(),
                            tot_amt:     $('#tot_amt').val(),
                            trans_mode:  $('input[name="trans_mode"]:checked').val(),
                            bank_id:     $('#bank_id').val() || 0,
                            ref_voucher: $('#ref_voucher').val().trim(),
                            bank_remarks: $('#bank_remarks').val().trim(),
                            mode: 1,
                            _token: '{{ csrf_token() }}'
                        }, AddressMasterForm.collect('')),
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
                                Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save member', 'error');
                            }
                        }
                    });
                }
            });

            $('#cancelBtn').on('click', clearForm);

            // Member List
            function destroyMemberDT() {
                if ($.fn.DataTable.isDataTable('#memberListTable')) {
                    $('#memberListTable').DataTable().destroy();
                }
                $('#memberListBody').html('');
                $('#memberListTableWrap').hide();
            }

            $('#memberListBtn').on('click', function() {
                destroyMemberDT();
                $('#memberListLoader').show();
                $('#memberListModal').modal('show');
                $.get("{{ route('member-share.list') }}", function(data) {
                    $('#memberListLoader').hide();
                    $.each(data, function(i, m) {
                        $('#memberListBody').append(
                            `<tr>
                                <td>${i + 1}</td>
                                <td>${m.Mem_No ?? ''}</td>
                                <td>${m.Mem_Name ?? ''}</td>
                                <td>${m.Gur_Name ?? ''}</td>
                                <td>${m.Mob_No ?? ''}</td>
                                <td>${m.Adm_Date ?? ''}</td>
                                <td>${m.Share_No ?? 0}</td>
                                <td class="text-center">
                                    <button class="btn btn-primary btn-sm editMember"
                                        data-id="${m.Mem_Id}"
                                        data-type="${m.Mem_Type ?? ''}"
                                        data-name="${m.Mem_Name ?? ''}"
                                        data-gur="${m.Gur_Name ?? ''}"
                                        data-mob="${m.Mob_No ?? ''}"
                                        data-address="${m.Address ?? ''}"
                                        data-village="${m.Village_Id ?? ''}"
                                        data-ps="${m.Ps_Id ?? ''}"
                                        data-post="${m.Post_Id ?? ''}"
                                        data-dist="${m.Dist_Id ?? ''}"
                                        data-adhar="${m.Adhar_No ?? ''}"
                                        data-voter="${m.Voter_No ?? ''}"
                                        data-pan="${m.Pan_No ?? ''}"
                                        data-admdate="${m.Adm_Date ?? ''}"
                                        data-created="${m.Created_Date ?? ''}"
                                        data-transmode="${m.Trans_Mode ?? 1}"
                                        data-share="${m.Share_No ?? 0}"
                                        data-bankid="${m.Bank_Id ?? 0}"
                                        data-refvoucher="${m.Ref_Vou_No ?? ''}"
                                        data-bankremarks="${m.Bank_Remarks ?? ''}"
                                        data-refmemno="${m.Ref_Mem_No ?? ''}">Edit</button>
                                </td>
                            </tr>`
                        );
                    });
                    $('#memberListTableWrap').show();
                    $('#memberListTable').DataTable({
                        pageLength: 10,
                        sDom: 'fBtlpi',
                        language: {
                            search: '', searchPlaceholder: 'Search...',
                            sLengthMenu: 'Row Per Page _MENU_',
                            info: '_START_ - _END_ of _TOTAL_',
                            paginate: {
                                next: '<i class="isax isax-arrow-right-1"></i>',
                                previous: '<i class="isax isax-arrow-left"></i>'
                            }
                        }
                    });
                }).fail(function() {
                    $('#memberListLoader').hide();
                    Swal.fire('Error', 'Failed to load member list', 'error');
                });
            });

            $('#memberListModal').on('hidden.bs.modal', function() {
                destroyMemberDT();
            });

            // Edit Member — populate main form
            $(document).on('click', '.editMember', function() {
                editMode = true;
                $('#edit_mem_id').val($(this).data('id'));
                $('#mem_type').val($(this).data('type')).trigger('change');
                $('#mem_name').val($(this).data('name'));
                $('#gur_name').val($(this).data('gur'));
                $('#mob_no').val($(this).data('mob'));
                $('#address').val($(this).data('address'));
                AddressMasterForm.setValues('', {
                    village_id: $(this).data('village'),
                    ps_id: $(this).data('ps'),
                    post_id: $(this).data('post'),
                    dist_id: $(this).data('dist')
                });
                $('#adhar_no').val($(this).data('adhar'));
                $('#voter_no').val($(this).data('voter'));
                $('#pan_no').val($(this).data('pan'));
                const admDate = String($(this).data('admdate') || '');
                const today   = '{{ date('Y-m-d') }}';
                const lockAdm = admDate !== today;
                $('#adm_date').val(admDate);
                $('#adm_date_hidden').val(admDate);
                $('#share_no').val($(this).data('share'));
                $('#ref_mem_no').val($(this).data('refmemno'));
                $('#ref_voucher').val($(this).data('refvoucher'));
                $('#bank_remarks').val($(this).data('bankremarks'));
                const transMode = $(this).data('transmode');
                if (transMode == 2) {
                    $('#trans_bank').prop('checked', true);
                    $('#bank_id').val($(this).data('bankid')).trigger('change');
                    $('#bank_dropdown_block, #bank_remarks_block').show();
                } else {
                    $('#trans_cash').prop('checked', true);
                    $('#bank_dropdown_block, #bank_remarks_block').hide();
                }
                calcShareTotals();
                $('#saveBtn').text('Update');
                $('#share_no, #ref_voucher, #bank_remarks').prop('readonly', lockAdm);
                $('#adm_date').prop('disabled', lockAdm);
                $('#adm_fees, #rate_share, #share_amt, #tot_amt').prop('disabled', lockAdm);
                $('input[name="trans_mode"]').prop('disabled', lockAdm);
                $('#bank_id').prop('disabled', lockAdm);
                if (!lockAdm && transMode != 2) $('#bank_dropdown_block, #bank_remarks_block').hide();
                if (lockAdm) {
                    $('#adm_date, #share_no, #ref_voucher, #bank_remarks, #adm_fees, #rate_share, #share_amt, #tot_amt').prop('disabled', true);
                    $('input[name="trans_mode"]').prop('disabled', true);
                    $('#bank_id').prop('disabled', true);
                }
                $('#memberListModal').modal('hide');
                $('html, body').animate({ scrollTop: 0 }, 300);
            });
        });

        function validateForm() {
            if (!$('#mem_type').val())              { Swal.fire('Validation Error', 'Member Type is required', 'error'); return false; }
            if (!$('#mem_name').val().trim())        { Swal.fire('Validation Error', 'Member Name is required', 'error'); return false; }
            if (!$('#gur_name').val().trim())        { Swal.fire('Validation Error', 'Guardian Name is required', 'error'); return false; }
            if (!AddressMasterForm.validate('')) return false;
            if ($('#mob_no').val().trim() && !/^\d{10}$/.test($('#mob_no').val())) { Swal.fire('Validation Error', 'Mobile must be exactly 10 digits', 'error'); return false; }
            if (!$('#adm_date').val())               { Swal.fire('Validation Error', 'Admission Date is required', 'error'); return false; }
            if (!$('#adm_fees').val())               { Swal.fire('Validation Error', 'Admission Fees is required', 'error'); return false; }
            if ($('input[name="trans_mode"]:checked').val() == '2' && !$('#bank_id').val()) {
                Swal.fire('Validation Error', 'Please select a bank', 'error'); return false;
            }
            return true;
        }

        function calcShareTotals() {
            const shares   = parseFloat($('#share_no').val()) || 0;
            const shareAmt = shares * RATE_SHARE;
            const totalAmt = shares > 0 ? ADM_FEES + shareAmt : 0;
            $('#share_amt').val(shareAmt.toFixed(2));
            $('#tot_amt').val(totalAmt.toFixed(2));
        }

        function clearForm() {
            editMode = false;
            $('#edit_mem_id').val('');
            $('#mem_type, #bank_id').val('').trigger('change');
            $('#mem_name, #gur_name, #address, #mob_no, #adhar_no, #voter_no, #pan_no, #ref_mem_no').val('');
            AddressMasterForm.clearValues('');
            $('#share_no, #ref_voucher, #bank_remarks').val('');
            $('#adm_fees').val(ADM_FEES.toFixed(2));
            $('#rate_share').val(RATE_SHARE.toFixed(2));
            calcShareTotals();
            $('#adm_date').val('{{ date('Y-m-d') }}');
            $('#adm_date_hidden').val('');
            $('#trans_cash').prop('checked', true);
            $('#bank_dropdown_block, #bank_remarks_block').hide();
            // Unlock admission block
            $('#mem_type, #mem_name, #gur_name, #mob_no, #address, #adhar_no, #voter_no, #pan_no').prop('disabled', false);
            $('#village_id, #ps_id, #post_id, #pin_code, #dist_id').prop('disabled', false);
            $('#adm_date, #share_no, #ref_voucher, #bank_remarks').prop('readonly', false);
            $('#adm_fees, #rate_share, #share_amt, #tot_amt').prop('disabled', false);
            $('#adm_date').prop('disabled', false);
            $('input[name="trans_mode"]').prop('disabled', false);
            $('#bank_id').prop('disabled', false);
            $('#saveBtn').prop('disabled', false).text('Save');
        }
    </script>
@endpush
