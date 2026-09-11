@extends('AgentDashboard.Layouts.layout')

@push('style')
    <style>
        @media (min-width: 1200px) {
            .modal-xl-custom {
                max-width: 1400px;
            }
        }

        #itemPickerTable tbody tr {
            cursor: pointer;
        }

        #itemPickerTable tbody tr:hover {
            background-color: #e8f4ff;
        }
    </style>
@endpush

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Agent Requisition</h6>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="searchReqBtn" style="width:100px;">
            <svg aria-hidden="true" class="me-2" width="18" height="18" viewBox="0 0 18 18">
                <path d="m18 16.5-5.14-5.18h-.35a7 7 0 1 0-1.19 1.19v.35L16.5 18zM12 7A5 5 0 1 1 2 7a5 5 0 0 1 10 0"></path>
            </svg> Search
        </button>
    </div>

    <div class="card">
        <div class="card-body">

            <input type="hidden" id="indentId" value="0">

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" id="indentDate" class="form-control"
                        min="{{ $year_start }}"
                        max="{{ min($year_end, date('Y-m-d')) }}"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Select Item <span class="text-danger">*</span></label>
                    <div class="input-group" style="position:relative;">
                        <input type="text" id="itemSearch" class="form-control" placeholder="Scan barcode or product code" autocomplete="off">
                       <button class="btn btn-primary" type="button" id="itemSearchBtn">
                          <i class="fas fa-search"></i>
                       </button>

                        <input type="hidden" id="selectedItemId">
                        <input type="hidden" id="selectedUnitId">
                        <div id="itemDropdown" class="list-group position-absolute"
                            style="display:none;z-index:1000;max-height:300px;overflow-y:auto;width:100%;top:100%;left:0;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" id="selectedUnitName" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" id="itemQty" class="form-control" min="1" placeholder="0">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Remarks</label>
                    <input type="text" id="remarks" class="form-control" maxlength="100">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100" onclick="addItem()">Add</button>
                </div>
            </div>

            <div id="itemTableSection" class="d-none">
                <div class="table-responsive mb-3"  style="max-height:300px; overflow-y:auto;">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-secondary" onclick="resetForm()">Cancel</button>
                    <button class="btn btn-primary" onclick="saveRequisition()">Save</button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

