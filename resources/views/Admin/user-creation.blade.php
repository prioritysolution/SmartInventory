@extends('Dashboard.Layouts.layout')

@push('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h6 class="ps-2">User Management</h6>
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
                        <table id="userTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>User Code</th>
                                    <th>User Name</th>
                                    <th>Full Name</th>
                                    <th>Contact No</th>
                                    <th>Mail</th>
                                    <th>Under Group</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $key => $user)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $user->User_Code }}</td>
                                        <td>{{ $user->User_Name }}</td>
                                        <td>{{ $user->User_FullName }}</td>
                                        <td>{{ $user->Contact_No }}</td>
                                        <td>{{ $user->Email_Id }}</td>
                                        <td>{{ $user->UGrp_Name }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm editRow"
                                                data-id="{{ $user->User_Id }}"
                                                data-code="{{ $user->User_Code }}"
                                                data-username="{{ $user->User_Name }}"
                                                data-fullname="{{ $user->User_FullName }}"
                                                data-mobile="{{ $user->Contact_No }}"
                                                data-mail="{{ $user->Email_Id }}"
                                                data-grpid="{{ $user->UGrp_Id }}"
                                                data-status="{{ $user->Status_Cd }}">
                                                Edit
                                            </button>
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

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content" style="min-height: 60vh;">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="userId">

                    <div class="row g-4">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">User Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="userCode" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">User Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="userName" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="fullName" maxlength="100" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="mobile" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mail</label>
                            <input type="email" class="form-control form-control-lg" id="mail" maxlength="25" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password<span class="text-danger" id="passwordRequired">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="password" maxlength="20" autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Under Role<span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="grpId">
                                <option value="">Select Role</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->UGrp_Id }}">{{ $group->UGrp_Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="statusDiv" style="display:none;">
                            <label class="form-label">User Status<span class="text-danger">*</span></label>
                            <select class="form-select" id="userStatus">
                                @foreach ($statusOptions as $option)
                                    <option value="{{ $option->Value_Id }}">{{ $option->Value_Name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveUser">Save</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {

        $('#userTable').DataTable();

        $('#grpId').select2({
            placeholder: 'Select Role',
            allowClear: true,
            dropdownParent: $('#userModal')
        });

        $('#userStatus').select2({
            placeholder: 'Select Status',
            allowClear: true,
            dropdownParent: $('#userModal')
        });

        $(document).on('click', '.editRow', function () {
            $('#userId').val($(this).data('id'));
            $('#userCode').val($(this).data('code')).prop('disabled', true);
            $('#userName').val($(this).data('username')).prop('disabled', true);
            $('#fullName').val($(this).data('fullname')).prop('disabled', false);
            $('#mobile').val($(this).data('mobile')).prop('disabled', false);
            $('#mail').val($(this).data('mail')).prop('disabled', false);
            $('#password').val('').prop('disabled', false);
            $('#grpId').val($(this).data('grpid')).trigger('change').prop('disabled', false);
            $('#userStatus').val($(this).data('status')).trigger('change');

            $('#passwordRequired').hide();
            $('#statusDiv').show();
            $('#modalTitle').text('Edit User');
            $('#saveUser').text('Update');
            $('#userModal').modal('show');
        });

        $('#saveUser').click(function () {
            if (!validateForm()) return;

            $(this).prop('disabled', true).text('Saving...');

            let userId = $('#userId').val();
            let url = userId ? `/user-creation/${userId}` : "{{ route('user-creation.store') }}";

            let data = {
                _token:    "{{ csrf_token() }}",
                user_code: $('#userCode').val(),
                user_name: $('#userName').val(),
                full_name: $('#fullName').val(),
                mobile:    $('#mobile').val(),
                mail:      $('#mail').val(),
                password:  $('#password').val() || null,
                grp_id:    $('#grpId').val()                            
            };

            if (userId) {
                data._method = 'PUT';
                data.status  = $('#userStatus').val();
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#userModal').modal('hide');
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                },
                error: function (xhr) {
                    $('#saveUser').prop('disabled', false).text(userId ? 'Update' : 'Save');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors;
                        Swal.fire('Validation Error', errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.error || 'Validation failed'), 'error');
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save user', 'error');
                    }
                }
            });
        });

    });

    function validateForm() {
        if (!$('#userCode').val())                          { Swal.fire('Validation Error', 'User Code required', 'error'); return false; }
        if (!$('#userName').val())                          { Swal.fire('Validation Error', 'User Name required', 'error'); return false; }
        if (!$('#fullName').val())                          { Swal.fire('Validation Error', 'Full Name required', 'error'); return false; }
        if (!$('#mobile').val())                            { Swal.fire('Validation Error', 'Mobile required', 'error'); return false; }
        if (!/^\d{10}$/.test($('#mobile').val()))           { Swal.fire('Validation Error', 'Mobile must be exactly 10 digits', 'error'); return false; }
        if (!$('#userId').val() && !$('#password').val())   { Swal.fire('Validation Error', 'Password required', 'error'); return false; }
        if (!$('#grpId').val())                             { Swal.fire('Validation Error', 'User Role required', 'error'); return false; }
        if ($('#userId').val() && !$('#userStatus').val())  { Swal.fire('Validation Error', 'User Status required', 'error'); return false; }
        return true;
    }

    function openAddModal() {
        $('#userId').val('');
        $('#userCode, #userName, #fullName, #mobile, #mail, #password').val('').prop('disabled', false);
        $('#grpId, #userStatus').val('').trigger('change');
        $('#grpId').prop('disabled', false);
        $('#passwordRequired').show();
        $('#statusDiv').hide();
        $('#modalTitle').text('Add New User');
        $('#saveUser').prop('disabled', false).text('Save');
        $('#userModal').modal('show');
    }
</script>
@endpush
