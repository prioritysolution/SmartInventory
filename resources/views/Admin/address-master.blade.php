@extends('Dashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="mb-3">
            <h6 class="ps-2">Address Master</h6>
        </div>

        <div class="row">
            <!-- Left: 5 boxes -->
            <div class="col-md-3">
                <div class="d-flex flex-column gap-3">
                    <div class="card chart-box cursor-pointer border-2" id="box-village" onclick="loadSection('village')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height:90px;">
                            <div class="avatar avatar-md bg-primary-subtle rounded">
                                <i class="fa-solid fa-house text-primary fs-18"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Village</h6>
                                <small class="text-muted">Manage villages</small>
                            </div>
                        </div>
                    </div>
                    <div class="card chart-box cursor-pointer border-2" id="box-ps" onclick="loadSection('ps')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height:90px;">
                            <div class="avatar avatar-md bg-success-subtle rounded">
                                <i class="fa-solid fa-shield-halved text-success fs-18"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Police Station</h6>
                                <small class="text-muted">Manage PS</small>
                            </div>
                        </div>
                    </div>
                    <div class="card chart-box cursor-pointer border-2" id="box-post" onclick="loadSection('post')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height:90px;">
                            <div class="avatar avatar-md bg-warning-subtle rounded">
                                <i class="fa-solid fa-envelope text-warning fs-18"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Post Office</h6>
                                <small class="text-muted">Manage post offices</small>
                            </div>
                        </div>
                    </div>
                    <div class="card chart-box cursor-pointer border-2" id="box-pin" onclick="loadSection('pin')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height:90px;">
                            <div class="avatar avatar-md bg-danger-subtle rounded">
                                <i class="fa-solid fa-location-dot text-danger fs-18"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">PIN Code</h6>
                                <small class="text-muted">Manage PIN codes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card chart-box cursor-pointer border-2" id="box-dist" onclick="loadSection('dist')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height:90px;">
                            <div class="avatar avatar-md bg-info-subtle rounded">
                                <i class="fa-solid fa-map text-info fs-18"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">District</h6>
                                <small class="text-muted">Manage districts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Data panel -->
            <div class="col-md-9">
                <div class="card" id="dataPanel" style="display:none;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0" id="panelTitle"></h6>
                            <button class="btn btn-primary btn-sm" id="addNewBtn" onclick="openAddModal()" style="display:none;">
                                + Add New
                            </button>
                        </div>
                        <div id="loadingSpinner" class="text-center py-3" style="display:none;">
                            <div class="spinner-border spinner-border-sm text-primary"></div> Loading...
                        </div>
                        <div class="table-responsive">
                            <table id="addressTable" class="table table-nowrap table-bordered" style="display:none;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sl</th>
                                        <th id="colHeader">Name</th>
                                        <th class="text-center" id="actionCol">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <input type="hidden" id="editType">
                <div class="mb-3">
                    <label class="form-label" id="inputLabel">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="inputName" maxlength="100" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .chart-box { cursor: pointer; transition: all 0.2s; }
    .chart-box:hover, .chart-box.active {
        border-color: #3F6AD8 !important;
        box-shadow: 0 0 0 2px rgba(63,106,216,0.15);
    }
</style>
<script>
    let currentSection = '';
    let addrDT = null;

    const sectionConfig = {
        village : { title: 'Village',        col: 'Village Name' },
        ps      : { title: 'Police Station', col: 'PS Name'      },
        post    : { title: 'Post Office',    col: 'Post Name'    },
        pin     : { title: 'PIN Code',       col: 'PIN Code'     },
        dist    : { title: 'District',       col: 'District Name'},
    };

    function loadSection(type) {
        $('.chart-box').removeClass('active');
        $('#box-' + type).addClass('active');
        currentSection = type;

        $('#dataPanel').show();
        $('#loadingSpinner').show();
        $('#addressTable').hide();

        const cfg = sectionConfig[type];
        $('#panelTitle').text(cfg.title);
        $('#colHeader').text(cfg.col);

        $('#addNewBtn').show();
        if (!$('#actionCol').length) {
            $('#addressTable thead tr').append('<th class="text-center" id="actionCol">Action</th>');
        }
        $('#actionCol').show();

        if (addrDT) { addrDT.destroy(); addrDT = null; }
        $('#tableBody').html('');

        $.get("{{ url('address-master/data') }}/" + type, function(data) {
            $('#loadingSpinner').hide();
            let body = '';
            data.forEach((r, i) => {
                const name = String(r.Name ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                const editBtn = `<button type="button" class="btn btn-primary btn-sm editRow" onclick="openEditModal(${r.Id}, '${name}')">Edit</button>`;
                body += `<tr>
                    <td>${i + 1}</td>
                    <td>${r.Name ?? ''}</td>
                    <td class="text-center">${editBtn}</td>
                </tr>`;
            });
            $('#tableBody').html(body);
            $('#addressTable').show();
            addrDT = $('#addressTable').DataTable({
                pageLength: 10,
                ordering: true,
                autoWidth: false,
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
        }).fail(function(xhr) {
            $('#loadingSpinner').hide();
            $('#addressTable').show();
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load data', 'error');
        });
    }

    function openAddModal() {
        $('#editId').val('');
        $('#editType').val(currentSection);
        $('#inputName').val('');
        $('#inputLabel').text(sectionConfig[currentSection].col + ' *');
        $('#modalTitle').text('Add New ' + sectionConfig[currentSection].title);
        $('#saveBtn').text('Save');
        $('#addressModal').modal('show');
    }

    function openEditModal(id, name) {
        $('#editId').val(id);
        $('#editType').val(currentSection);
        $('#inputName').val(name);
        $('#inputLabel').text(sectionConfig[currentSection].col + ' *');
        $('#modalTitle').text('Edit ' + sectionConfig[currentSection].title);
        $('#saveBtn').text('Update');
        $('#addressModal').modal('show');
    }

    $('#saveBtn').on('click', function() {
        const name = $('#inputName').val().trim();
        const type = $('#editType').val();
        const id   = $('#editId').val();

        if (!IS_ADMIN && id) {
            Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'warning');
            return;
        }

        if (!name) { Swal.fire('Validation Error', sectionConfig[type].col + ' is required', 'error'); return; }

        $(this).prop('disabled', true).text(id ? 'Updating...' : 'Saving...');

        if (id) {
            $.ajax({
                url: `/address-master/${type}/${id}`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'PUT', name: name },
                success: function(res) {
                    $('#saveBtn').prop('disabled', false).text('Update');
                    $('#addressModal').modal('hide');
                    Swal.fire('Success!', res.message, 'success').then(() => loadSection(type));
                },
                error: function(xhr) {
                    $('#saveBtn').prop('disabled', false).text('Update');
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to update', 'error');
                }
            });
        } else {
            $.ajax({
                url: "{{ route('address-master.store') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', type: type, name: name },
                success: function(res) {
                    $('#saveBtn').prop('disabled', false).text('Save');
                    $('#addressModal').modal('hide');
                    Swal.fire('Success!', res.message, 'success').then(() => loadSection(type));
                },
                error: function(xhr) {
                    $('#saveBtn').prop('disabled', false).text('Save');
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save', 'error');
                }
            });
        }
    });
</script>
@endpush