{{-- Item Picker Modal --}}
<div class="modal fade" id="itemPickerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-xl-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Item</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Category</label>
                        <select class="form-control form-control-sm" id="modalCateId">
                            <option value="0">-- All Categories --</option>
                            @foreach ($categories ?? [] as $cat)
                                <option value="{{ $cat->Prd_CateId }}">{{ $cat->Prd_CateNm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Sub Category</label>
                        <select class="form-control form-control-sm" id="modalSubCateId">
                            <option value="0">-- All Sub Categories --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Product Name / Code</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="modalSearchInput" placeholder="Search...">
                            <button class="btn btn-primary" type="button" id="modalSearchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="itemPickerLoader" class="text-center py-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 mb-0">Loading items...</p>
                </div>
                <div id="itemPickerTableWrap" style="display:none;">
                    <table id="itemPickerTable" class="table table-bordered table-hover table-sm w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Search Modal --}}
<div class="modal fade" id="reqSearchModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-xl-custom">
        <div class="modal-content">
           <div class="modal-header">
    <h5 class="modal-title">Search Requisition</h5>
    <button type="button" class="close" data-bs-dismiss="modal">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="searchFrmDate"
                            min="{{ $year_start }}" max="{{ $year_end }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="searchToDate"
                            min="{{ $year_start }}" max="{{ $year_end }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="searchReqGo">Search</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="reqSearchTable" class="table table-bordered table-sm w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Indent No</th>
                                <th>Date</th>
                                <th>Remarks</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="reqSearchBody">
                            <tr><td colspan="5" class="text-center text-muted">Use filters above to search</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const baseUrl   = "{{ url('/') }}";
    const csrfToken = "{{ csrf_token() }}";
    const yearStart = "{{ $year_start }}";
    const yearEnd   = "{{ $year_end }}";
    const today     = "{{ date('Y-m-d') }}";
    const maxDate   = yearEnd < today ? yearEnd : today;

    let itemList    = [];
    let reqSearchDT = null;
    let selectedRow = null;
    let itemPickerDT = null;

    $(document).ready(function () {

        $('#itemSearchBtn').on('click', function (e) {
            e.preventDefault();
            const code = $('#itemSearch').val().trim();
            if (!code) {
                openItemPickerModal([], '');
                return;
            }
            $.get(baseUrl + '/agent/requisition/items', {
                code: code,
                cat_id: 0,
                sub_cat_id: 0
            }, function (data) {
                if (!data || !data.length) {
                    openItemPickerModal([], code);
                    return;
                }
                if (data.length === 1) {
                    selectRequisitionItem(data[0]);
                    return;
                }
                const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === code.toLowerCase());
                if (exact.length === 1) {
                    selectRequisitionItem(exact[0]);
                    return;
                }
                openItemPickerModal(data, code);
            }).fail(function () {
                openItemPickerModal([], code);
            });
        });

        $('#modalCateId').on('change', function () {
            const catId = parseInt($(this).val()) || 0;
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            if (!catId) return;
            $.get(baseUrl + '/agent/requisition/subcats', { cat_id: catId }, function (subs) {
                subs.forEach(s => {
                    $('#modalSubCateId').append(
                        `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                    );
                });
            });
        });

        $('#modalSearchBtn').on('click', function () {
            const catId = parseInt($('#modalCateId').val()) || 0;
            const subCatId = parseInt($('#modalSubCateId').val()) || 0;
            const code = $('#modalSearchInput').val().trim();
            $('#itemPickerLoader').show();
            $('#itemPickerTableWrap').hide();
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
            $.get(baseUrl + '/agent/requisition/items', {
                cat_id: catId,
                sub_cat_id: subCatId,
                code: code
            }, function (data) {
                renderItemPickerTable(data);
            }).fail(function () {
                $('#itemPickerLoader').hide();
                Swal.fire('Error', 'Failed to load items', 'error');
            });
        });

        $('#modalSearchInput').on('keypress', function (e) {
            if (e.which === 13) $('#modalSearchBtn').trigger('click');
        });

        $('#itemPickerModal').on('hidden.bs.modal', function () {
            $('#modalCateId').val('0');
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            $('#modalSearchInput').val('');
            $('#itemPickerLoader').hide();
            $('#itemPickerTableWrap').hide();
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
        });

        $(document).on('click', '#itemPickerTable tbody tr', function () {
            const prodId = $(this).data('id');
            if (!prodId) return;
            selectRequisitionItem({
                Prod_Id: prodId,
                Prod_ShortNm: $(this).data('name') || '',
                Unit_Id: $(this).data('unit') || '',
                Unit_Name: $(this).data('unitname') || ''
            });
            $('#itemPickerModal').modal('hide');
        });

        $('#itemSearch').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#itemSearchBtn').trigger('click');
            }
        });

        $('#itemSearch').on('input', function () {
            if ($(this).val().trim() === '') $('#itemDropdown').hide();
            if ($('#selectedItemId').val()) {
        $('#selectedItemId').val('');
        $('#selectedUnitId').val('');
        $('#selectedUnitName').val('');
    }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#itemSearch, #itemDropdown, #itemSearchBtn').length) {
                $('#itemDropdown').hide();
            }
        });

        $('#itemQty').on('keypress', function (e) {
            if (e.which === 13) addItem();
        });

        $('#searchReqBtn').on('click', function () {
            $('#reqSearchModal').modal('show');
        });

        $('#searchReqGo').on('click', function () {
            const frmDate = $('#searchFrmDate').val();
            const toDate  = $('#searchToDate').val();
            if (!frmDate || !toDate) { Swal.fire('Error', 'Select date range', 'error'); return; }

            if (reqSearchDT) { reqSearchDT.destroy(); reqSearchDT = null; }
            $('#reqSearchBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

            $.get(baseUrl + '/agent/requisition/search', {
                frm_date: frmDate,
                to_date: toDate
            }, function (data) {
                if (!data.length) {
                    $('#reqSearchBody').html('<tr><td colspan="5" class="text-center text-muted">No records found</td></tr>');
                    return;
                }
                let html = '';
                data.forEach((row, idx) => {
                    const rowData = encodeURIComponent(JSON.stringify(row));
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${row.Indent_No}</td>
                        <td>${siDate.toDisplay(row.Indent_Date)}</td>
                        <td>${row.Remarks ?? ''}</td>
                        <td class="text-center">
                           <button class="btn btn-sm btn-warning viewReq" data-row="${rowData}">Edit</button>
                        </td>
                    </tr>`;
                });
                $('#reqSearchBody').html(html);
                reqSearchDT = $('#reqSearchTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    language: {
                        search: '', searchPlaceholder: 'Search...',
                        sLengthMenu: 'Row Per Page _MENU_ Entries',
                        info: '_START_ - _END_ of _TOTAL_ items',
                        paginate: {
                            next: '<i class="fa fa-angle-right"></i>',
                            previous: '<i class="fa fa-angle-left"></i>'
                        }
                    }
                });
            }).fail(function () {
                Swal.fire('Error', 'Failed to load data', 'error');
                $('#reqSearchBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>');
            });
        });

     $(document).on('click', '.viewReq', function () {
    selectedRow = JSON.parse(decodeURIComponent($(this).data('row')));
    $('#reqSearchModal').modal('hide');
});

$('#reqSearchModal').on('hidden.bs.modal', function () {
    if (reqSearchDT) { reqSearchDT.destroy(); reqSearchDT = null; }
    $('#reqSearchBody').html('<tr><td colspan="5" class="text-center text-muted">Use filters above to search</td></tr>');
    $('#searchFrmDate, #searchToDate').val('');

    if (selectedRow) {
        const row = selectedRow;
        selectedRow = null;
        loadForEdit(row);
    }
});

    });

    function selectRequisitionItem(item) {
        $('#itemSearch').val(item.Prod_ShortNm || item.Item_Name || '');
        $('#selectedItemId').val(item.Prod_Id);
        $('#selectedUnitId').val(item.Unit_Id || '');
        $('#selectedUnitName').val(item.Unit_Name || '');
        $('#itemQty').focus();
    }

    function openItemPickerModal(data, code) {
        $('#itemPickerLoader').hide();
        $('#itemPickerTableWrap').hide();
        if (itemPickerDT) {
            itemPickerDT.destroy();
            itemPickerDT = null;
        }
        $('#itemPickerTable tbody').html('');
        $('#modalCateId').val('0');
        $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
        $('#modalSearchInput').val(code || '');
        $('#itemPickerModal').modal('show');
        if (data.length > 0) {
            $('#itemPickerLoader').show();
            renderItemPickerTable(data);
        }
    }

    function renderItemPickerTable(data) {
        setTimeout(function () {
            $('#itemPickerLoader').hide();
            if (!data.length) {
                $('#itemPickerTable tbody').html(
                    '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
                $('#itemPickerTableWrap').show();
                return;
            }
            $.each(data, function (i, item) {
                $('#itemPickerTable tbody').append(
                    `<tr data-id="${item.Prod_Id}"
                 data-code="${item.Prod_Code}"
                 data-name="${item.Prod_ShortNm}"
                 data-unit="${item.Unit_Id}"
                 data-unitname="${item.Unit_Name}">
                <td>${i + 1}</td>
                <td>${item.Prod_Code}</td>
                <td>${item.Prod_ShortNm}</td>
                <td>${item.Unit_Name}</td>
            </tr>`
                );
            });
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTableWrap').show();
            itemPickerDT = $('#itemPickerTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                ordering: true,
                language: {
                    search: '',
                    searchPlaceholder: 'Search items...',
                    paginate: {
                        next: '<i class="fa fa-angle-right"></i>',
                        previous: '<i class="fa fa-angle-left"></i>'
                    }
                }
            });
        }, 0);
    }

    function fetchItems(keyword) {
        $.get(baseUrl + '/agent/requisition/search-item', { keyword }, function (data) {
            const dd = $('#itemDropdown').empty();
            if (!data.length) {
                dd.append('<div class="list-group-item">No items found</div>').show();
                return;
            }
            data.forEach(item => {
                dd.append(
                    $('<a href="#" class="list-group-item list-group-item-action"></a>')
                        .text(item.Item_Name)
                        .on('click', function (e) {
                            e.preventDefault();
                            $('#itemSearch').val(item.Item_Name);
                            $('#selectedItemId').val(item.Prod_Id);
                            $('#selectedUnitId').val(item.Unit_Id);
                            $('#selectedUnitName').val(item.Unit_Name);
                            $('#itemDropdown').hide();
                            $('#itemQty').focus();
                        })
                );
            });
            dd.show();
        });
    }

    function addItem() {
        const itemId   = $('#selectedItemId').val();
        const itemName = $('#itemSearch').val().trim();
        const unitId   = $('#selectedUnitId').val();
        const unitName = $('#selectedUnitName').val();
        const qty      = parseFloat($('#itemQty').val());

        if (!itemId)         { Swal.fire('Error', 'Please select an item', 'error'); return; }
        if (!qty || qty < 1) { Swal.fire('Error', 'Enter a valid quantity', 'error'); return; }
        if (itemList.find(i => i.item_id == itemId)) { Swal.fire('Error', 'Item already added', 'error'); return; }

        itemList.push({ item_id: itemId, item_name: itemName, unit_id: unitId, unit_name: unitName, qnty: qty });
        renderItemTable();
        clearItemInputs();
    }

