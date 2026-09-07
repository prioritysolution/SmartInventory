@extends('AgentDashboard.Layouts.layout')

@push('style')
<style>
    @media (min-width: 1200px) {
        .modal-xl-custom { max-width: 1400px; }
    }
    #itemPickerTable tbody tr { cursor: pointer; }
    #itemPickerTable tbody tr:hover { background-color: #e8f4ff; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Office Return</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Return Date <span class="text-danger">*</span></label>
                    <input type="date" id="returnDate" class="form-control"
                        min="{{ $year_start }}"
                        max="{{ min($year_end, date('Y-m-d')) }}"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Select Item</label>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" class="form-control"
                            placeholder="Scan barcode or search item" autocomplete="off">
                        <button class="btn btn-primary" type="button" id="itemSearchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input type="text" id="remarks" class="form-control" maxlength="100" autocomplete="off">
                </div>
            </div>

            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label class="form-label">Item Selected</label>
                    <input type="text" id="itemSelected" class="form-control bg-light" readonly placeholder="Select item">
                    <input type="hidden" id="selectedItemId">
                    <input type="hidden" id="unitId">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" id="unitDisplay" class="form-control bg-light" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Available Qty</label>
                    <input type="text" id="availableQty" class="form-control bg-light" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" id="quantity" class="form-control" step="0.01" min="0.01" autocomplete="off">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success w-100" id="addItemBtn">Add</button>
                </div>
            </div>
        </div>
    </div>

    <div id="itemsSection" class="d-none">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Items to Return</h6>
                <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Item</th>
                                <th>Unit</th>
                                <th class="text-end">Available</th>
                                <th class="text-end">Return Qty</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-3">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBtn">Save Return</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const baseUrl   = "{{ url('/') }}";
    const csrfToken = "{{ csrf_token() }}";
    const today     = "{{ date('Y-m-d') }}";
    const apiBase   = baseUrl + '/agent/office-return';

    let itemsArray   = [];
    let itemPickerDT = null;

    $(document).ready(function () {
        $('#barcodeInput').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = $(this).val().trim();
                if (val) lookupBarcode();
                else openItemPicker([]);
            }
        });

        $('#itemSearchBtn').on('click', function (e) {
            e.preventDefault();
            const code = $('#barcodeInput').val().trim();
            if (!code) {
                openItemPicker([]);
                return;
            }
            $.get(apiBase + '/items', { code: code, cat_id: 0, sub_cat_id: 0 }, function (data) {
                resolveOfficeItemsOrOpenPicker(data, code);
            }).fail(function () {
                openItemPicker([], code);
            });
        });

        $('#modalCateId').on('change', function () {
            const catId = parseInt($(this).val()) || 0;
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            clearItemPickerTable();
            if (!catId) return;
            $.get(apiBase + '/subcats', { cat_id: catId }, function (subs) {
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
            if (itemPickerDT) { itemPickerDT.destroy(); itemPickerDT = null; }
            $('#itemPickerTable tbody').html('');
            $.get(apiBase + '/items', { cat_id: catId, sub_cat_id: subCatId, code: code }, function (data) {
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
            clearItemPickerTable();
        });

        $(document).on('click', '#itemPickerTable tbody tr', function () {
            const prodId = $(this).data('id');
            const returnDate = $('#returnDate').val();
            if (!prodId) return;
            if (!returnDate) {
                Swal.fire('Error', 'Please select return date first', 'error');
                return;
            }
            $.get(apiBase + '/item-info', { prod_id: prodId, return_date: returnDate })
                .done(function (item) {
                    $('#itemPickerModal').modal('hide');
                    populateItemFields(item);
                })
                .fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
                });
        });

        $('#addItemBtn').on('click', function () {
            if (!validateItem()) return;
            itemsArray.push({
                item_id:   $('#selectedItemId').val(),
                item_name: $('#itemSelected').val(),
                unit_id:   $('#unitId').val(),
                unit_name: $('#unitDisplay').val(),
                available: $('#availableQty').val(),
                quantity:  $('#quantity').val(),
            });
            renderItemsTable();
            clearItemSelection();
            $('#barcodeInput').val('').focus();
            $('#returnDate').prop('disabled', true);
        });

        $(document).on('click', '.removeItem', function () {
            itemsArray.splice($(this).data('index'), 1);
            renderItemsTable();
            if (!itemsArray.length) $('#returnDate').prop('disabled', false);
        });

        $('#saveBtn').on('click', saveReturn);
        $('#cancelBtn').on('click', resetForm);
    });

    function pickUniqueItem(data, code) {
        if (!data || !data.length) return null;
        const needle = String(code).toLowerCase();
        const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === needle);
        if (exact.length === 1) return exact[0];
        if (data.length === 1) return data[0];
        return null;
    }

    function loadOfficeItemByProdId(prodId) {
        const returnDate = $('#returnDate').val();
        if (!returnDate) { Swal.fire('Error', 'Please select return date first', 'error'); return; }
        $.get(apiBase + '/item-info', { prod_id: prodId, return_date: returnDate })
            .done(function (item) { populateItemFields(item); })
            .fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
            });
    }

    function resolveOfficeItemsOrOpenPicker(data, code) {
        const unique = pickUniqueItem(data, code);
        if (unique) {
            loadOfficeItemByProdId(unique.Prod_Id);
            return;
        }
        openItemPicker(data || [], code);
    }

    function lookupBarcode() {
        const barcode = $('#barcodeInput').val().trim();
        const returnDate = $('#returnDate').val();
        if (!barcode) return;
        if (!returnDate) { Swal.fire('Error', 'Please select return date first', 'error'); return; }

        $.get(apiBase + '/barcode', { barcode, return_date: returnDate })
            .done(function (item) { populateItemFields(item); })
            .fail(function () {
                $.get(apiBase + '/items', { code: barcode, cat_id: 0, sub_cat_id: 0 }, function (data) {
                    resolveOfficeItemsOrOpenPicker(data, barcode);
                }).fail(function () {
                    openItemPicker([], barcode);
                });
            });
    }

    function openItemPicker(data, code) {
        if (!$('#returnDate').val()) {
            Swal.fire('Error', 'Please select return date first', 'error');
            return;
        }
        clearItemPickerTable();
        $('#modalCateId').val('0');
        $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
        $('#modalSearchInput').val(code || '');
        $('#itemPickerModal').modal('show');
        if (data && data.length > 0) {
            $('#itemPickerLoader').show();
            renderItemPickerTable(data);
        }
    }

    function clearItemPickerTable() {
        $('#itemPickerLoader').hide();
        $('#itemPickerTableWrap').hide();
        if (itemPickerDT) { itemPickerDT.destroy(); itemPickerDT = null; }
        $('#itemPickerTable tbody').html('');
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
                    `<tr data-id="${item.Prod_Id}">
                        <td>${i + 1}</td>
                        <td>${item.Prod_Code}</td>
                        <td>${item.Prod_ShortNm}</td>
                        <td>${item.Unit_Name}</td>
                    </tr>`
                );
            });
            if (itemPickerDT) { itemPickerDT.destroy(); itemPickerDT = null; }
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

    function populateItemFields(item) {
        const existing = itemsArray.find(i => i.item_id == item.Prod_Id);
        if (existing) {
            Swal.fire('Warning', item.Prod_ShortNm + ' is already added', 'warning');
            $('#barcodeInput').val('').focus();
            return;
        }
        const avail = parseFloat(item.Avil_Qnty) || 0;
        $('#selectedItemId').val(item.Prod_Id);
        $('#itemSelected').val(item.Prod_ShortNm);
        $('#unitId').val(item.Unit_Id);
        $('#unitDisplay').val(item.Unit_Name);
        $('#availableQty').val(avail);
        $('#quantity').val(avail > 0 ? 1 : '');
        if (avail <= 0) {
            Swal.fire('Warning', 'No stock available with you for this item', 'warning');
        }
        $('#quantity').focus();
    }

    function validateItem() {
        if (!$('#returnDate').val()) { Swal.fire('Error', 'Return Date is required', 'error'); return false; }
        if (!$('#selectedItemId').val()) { Swal.fire('Error', 'Please select an item', 'error'); return false; }
        const qty = parseFloat($('#quantity').val());
        const avail = parseFloat($('#availableQty').val()) || 0;
        if (!qty || qty <= 0) { Swal.fire('Error', 'Quantity must be > 0', 'error'); return false; }
        if (avail <= 0) { Swal.fire('Error', 'This item has no stock with you', 'error'); return false; }
        if (qty > avail) {
            Swal.fire('Error', 'Return qty cannot exceed available qty (' + avail + ')', 'error');
            return false;
        }
        return true;
    }

    function renderItemsTable() {
        let html = '';
        itemsArray.forEach((item, idx) => {
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${item.item_name}</td>
                <td>${item.unit_name}</td>
                <td class="text-end">${item.available}</td>
                <td class="text-end">${item.quantity}</td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm removeItem" data-index="${idx}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        $('#itemsTableBody').html(html);
        $('#itemsSection').toggleClass('d-none', !itemsArray.length);
    }

    function clearItemSelection() {
        $('#selectedItemId, #unitId, #itemSelected, #unitDisplay, #availableQty, #quantity').val('');
    }

    function saveReturn() {
        if (!$('#returnDate').val()) { Swal.fire('Error', 'Return Date is required', 'error'); return; }
        if (!itemsArray.length) { Swal.fire('Error', 'Add at least one item', 'error'); return; }

        $('#saveBtn').prop('disabled', true).text('Saving...');
        $.ajax({
            url: apiBase + '/save',
            type: 'POST',
            data: {
                _token: csrfToken,
                return_date: $('#returnDate').val(),
                remarks: $('#remarks').val(),
                items: itemsArray,
            },
            success: function (res) {
                Swal.fire('Success', res.message, 'success').then(resetForm);
            },
            error: function (xhr) {
                $('#saveBtn').prop('disabled', false).text('Save Return');
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save', 'error');
            }
        });
    }

    function resetForm() {
        itemsArray = [];
        $('#returnDate').prop('disabled', false).val(today);
        $('#remarks').val('');
        $('#barcodeInput').val('');
        $('#itemsTableBody').html('');
        $('#itemsSection').addClass('d-none');
        $('#saveBtn').prop('disabled', false).text('Save Return');
        clearItemSelection();
        $('#barcodeInput').focus();
    }
</script>
@endpush
