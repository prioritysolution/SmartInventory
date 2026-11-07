@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h6 class="ps-2">Agent Profile</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <a href="javascript:void(0);" class="btn btn-primary d-flex align-items-center" onclick="openAddModal()">
                        + Add New
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="agentListContainer">
                        <div class="text-center text-muted py-4">Loading agents...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Agent Modal -->
    <div class="modal fade" id="agentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content" style="min-height: 70vh;">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" id="agentId">
               <div class="row g-3 px-2">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Agent Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="agentName" autocomplete="off"
                                placeholder="Enter agent name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile No</label>
                            <input type="text" class="form-control form-control-lg" id="mobile" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off"
                                placeholder="10-digit mobile number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="joinDate"
                                max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" autocomplete="off">
                        </div>

                        <div class="col-md-6">
                           <label class="form-label fw-semibold">Stock Limit (₹)</label>
                            <input type="number" step="0.01" class="form-control form-control-lg" id="stockLimit"
                                max="99999999.99" autocomplete="off" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                           <label class="form-label fw-semibold">Credit Sell Limit (₹)</label>
                            <input type="number" step="0.01" class="form-control form-control-lg" id="creditSellLimit"
                                max="99999999.99" autocomplete="off" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger" id="passwordRequired">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg" id="password"
                                    autocomplete="off" placeholder="Password"
                                    style="padding-right: 45px;">
                                <i class="fa fa-eye" id="eyeIcon"
                                    style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6c757d; font-size:1.1rem;"></i>
                            </div>
                        </div>



                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" class="form-control form-control-lg" id="address" maxlength="150"
                                autocomplete="off" placeholder="Enter address">
                        </div>

                        @include('Admin.partials.address-master-fields', ['colClass' => 'col-md-4'])

                    </div>
                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveAgent">Save</button>
                </div>

            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('template/assets/js/address-master-form.js') }}?v=5"></script>
    <script>
        $(document).ready(function() {
            let agentListRequest = null;

            function fetchAgentPage(page, search) {
                if (agentListRequest) {
                    agentListRequest.abort();
                }

                agentListRequest = $.ajax({
                    url: "{{ route('agent-profile.list') }}",
                    type: 'GET',
                    data: { page: page, search: search },
                    success: function(html) {
                        $('#agentListContainer').html(html);
                    },
                    error: function(xhr, status) {
                        if (status !== 'abort') {
                            Swal.fire('Error', 'Failed to load agents. Please try again.', 'error');
                        }
                    },
                    complete: function() {
                        agentListRequest = null;
                    }
                });
            }

            $('#agentListContainer').on('click', '.agent-page-link', function(event) {
                event.preventDefault();
                const page = Number($(this).data('page'));
                if (!page || $(this).closest('.page-item').hasClass('disabled') ||
                    $(this).closest('.page-item').hasClass('active')) {
                    return;
                }
                fetchAgentPage(page, $('#agentSearch').val());
            });

            $('#agentListContainer').on('submit', '#agentSearchForm', function(event) {
                event.preventDefault();
                fetchAgentPage(1, $('#agentSearch').val());
            });

            $('#agentListContainer').on('click', '#clearAgentSearch', function() {
                $('#agentSearch').val('');
                fetchAgentPage(1, '');
            });

            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('page');
            window.history.replaceState({}, '', currentUrl);
            fetchAgentPage(1, @json(request('search', '')));

            $('#eyeIcon').on('click', function() {
                let input = $('#password');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            $(document).on('click', '.editRow', function() {
                $('#agentId').val($(this).data('id'));
                $('#agentName').val($(this).data('name'));
                $('#address').val($(this).data('address'));
                $('#mobile').val($(this).data('mobile'));
                $('#joinDate').val($(this).data('join'));
                $('#stockLimit').val($(this).data('limit'));
                $('#creditSellLimit').val($(this).data('credit-limit'));
                AddressMasterForm.setValues('', {
                    village_id: $(this).data('village'),
                    ps_id: $(this).data('ps'),
                    post_id: $(this).data('post'),
                    dist_id: $(this).data('dist')
                });
                $('#password').val('');
                $('#passwordRequired').hide();
                $('#modalTitle').text('Edit Agent');
                $('#saveAgent').text('Update').prop('disabled', false);
                $('#agentModal').modal('show');
            });

            $('#saveAgent').click(function() {
                if (!validateForm()) return;
                $(this).prop('disabled', true).text('Saving...');
                let agentId = $('#agentId').val();
                let url = agentId
                    ? ("{{ url('agent-profile') }}/" + agentId)
                    : "{{ route('agent-profile.store') }}";
                let data = Object.assign({
                    _token: "{{ csrf_token() }}",
                    agent_name: $('#agentName').val(),
                    address: $('#address').val(),
                    mobile: $('#mobile').val(),
                    join_date: $('#joinDate').val(),
                    stock_limit: $('#stockLimit').val(),
                    credit_sell_limit: $('#creditSellLimit').val(),
                    password: $('#password').val() || null
                }, AddressMasterForm.collect(''));
                if (agentId) data._method = 'PUT';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: res => {
                        $('#agentModal').modal('hide');
                        Swal.fire('Success', res.message, 'success').then(() => location
                            .reload());
                    },
                    error: xhr => {
                        $('#saveAgent').prop('disabled', false).text(agentId ? 'Update' :
                            'Save');
                        let msg = xhr.responseJSON?.errors ?
                            Object.values(xhr.responseJSON.errors)[0][0] :
                            (xhr.responseJSON?.message || 'Failed to save agent');
                        Swal.fire('Validation Error', msg, 'error');
                    }
                });
            });

            $('#agentModal').on('hidden.bs.modal', function() {
                $('#agentId, #agentName, #address, #mobile, #stockLimit, #creditSellLimit, #password').val('');
                AddressMasterForm.clearValues('');
                $('#joinDate').val('{{ date('Y-m-d') }}');
                $('#modalTitle').text('Add New Agent');
                $('#saveAgent').text('Save').prop('disabled', false);
                $('#passwordRequired').show();
                $('#password').attr('type', 'password');
                $('#eyeIcon').removeClass('fa-eye-slash').addClass('fa-eye');
            });

        });

        function validateForm() {
            if (!$('#agentName').val()) {
                Swal.fire('Validation Error', 'Agent Name required', 'error');
                return false;
            }
            if (!AddressMasterForm.validate('')) return false;
            if ($('#mobile').val().trim() && !/^\d{10}$/.test($('#mobile').val())) {
                Swal.fire('Validation Error', 'Mobile must be exactly 10 digits', 'error');
                return false;
            }
            if (!$('#joinDate').val()) {
                Swal.fire('Validation Error', 'Joining date required', 'error');
                return false;
            }
            const password = $('#password').val().trim();
            if (!$('#agentId').val() && !password) {
                Swal.fire('Validation Error', 'Password required', 'error');
                return false;
            }
            if (password && (password.length < 6 || password.length > 20)) {
                Swal.fire('Validation Error', 'Password must be between 6 and 20 characters', 'error');
                return false;
            }
            if ($('#stockLimit').val() && $('#stockLimit').val() > 99999999.99) {
                Swal.fire('Validation Error', 'Stock limit must not exceed 99,999,999.99', 'error');
                return false;
            }
            if ($('#creditSellLimit').val() && $('#creditSellLimit').val() > 99999999.99) {
                Swal.fire('Validation Error', 'Credit sell limit must not exceed 99,999,999.99', 'error');
                return false;
            }
            return true;
        }

        function openAddModal() {
            $('#agentId, #agentName, #address, #mobile, #stockLimit, #creditSellLimit, #password').val('');
            AddressMasterForm.clearValues('');
            $('#joinDate').val('{{ date('Y-m-d') }}');
            $('#passwordRequired').show();
            $('#modalTitle').text('Add New Agent');
            $('#saveAgent').text('Save').prop('disabled', false);
            $('#agentModal').modal('show');
        }
    </script>
@endpush