function renderItemTable() {
    const tbody = $('#itemTableBody').empty();
    if (!itemList.length) {
        $('#itemTableSection').addClass('d-none');
        return;
    }
    $('#itemTableSection').removeClass('d-none');
    itemList.forEach((item, idx) => {
        tbody.append(`
            <tr>
                <td>${idx + 1}</td>
                <td>${item.item_name || ''}</td>
                <td>${item.unit_name || ''}</td>
                <td>${item.qnty}</td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm me-1" onclick="loadItemIntoForm(${idx})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(${idx})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`);
    });
}



    function removeItem(idx) {
        itemList.splice(idx, 1);
        renderItemTable();
    }

   function clearItemInputs() {
    $('#itemSearch').val('').prop('readonly', false);
    $('#selectedItemId, #selectedUnitId, #selectedUnitName, #itemQty').val('');
    $('#itemSearch').focus();
}


    function saveRequisition(confirmOverLimit) {
        const date = $('#indentDate').val();
        if (!date)            { Swal.fire('Error', 'Please select a date', 'error'); return; }
        if (date < yearStart || date > yearEnd) {
            Swal.fire('Error', `Date must be between ${yearStart} and ${yearEnd}`, 'error'); return;
        }
        if (date > today)     { Swal.fire('Error', 'Future date not allowed', 'error'); return; }
        if (!itemList.length) { Swal.fire('Error', 'Add at least one item', 'error'); return; }

        $.ajax({
            url: baseUrl + '/agent/requisition/save',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:    csrfToken,
                indent_id: $('#indentId').val() || 0,
                date:      date,
                remarks:   $('#remarks').val(),
                items:     itemList,
                confirm_over_limit: confirmOverLimit ? 1 : 0
            }),
            success: function (res) {
                if (res.needs_confirm && res.warning) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'You have crossed your requisition limit',
                        text: res.warning,
                        showCancelButton: true,
                        confirmButtonText: 'Continue',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            saveRequisition(true);
                        }
                    });
                    return;
                }
                Swal.fire('Success', res.message, 'success').then(() => resetForm());
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Save failed', 'error');
            }
        });
    }

