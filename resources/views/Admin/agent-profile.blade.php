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

                    <div class="table-responsive">
                        <table id="agentTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Agent Code</th>
                                    <th>Agent Name</th>
                                    <th>Address</th>
                                    <th>Mobile No</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($agents as $key => $agent)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $agent->Agent_Code }}</td>
                                        <td>{{ $agent->Agent_Name }}</td>
                                        <td>{{ $agent->Address }}</td>
                                        <td>{{ $agent->Contact_No }}</td>

                                        <td class="text-center">

                                            <button class="btn btn-primary btn-sm editRow" data-id="{{ $agent->Agent_Id }}"
                                                data-code="{{ $agent->Agent_Code }}" data-name="{{ $agent->Agent_Name }}"
                                                data-address="{{ $agent->Address }}" data-mobile="{{ $agent->Contact_No }}"
                                                data-join="{{ $agent->Join_Date }}" data-limit="{{ $agent->Stock_Limit }}"
                                                data-password="">
                                                Edit
                                            </button>

                                            {{-- <button class="btn btn-danger btn-sm deleteRow"
                                    data-id="{{ $agent->Agent_Id }}">
                                    Delete
                                </button> --}}

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>



    <!-- Agent Modal -->
    <div class="modal fade" id="agentModal" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content" style="min-height: 70vh;">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="agentId">

                    <div class="row g-4">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Agent Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="agentCode" maxlength="4"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Agent Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="agentName" autocomplete="off">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address<span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-lg" id="address" rows="2" autocomplete="off"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile No<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="mobile" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off">
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="joinDate" max="{{ date('Y-m-d') }}" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Limit</label>
                            <input type="number" step="0.01" class="form-control form-control-lg" id="stockLimit" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-control form-control-lg" id="password" autocomplete="off">
                        </div>

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
    <script>
        $(document).ready(function() {

             $('#agentTable').DataTable();// Simple alert
            // Swal.fire('Title', 'Message', 'success' | 'error' | 'warning' | 'info')

            // // With callback (used after save)
            // Swal.fire('Success', res.message, 'success').then(() => resetForm())


            $(document).on('click', '.deleteRow', function() {

                let agentId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes delete it'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: `/agent-profile/${agentId}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function() {

                                Swal.fire('Deleted', 'Agent removed', 'success')
                                    .then(() => location.reload());

                            }
                        })

                    }

                })

            });

            $(document).on('click', '.editRow', function() {

                $('#agentId').val($(this).data('id'));
                $('#agentCode').val($(this).data('code'));
                $('#agentName').val($(this).data('name'));
                $('#address').val($(this).data('address'));
                $('#mobile').val($(this).data('mobile'));
                $('#joinDate').val($(this).data('join'));
                $('#stockLimit').val($(this).data('limit'));

                $('#modalTitle').text('Edit Agent');
                $('#saveAgent').text('Update');

                $('#agentModal').modal('show');

            });


            $('#saveAgent').click(function() {

                if (!validateForm()) return;

                $(this).prop('disabled', true).text('Saving...');

                let agentId = $('#agentId').val();
                let url = agentId ? `/agent-profile/${agentId}` : "{{ route('agent-profile.store') }}";

                let data = {
                    _token: "{{ csrf_token() }}",
                    agent_code: $('#agentCode').val(),
                    agent_name: $('#agentName').val(),
                    address: $('#address').val(),
                    mobile: $('#mobile').val(),
                    join_date: $('#joinDate').val(),
                    stock_limit: $('#stockLimit').val(),
                    password: $('#password').val() || null
                };

                if (agentId) data._method = 'PUT';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(res) {
                        $('#agentModal').modal('hide');
                        Swal.fire('Success', res.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        $('#saveAgent').prop('disabled', false).text(agentId ? 'Update' :
                            'Save');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON?.errors;
                            if (errors) {
                                // Show only the first error
                                let firstError = Object.values(errors)[0][0];
                                Swal.fire('Validation Error', firstError, 'error');
                            }
                        } else {
                            let message = xhr.responseJSON?.message || 'Failed to save agent';
                            Swal.fire('Error', message, 'error');
                        }
                    }

                });
            });


        });

        function validateForm() {

            if (!$('#agentCode').val()) {
                Swal.fire('Validation Error', 'Agent Code required', 'error');
                return false;
            }

            if (!/^\d+$/.test($('#agentCode').val())) {
                Swal.fire('Validation Error', 'Agent Code must be numeric', 'error');
                return false;
            }
            if (!$('#agentName').val()) {
                Swal.fire('Validation Error', 'Agent Name required', 'error');
                return false;
            }

            if (!$('#address').val()) {
                Swal.fire('Validation Error', 'Address required', 'error');
                return false;
            }

            if (!$('#mobile').val()) {
                Swal.fire('Validation Error', 'Mobile required', 'error');
                return false;
            }
            if (!/^\d{10}$/.test($('#mobile').val())) {
                Swal.fire('Validation Error', 'Mobile must be exactly 10 digits', 'error');
                return false;
            }
            if (!$('#joinDate').val()) {
                Swal.fire('Validation Error', 'Joining date required', 'error');
                return false;
            }

            if ($('#stockLimit').val() > 99999) {
                Swal.fire('Validation Error', 'Stock limit must not exceed 99999', 'error');
                return false;
            }
            return true;

        }

        function openAddModal() {

            $('#agentId').val('');
            $('#agentCode').val('');
            $('#agentName').val('');
            $('#address').val('');
            $('#mobile').val('');
            $('#joinDate').val('');
            $('#stockLimit').val('');
            $('#password').val('');

            $('#modalTitle').text('Add New Agent');
            $('#saveAgent').text('Save');

            $('#agentModal').modal('show');

        }
    </script>
@endpush
