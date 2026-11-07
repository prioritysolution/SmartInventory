@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class=" ps-2">User Roles & Permissions</h6>

                <button class="btn btn-primary" onclick="openAddModal()">
                    + Add New
                </button>
            </div>

            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">

                        <table id="groupTable" class="table table-bordered datatable">

                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>User Group</th>
                                    <th>Description</th>
                                    <th>Is Admin</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($groups as $key => $group)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $group->UGrp_Name }}</td>
                                        <td>{{ $group->UGrp_Description }}</td>
                                        <td>{{ $group->Is_Admin ? 'Yes' : 'No' }}</td>

                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm editGroup" data-id="{{ $group->UGrp_Id }}"
                                                data-name="{{ $group->UGrp_Name }}"
                                                data-desc="{{ $group->UGrp_Description }}"
                                                data-admin="{{ $group->Is_Admin }}">
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



    <!-- MODAL -->

    <div class="modal fade" id="groupModal">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add User Group</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <!-- LEFT FORM -->

                        <div class="col-md-4 border-end pe-md-5 mb-4 mb-md-0">

                            <input type="hidden" id="grp_id">

                            <div class="mb-3">
                                <label>User Group <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="grp_name" autocomplete="off">
                            </div>

                            <div class="mb-4">
                                <label>Description</label>
                                <textarea class="form-control" id="grp_desc" rows="3"></textarea>
                            </div>

                            <div class="form-check ">
                                <input type="checkbox" class="form-check-input" id="is_admin">
                                <label class="form-check-label">Is Admin</label>
                            </div>

                        </div>


                        <!-- RIGHT MENU PERMISSION -->

                        <div class="col-md-8 ps-md-4 ">
                            <h6 class="mb-3">Select Role</h6>
                            <div style="max-height:400px; overflow:auto">
                                @php
                                    $parents = [];
                                    foreach ($menus as $menu) {
                                        $isParentRow = $menu->Child_Id === null || $menu->Child_Id === '';
                                        if ($isParentRow && ($menu->Parraint_Name ?? '') !== '') {
                                            $parents[] = $menu;
                                        }
                                    }
                                    usort($parents, function ($a, $b) {
                                        $rank = function ($row) {
                                            $name = strtolower(trim((string) ($row->Parraint_Name ?? '')));
                                            return $name === 'dashboard' ? 0 : 1;
                                        };
                                        $cmp = $rank($a) <=> $rank($b);
                                        if ($cmp !== 0) {
                                            return $cmp;
                                        }
                                        return ((int) $a->Parraint_Id) <=> ((int) $b->Parraint_Id);
                                    });
                                @endphp
                                @foreach ($parents as $parent)
                                    @php
                                        $parentId = (string) ((int) $parent->Parraint_Id);
                                        $childCount = 0;
                                        foreach ($menus as $child) {
                                            if ((int) $child->Parraint_Id === (int) $parent->Parraint_Id
                                                && $child->Child_Id !== null
                                                && $child->Child_Id !== ''
                                                && (int) $child->Child_Id !== 0) {
                                                $childCount++;
                                            }
                                        }
                                    @endphp
                                    <div class="menu-block mb-2">
                                        <div class="menu-header d-flex align-items-center">
                                            <input type="checkbox" class="parent-menu me-2"
                                                data-parent="{{ $parentId }}">
                                            <span class="menu-title">
                                                {{ $parent->Parraint_Name }}
                                            </span>
                                            @if ($childCount > 0)
                                                <button type="button" class="toggle-menu ms-2"
                                                    data-parent="{{ $parentId }}">
                                                    +
                                                </button>
                                            @endif
                                        </div>

                                        <div class="child-container ms-4 mt-2" id="child-{{ $parentId }}"
                                            style="display:none">
                                            @foreach ($menus as $child)
                                                @if ((int) $child->Parraint_Id === (int) $parent->Parraint_Id
                                                    && $child->Child_Id !== null
                                                    && $child->Child_Id !== ''
                                                    && (int) $child->Child_Id !== 0)
                                                    <div class="child-item mb-1">
                                                        <label class="d-flex align-items-center">
                                                            <input type="checkbox" class="child-menu me-2"
                                                                data-parent="{{ $parentId }}"
                                                                value="{{ $parentId }}|{{ $child->Child_Id }}">
                                                            {{ $child->Child_Name }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal-footer">

                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-primary" id="saveGroup">
                        Save
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .menu-header {
            padding: 8px 12px;
            border-radius: 6px;
        }

        .menu-title {
            font-weight: 600;
        }

        .toggle-menu {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: none;
            background: #7539FF;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .toggle-menu:hover {
            background: #5c2ed6;
        }

        .child-item {
            padding-left: 6px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#groupTable').DataTable();
            /* Expand Collapse */
            $(document).on('click', '.toggle-menu', function() {
                let parent = String($(this).attr('data-parent'));
                let box = $("#child-" + parent);
                if (box.is(':visible')) {
                    box.slideUp();
                    $(this).text('+');
                } else {
                    box.slideDown();
                    $(this).text('-');
                }
            });
            function syncParentCheck(parent) {
                parent = String(parent);
                const children = $('.child-menu[data-parent="' + parent + '"]');
                const $parentBox = $('.parent-menu[data-parent="' + parent + '"]');
                if (!children.length) {
                    return;
                }
                $parentBox.prop('checked', children.filter(':checked').length > 0);
            }

            /* Parent Check */
            $(document).on('change', '.parent-menu', function() {
                let parent = String($(this).attr('data-parent'));
                let children = $('.child-menu[data-parent="' + parent + '"]');
                let box = $("#child-" + parent);
                if ($(this).is(':checked')) {
                    if (children.length) {
                        box.slideDown();
                        children.prop('checked', true);
                        $('.toggle-menu[data-parent="' + parent + '"]').text('-');
                    }
                } else {
                    children.prop('checked', false);
                    box.slideUp();
                    $('.toggle-menu[data-parent="' + parent + '"]').text('+');
                }
            });

            /* Child Check — uncheck parent when every child is unchecked */
            $(document).on('change', '.child-menu', function() {
                syncParentCheck($(this).attr('data-parent'));
            });


          
            /* Edit */
            $(document).on('click', '.editGroup', function() {
                let grpId = $(this).data('id');
                $('#grp_id').val(grpId);
                $('#grp_name').val($(this).data('name'));
                $('#grp_desc').val($(this).data('desc'));
                $('#is_admin').prop('checked', $(this).data('admin') == 1);
                $('#modalTitle').text('Edit User Group');
                $('#saveGroup').text('Update');

                // Reset all checkboxes
                $('.child-menu,.parent-menu').prop('checked', false);
                $('.child-container').hide();
                $('.toggle-menu').text('+');

                // Load group permissions
                $.ajax({
                    url: "{{ url('user-roles/permissions') }}/" + grpId,
                    type: "GET",
                    success: function(permissions) {
                        console.log('Permissions:', permissions);

                        (permissions || []).forEach(function(perm) {
                            const menuId = String(perm.Menu_Id);
                            const subId = perm.SubMenu_Id;
                            const childCount = $('#child-' + menuId + ' .child-menu').length;
                            const isParentOnly = subId === null || subId === '' || Number(subId) === 0;

                            if (isParentOnly) {
                                if (childCount === 0) {
                                    $('.parent-menu[data-parent="' + menuId + '"]').prop('checked', true);
                                }
                                return;
                            }

                            const checkbox = $('.child-menu[value="' + menuId + '|' + subId + '"]');
                            if (checkbox.length > 0) {
                                checkbox.prop('checked', true);
                                $('#child-' + menuId).show();
                                $('.toggle-menu[data-parent="' + menuId + '"]').text('-');
                            }
                        });
                        $('.parent-menu').each(function() {
                            syncParentCheck($(this).attr('data-parent'));
                        });
                        $('#groupModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error loading permissions:', xhr);
                        $('#groupModal').modal('show');
                    }
                });
            });



            /* Save */
            $('#saveGroup').click(function() {
                if (!IS_ADMIN && $('#grp_id').val()) {
                    Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'warning');
                    return;
                }
                let menus = [];
                $('.child-menu:checked').each(function() {
                    let val = String($(this).val() || '').split('|');
                    if (!val[0] || !val[1]) return;
                    menus.push({
                        parent: val[0],
                        child: val[1]
                    });
                });
                $('.parent-menu:checked').each(function() {
                    let parent = String($(this).attr('data-parent'));
                    let hasChildren = $('#child-' + parent + ' .child-menu').length;
                    if (hasChildren === 0) {
                        menus.push({
                            parent: parent,
                            child: 0
                        });
                    }
                });
                let grp_id = $('#grp_id').val();
                $.ajax({
                    url: "{{ route('user-roles.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        grp_id: grp_id,
                        grp_name: $('#grp_name').val(),
                        grp_desc: $('#grp_desc').val(),
                        is_admin: $('#is_admin').is(':checked') ? 1 : 0,
                        menus: menus,
                        mode: grp_id ? 2 : 1
                    },
                    success: function(res) {
                        $('#groupModal').modal('hide');
                        Swal.fire(
                            'Success',
                            res.message,
                            'success'
                        ).then(() => location.reload());
                    },
                  
                    error: function(xhr) {
                       let msg = 'Something went wrong';
                       if (xhr.responseJSON) {
                        msg = xhr.responseJSON.error || xhr.responseJSON.message || msg;
                     }
                        Swal.fire('Error', msg, 'error');
                    }

                });
            });
        });

        function openAddModal() {
            $('#grp_id').val('');
            $('#grp_name').val('');
            $('#grp_desc').val('');
            $('#is_admin').prop('checked', false);
            $('.child-menu,.parent-menu').prop('checked', false);
            $('.child-container').hide();
            $('.toggle-menu').text('+');
            $('#modalTitle').text('Add User Group');
            $('#saveGroup').text('Save');
            $('#groupModal').modal('show');
        }
    </script>
@endpush
