@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
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
            <h6 class="ps-2">Agent Indent</h6>

            <!-- Form Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <!-- Date & Agent -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="indentDate" min="{{ session('year_start') }}"
                                max="{{ session('year_end') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Agent<span class="text-danger">*</span></label>
                            <select class="form-select select2-agent" id="agentId">
                                <option value="">Select Agent</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->Agent_Id }}">{{ $agent->Agent_Name }}
                                        ({{ $agent->Agent_Code }})
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <!-- Indent Type Radio -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label me-3">Indent Form</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="indentType" id="radioNew"
                                    value="2" checked>
                                <label class="form-check-label" for="radioNew">New</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="indentType" id="radioRequisition"
                                    value="1">
                                <label class="form-check-label" for="radioRequisition">Requisition</label>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details (hidden for Requisition) -->
                    <div id="productSection">
                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Product Barcode<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="productBarcode"
                                        placeholder="Enter barcode and press Enter" autocomplete="off">
                                    <button class="btn btn-primary" type="button" id="productSearchBtn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Product Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="productName" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">MRP</label>
                                <input type="number" class="form-control" id="mrp" step="0.01" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Available Qty</label>
                                <input type="number" class="form-control" id="availableQty" step="0.01" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pack Date</label>
                                <input type="date" class="form-control" id="packDate" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Quantity<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="0.01" min="0.01">
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-success" onclick="addItemRow()">+ Add Product</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="selectedIndentId">
                    <div class="table-responsive">
                        <table id="itemsTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="itemsTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-secondary" onclick="resetForm()">Cancel</button>
                        <button class="btn btn-primary" id="saveBtn">Save Indent</button>
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
                    <h5 class="modal-title" id="itemPickerTitle">Select Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3" id="modalFilterRow">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Category</label>
                            <select class="form-select form-select-sm" id="modalCateId">
                                <option value="0">-- All Categories --</option>
                                @foreach ($categories ?? [] as $cat)
                                    <option value="{{ $cat->Prd_CateId }}">{{ $cat->Prd_CateNm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Sub Category</label>
                            <select class="form-select form-select-sm" id="modalSubCateId">
                                <option value="0">-- All Sub Categories --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Product Name / Code</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="modalSearchInput"
                                    placeholder="Search...">
                                <button class="btn btn-primary" type="button" id="modalSearchBtn">
                                    <i class="fa-solid fa-magnifying-glass"></i>
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

    <!-- Pending Indent Modal -->
    <div class="modal fade" id="pendingIndentModal" tabindex="-1" aria-labelledby="pendingIndentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingIndentModalLabel">Pending Indents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="pendingIndentTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px"></th>
                                    <th>Indent No</th>
                                    <th>Indent Date</th>
                                    <th>Remarks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="pendingIndentBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let indentItems = [];
        let dataTable;
        let itemPickerDT = null;

        $(document).ready(function() {

            $('.select2-agent').select2({
                placeholder: 'Search agent...',
                allowClear: true,
                width: '100%'
            });

            dataTable = $('#itemsTable').DataTable();

            $('#productBarcode').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode.length >= 8) {
                        fetchProductDetails(barcode);
                    } else {
                        Swal.fire('Error', 'Please enter a valid barcode', 'error');
                    }
                }
            });

            $('#productSearchBtn').on('click', function(e) {
                e.preventDefault();
                if (!validateIndentDate()) return;
                const code = $('#productBarcode').val().trim();
                if (!code) {
                    openItemPickerModal([], false, '');
                    return;
                }
                $.get("{{ route('agent-indent.items') }}", {
                    code: code,
                    cat_id: 0,
                    sub_cat_id: 0
                }, function(data) {
                    openItemPickerModal(data, true, code);
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load items', 'error');
                });
            });

            $('#modalCateId').on('change', function() {
                const catId = parseInt($(this).val()) || 0;
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                if (!catId) return;
                $.get("{{ route('agent-indent.subcats') }}", {
                    cat_id: catId
                }, function(subs) {
                    subs.forEach(s => {
                        $('#modalSubCateId').append(
                            `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                        );
                    });
                });
            });

            $('#modalSearchBtn').on('click', function() {
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
                $.get("{{ route('agent-indent.items') }}", {
                    cat_id: catId,
                    sub_cat_id: subCatId,
                    code: code
                }, function(data) {
                    renderItemPickerTable(data);
                }).fail(function() {
                    $('#itemPickerLoader').hide();
                    Swal.fire('Error', 'Failed to load items', 'error');
                });
            });

            $('#modalSearchInput').on('keypress', function(e) {
                if (e.which === 13) $('#modalSearchBtn').trigger('click');
            });

            $('#itemPickerModal').on('hidden.bs.modal', function() {
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

            $(document).on('click', '#itemPickerTable tbody tr', function() {
                const prodId = $(this).data('id');
                const code = $(this).data('code');
                if (!prodId) return;
                if (!validateIndentDate()) return;
                $('#productBarcode').val(code || '');
                $('#itemPickerModal').modal('hide');
                fetchProductById(prodId);
            });

            $('#indentDate').on('change', function() {
                if ($('#productName').val()) {
                    clearProductFields();
                    Swal.fire('Info', 'Product data cleared. Please re-enter barcode for the new date.',
                        'info');
                }
            });

            $('input[name="indentType"]').on('change', function() {
                const isRequisition = $(this).val() === '1';
                $('#productSection').toggle(!isRequisition);

                if (isRequisition) {
                    const agentId = $('#agentId').val();
                    if (!agentId) {
                        Swal.fire('Error', 'Please select an agent first', 'error');
                        $('#radioNew').prop('checked', true);
                        $('#productSection').show();
                        return;
                    }
                    loadPendingIndents(agentId);
                } else {
                    $('#selectedIndentId').val('');
                    $('#indentDate').prop('disabled', false);
                    indentItems = [];
                    renderItemsTable();
                }
            });

            $('#saveBtn').on('click', saveIndent);
        });

        function openItemPickerModal(data, isCodeSearch, code) {
            $('#itemPickerTitle').text('Select Item');
            $('#itemPickerLoader').hide();
            $('#itemPickerTableWrap').hide();
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
            $('#modalFilterRow').show();
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
            setTimeout(function() {
                $('#itemPickerLoader').hide();
                if (!data.length) {
                    $('#itemPickerTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
                    $('#itemPickerTableWrap').show();
                    return;
                }
                $.each(data, function(i, item) {
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
                    sDom: 'fBtlpi',
                    language: {
                        search: '',
                        searchPlaceholder: 'Search items...',
                        sLengthMenu: 'Row Per Page _MENU_ Entries',
                        info: '_START_ - _END_ of _TOTAL_ items',
                        paginate: {
                            next: '<i class="isax isax-arrow-right-1"></i>',
                            previous: '<i class="isax isax-arrow-left"></i>'
                        }
                    }
                });
            }, 0);
        }

        function loadPendingIndents(agentId) {
            $.ajax({
                url: "{{ route('agent-indent.pending-indents') }}",
                method: 'POST',
                data: {
                    agent_id: agentId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        renderPendingIndents(response.data);
                        $('#pendingIndentModal').modal('show');
                    } else {
                        Swal.fire('Info', 'No pending indents found for this agent', 'info');
                        $('#radioNew').prop('checked', true);
                        $('#productSection').show();
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to load pending indents', 'error');
                    $('#radioNew').prop('checked', true);
                    $('#productSection').show();
                }
            });
        }

        function renderPendingIndents(data) {
            const tbody = $('#pendingIndentBody');
            tbody.empty();

            const indentMap = {};

            data.forEach(function(indent) {
                const items = indent.Item_Data ? JSON.parse(indent.Item_Data) : [];
                indentMap[indent.Indent_Id] = {
                    items,
                    date: indent.Indent_Date
                };

                const mainRow = `
            <tr>
                <td>
                    <button class="btn btn-sm btn-outline-secondary toggle-items" data-indent="${indent.Indent_Id}">
                        <i class="fas fa-plus"></i>
                    </button>
                </td>
                <td>${indent.Indent_No}</td>
                <td>${siDate.toDisplay(indent.Indent_Date)}</td>
                <td>${indent.Remarks || ''}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary attach-indent" data-indent-id="${indent.Indent_Id}">
                        <i class="fas fa-paperclip"></i> Attach
                    </button>
                </td>
            </tr>
            <tr class="indent-detail-row d-none" id="detail-${indent.Indent_Id}">
                <td colspan="5" class="p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Sl</th><th>Item Name</th><th>Quantity</th></tr>
                        </thead>
                        <tbody>
                            ${items.map((it, idx) => `
                                        <tr>
                                            <td>${idx + 1}</td>
                                            <td>${it.Prod_ShortNm}</td>
                                            <td>${it.Quantity} ${it.Unit_Name || ''}</td>
                                        </tr>`).join('')}
                        </tbody>
                    </table>
                </td>
            </tr>`;
                tbody.append(mainRow);
            });

            $(document).off('click', '.toggle-items').on('click', '.toggle-items', function() {
                const id = $(this).data('indent');
                $(`#detail-${id}`).toggleClass('d-none');
                $(this).find('i').toggleClass('fa-plus fa-minus');
            });

            $(document).off('click', '.attach-indent').on('click', '.attach-indent', function() {
                const indentId = $(this).data('indent-id');
                const {
                    items,
                    date
                } = indentMap[indentId];

                if (!items || items.length === 0) {
                    Swal.fire('Warning', 'No items found in this indent', 'warning');
                    return;
                }

                $('#selectedIndentId').val(indentId);
                $('#indentDate').val(date).prop('disabled', true);

                indentItems = items.map(it => ({
                    barcode: it.Prod_Code,
                    prod_id: it.Prod_Id,
                    unit_id: it.Unit_Id,
                    product_name: it.Prod_ShortNm,
                    mrp: 0,
                    available_qty: 0,
                    pack_date: '',
                    quantity: it.Quantity
                }));

                renderItemsTable();
                $('#pendingIndentModal').modal('hide');
            });
        }

        function applyProductDetails(product) {
            if (product.Prod_Code) {
                $('#productBarcode').val(product.Prod_Code);
            }
            $('#productName').val(product.Prod_ShortNm);
            $('#mrp').val(product.MRP);
            $('#availableQty').val(product.Avil_Qnty);
            $('#packDate').val(product.Pack_Date);
            $('#productBarcode').data('prod-id', product.Prod_Id);
            $('#productBarcode').data('unit-id', product.Unit_Id);
            $('#quantity').attr('max', product.Avil_Qnty);
            if (product.Avil_Qnty > 0) {
                $('#quantity').focus();
            } else {
                Swal.fire('Warning', 'This product is out of stock!', 'warning');
            }
            $('#productBarcode').addClass('is-valid');
            setTimeout(() => $('#productBarcode').removeClass('is-valid'), 2000);
        }

        function fetchProductById(prodId) {
            if (!validateIndentDate()) return;
            const indentDate = $('#indentDate').val();
            $('#productName, #mrp, #availableQty, #packDate').val('');

            $.get("{{ route('agent-indent.item-info') }}", {
                prod_id: prodId,
                sale_date: indentDate
            }).done(function(product) {
                applyProductDetails(product);
            }).fail(function(xhr) {
                $('#productName, #mrp, #availableQty, #packDate').val('');
                $('#productBarcode').addClass('is-invalid');
                setTimeout(() => $('#productBarcode').removeClass('is-invalid'), 2000);
                Swal.fire({
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to fetch product details. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }

        function fetchProductDetails(barcode) {
            if (!validateIndentDate()) return;
            const indentDate = $('#indentDate').val();
            if (!indentDate) {
                Swal.fire('Error', 'Please select indent date first', 'error');
                $('#indentDate').focus();
                return;
            }

            $('#productName, #mrp, #availableQty, #packDate').val('');

            $.ajax({
                url: "{{ route('agent-indent.get-product-info') }}",
                method: 'POST',
                data: {
                    barcode: barcode,
                    date: indentDate,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success && response.data) {
                        applyProductDetails(response.data);
                    } else {
                        $('#productName, #mrp, #availableQty, #packDate').val('');
                        $('#productBarcode').addClass('is-invalid');
                        setTimeout(() => $('#productBarcode').removeClass('is-invalid'), 2000);
                        Swal.fire({
                            title: 'Product Not Found',
                            text: response.message || 'Invalid Code Entered !!',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    $('#productName, #mrp, #availableQty, #packDate').val('');
                    $('#productBarcode').addClass('is-invalid');
                    setTimeout(() => $('#productBarcode').removeClass('is-invalid'), 2000);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch product details. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function addItemRow() {
            if (!validateIndentDate()) return;

            const barcode = $('#productBarcode').val().trim();
            const productName = $('#productName').val().trim();
            const mrp = parseFloat($('#mrp').val()) || 0;
            const availableQty = parseFloat($('#availableQty').val()) || 0;
            const packDate = $('#packDate').val();
            const qty = parseFloat($('#quantity').val());
            const prodId = $('#productBarcode').data('prod-id');
            const unitId = $('#productBarcode').data('unit-id');

            if (!barcode) {
                Swal.fire('Error', 'Product barcode is required', 'error');
                $('#productBarcode').focus();
                return;
            }
            if (!productName || productName === 'Loading...') {
                Swal.fire('Error', 'Product name is required. Press Enter after entering barcode.', 'error');
                $('#productBarcode').focus();
                return;
            }
            if (!qty || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                $('#quantity').focus();
                return;
            }
            if (qty > availableQty) {
                Swal.fire('Error', `Requested quantity (${qty}) exceeds available quantity (${availableQty})`, 'error');
                $('#quantity').focus();
                return;
            }

            if (indentItems.findIndex(item => item.barcode === barcode) !== -1) {
                Swal.fire('Error', 'This product is already added', 'error');
                return;
            }

            indentItems.push({
                barcode,
                prod_id: prodId,
                unit_id: unitId,
                product_name: productName,
                mrp,
                available_qty: availableQty,
                pack_date: packDate || '',
                quantity: qty
            });
            $('#indentDate').prop('disabled', true);
            renderItemsTable();
            clearProductFields();
            $('#productBarcode').focus();
        }

        function removeItemRow(index) {
            Swal.fire({
                    title: 'Delete Product',
                    text: 'Are you sure you want to remove this product?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        indentItems.splice(index, 1);
                        renderItemsTable();
                    }
                });
        }

        function renderItemsTable() {
            dataTable.clear();
            indentItems.forEach(function(item, i) {
                dataTable.row.add([
                    i + 1,
                    item.product_name,
                    item.quantity,
                    `<div class="text-center"><button class="btn btn-danger btn-sm" onclick="removeItemRow(${i})"><i class="fas fa-trash"></i></button></div>`
                ]);
            });
            dataTable.draw();
        }

        function clearProductFields() {
            $('#productBarcode, #productName, #mrp, #availableQty, #packDate, #quantity').val('');
            $('#productBarcode').removeClass('is-valid is-invalid').removeData('prod-id unit-id');
            $('#quantity').removeAttr('max');
        }

        function saveIndent() {
            if (!$('#indentDate').val()) {
                Swal.fire('Error', 'Date is required', 'error');
                $('#indentDate').focus();
                return;
            }
            if (!$('#agentId').val()) {
                Swal.fire('Error', 'Please select Agent', 'error');
                $('#agentId').focus();
                return;
            }
            if (indentItems.length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            const indentType = $('input[name="indentType"]:checked').val();
            const indentId = $('#selectedIndentId').val();

            $('#saveBtn').prop('disabled', true).text('Saving...');

            const saveData = {
                indent_date: $('#indentDate').val(),
                agent_id: $('#agentId').val(),
                indent_type: indentType,
                indent_id: indentId,
                items: indentItems.map(item => ({
                    prod_id: item.prod_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity
                })),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('agent-indent.store') }}",
                method: 'POST',
                data: saveData,
                success: function(response) {
                    Swal.fire('Success!', response.message || 'Agent indent saved successfully', 'success')
                        .then(() => resetForm());
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save agent indent', 'error');
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false).text('Save Indent');
                }
            });
        }

        function validateIndentDate() {
            const selectedDate = $('#indentDate').val();
            const yearStart = "{{ session('year_start') }}";
            const yearEnd = "{{ session('year_end') }}";
            if (!selectedDate) {
                Swal.fire('Error', 'Please select indent date first', 'error');
                $('#indentDate').focus();
                return false;
            }
            if (yearStart && yearEnd && (selectedDate < yearStart || selectedDate > yearEnd)) {
                Swal.fire({
                    title: 'Invalid Date',
                    text: `Date must be between ${yearStart} and ${yearEnd}`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('#indentDate').focus();
                return false;
            }
            return true;
        }

        function resetForm() {
            indentItems = [];
            $('#indentDate').val('{{ date('Y-m-d') }}').prop('disabled', false);
            $('#agentId').val('').trigger('change');
            $('#selectedIndentId').val('');
            $('#radioNew').prop('checked', true);
            $('#productSection').show();
            clearProductFields();
            $('#saveBtn').prop('disabled', false).text('Save Indent');
            dataTable.clear().draw();
        }
    </script>
@endpush