// ── Edit index tracker ────────────────────────────────────────
let editIndex = -1;

function loadForEdit(row) {
    resetForm();

    $('#indentId').val(row.Indent_Id);
    $('#indentDate').val(row.Indent_Date ? row.Indent_Date.substring(0, 10) : '');
    $('#remarks').val(row.Remarks ?? '');

    itemList = [];

    let items = row.Item_Data;
    if (items) {
        if (typeof items === 'string') {
            try { items = JSON.parse(items); } catch(e) { items = []; }
        }
        (items || []).forEach(i => {
            itemList.push({
                item_id:   i.Prod_Id,
                item_name: i.Prod_ShortNm,
                unit_id:   i.Unit_Id,
                unit_name: i.Unit_Name,
                qnty:      i.Quantity
            });
        });
    }

    renderItemTable();
    $('html, body').animate({ scrollTop: 0 }, 300);
}


function loadItemIntoForm(idx) {
    const item = itemList[idx];
    if (!item) return;

    $('#itemSearch').val(item.item_name);
    $('#selectedItemId').val(item.item_id);
    $('#selectedUnitId').val(item.unit_id);
    $('#selectedUnitName').val(item.unit_name);
    $('#itemQty').val(item.qnty);

    itemList.splice(idx, 1);
    renderItemTable();
    $('#itemQty').focus();
}






   function resetForm() {
    $('#indentId').val('0');
    $('#indentDate').val(today <= maxDate ? today : maxDate);
    $('#remarks').val('');
    itemList = [];
    editIndex = -1;
    $('#itemTableSection').addClass('d-none');
    $('#itemTableBody').empty();
    clearItemInputs();
}

</script>
@endpush
