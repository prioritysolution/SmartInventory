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

        #barcodePickerTable tbody tr.barcode-pick-row {
            cursor: pointer;
        }

        #barcodePickerTable tbody tr.barcode-pick-row:hover {
            background-color: #e8f4ff;
        }

        .barcode-picker-scroll {
            max-height: 320px;
            overflow-y: auto;
        }

        .barcode-picker-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f6f6f6;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Agent Return</h6>

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
                            <select class="form-select select2-agent" id="agentId" data-si-ajax="agents">
                                <option value="">Select Agent</option>
                            </select>

                        </div>
                    </div>

                    <!-- Indent Type Radio -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label me-3">Return Form</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="returnType" id="radioNew"
                                    value="2" checked>
                                <label class="form-check-label" for="radioNew">New</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="returnType" id="radioRequisition"
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
                                <label class="form-label">MRP<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mrp" readonly placeholder="Select barcode">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Agent Available Qty</label>
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
                                    <th class="text-end">MRP</th>
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
                        <button class="btn btn-primary" id="saveBtn">Save Return</button>
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
                                    <th class="text-end">MRP</th>
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
    <div class="modal fade" id="pendingIndentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="pendingIndentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingIndentModalLabel">Pending Return Requisitions</h5>
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
    @include('Admin.partials.barcode-picker-modal')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let indentItems = [];
        let dataTable;
        let itemPickerDT = null;
        let currentMrpOptions = [];
        let currentTotalQty = 0;
        let pendingBarcodePick = null;

        $(document).ready(function() {

            $('.select2-agent').select2({
                placeholder: 'Search agent...',
                allowClear: true,
                width: '100%'
            });

            dataTable = $('#itemsTable').DataTable();

            function filterBarcodePickerRows() {
                const needle = String($('#barcodePickerSearch').val() || '').trim().toLowerCase();
                const $rows = $('#barcodePickerTable tbody tr.barcode-pick-row');
                let visible = 0;
                $rows.each(function() {
                    const hay = String($(this).attr('data-search') || '').toLowerCase();
                    const show = !needle || hay.indexOf(needle) !== -1;
                    $(this).toggle(show);
                    if (show) visible++;
                });
                $('#barcodePickerEmpty').toggleClass('d-none', !$rows.length || visible > 0);
            }

            function openBarcodePicker(payload) {
                const item = payload.item || {};
                const name = item.Prod_ShortNm || '';
                const rows = payload.barcodes || [];
                $('#barcodePickerTitle').text(name ? ('Select Barcode — ' + name) : 'Select Barcode');
                $('#barcodePickerSearch').val('');
                $('#barcodePickerEmpty').addClass('d-none');
                const $body = $('#barcodePickerTable tbody').empty();
                if (!rows.length) {
                    $body.append('<tr><td colspan="5" class="text-center text-muted">No returnable barcode stock for this agent / product</td></tr>');
                } else {
                    rows.forEach(function(row, i) {
                        const barcode = String(row.Barcode || '');
                        const mrp = parseFloat(row.MRP) || 0;
                        const qty = parseFloat(row.Avil_Qnty);
                        const qtyText = isNaN(qty) ? '' : String(qty);
                        const searchText = [i + 1, barcode, name, mrp.toFixed(2), qtyText].join(' ');
                        const $tr = $('<tr class="barcode-pick-row"></tr>');
                        $tr.attr('data-barcode', barcode);
                        $tr.attr('data-mrp', mrp);
                        $tr.attr('data-qty', qtyText);
                        $tr.attr('data-search', searchText);
                        $tr.append($('<td></td>').text(i + 1));
                        $tr.append($('<td></td>').text(barcode));
                        $tr.append($('<td></td>').text(name));
                        $tr.append($('<td class="text-end"></td>').text(mrp.toFixed(2)));
                        $tr.append($('<td class="text-end"></td>').text(qtyText));
                        $body.append($tr);
                    });
                }
                $('#barcodePickerModal').data('item', item);
                $('#barcodePickerModal').modal('show');
                setTimeout(function() { $('#barcodePickerSearch').trigger('focus'); }, 250);
            }

            function selectProductForReturn(prodId) {
                if (!prodId) return;
                if (!validateReturnHeader()) return;
                const indentDate = $('#indentDate').val();
                const agentId = $('#agentId').val();
                $.when(
                    $.get("{{ route('agent-return.item-info') }}", {
                        prod_id: prodId,
                        sale_date: indentDate,
                        agent_id: agentId
                    }),
                    $.get("{{ route('agent-return.product-barcodes') }}", {
                        prod_id: prodId,
                        sale_date: indentDate,
                        agent_id: agentId
                    })
                ).done(function(infoRes, barcodeRes) {
                    pendingBarcodePick = {
                        item: infoRes[0],
                        barcodes: barcodeRes[0] || []
                    };
                    if ($('#itemPickerModal').hasClass('show')) {
                        $('#itemPickerModal').modal('hide');
                        return;
                    }
                    const payload = pendingBarcodePick;
                    pendingBarcodePick = null;
                    openBarcodePicker(payload);
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
                });
            }

            $('#barcodePickerSearchBtn').on('click', filterBarcodePickerRows);
            $('#barcodePickerClearBtn').on('click', function() {
                $('#barcodePickerSearch').val('');
                filterBarcodePickerRows();
                $('#barcodePickerSearch').trigger('focus');
            });
            $('#barcodePickerSearch').on('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); filterBarcodePickerRows(); }
            });
            $('#barcodePickerSearch').on('input', filterBarcodePickerRows);

            $(document).on('click', '#barcodePickerTable tbody tr.barcode-pick-row', function() {
                const item = $('#barcodePickerModal').data('item');
                if (!item) return;
                const code = String($(this).attr('data-barcode') || '').trim();
                const mrp = parseFloat($(this).attr('data-mrp')) || 0;
                const qty = parseFloat($(this).attr('data-qty'));
                $('#barcodePickerModal').modal('hide');
                applyFormBarcodePick(item, code, mrp, isNaN(qty) ? 0 : qty);
            });

            $('#productBarcode').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode) {
                        fetchProductDetails(barcode);
                    } else {
                        openItemPickerModal([], false, '');
                    }
                }
            });

            $('#productSearchBtn').on('click', function(e) {
                e.preventDefault();
                if (!validateReturnHeader()) return;
                const code = $('#productBarcode').val().trim();
                if (!code) {
                    openItemPickerModal([], false, '');
                    return;
                }
                fetchProductDetails(code);
            });

            $('#modalCateId').on('change', function() {
                const catId = parseInt($(this).val()) || 0;
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                if (!catId) return;
                $.get("{{ route('agent-return.subcats') }}", {
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
                if (!validateReturnHeader()) {
                    $('#itemPickerLoader').hide();
                    return;
                }
                $.get("{{ route('agent-return.items') }}", {
                    cat_id: catId,
                    sub_cat_id: subCatId,
                    code: code,
                    agent_id: $('#agentId').val(),
                    sale_date: $('#indentDate').val()
                }, function(data) {
                    renderItemPickerTable(data);
                }).fail(function(xhr) {
                    $('#itemPickerLoader').hide();
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load items', 'error');
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
                if (pendingBarcodePick) {
                    const payload = pendingBarcodePick;
                    pendingBarcodePick = null;
                    openBarcodePicker(payload);
                }
            });

                        $(document).on('click', '#itemPickerTable tbody tr', function() {
                const prodId = $(this).data('id');
                if (!prodId) return;
                if (!validateReturnHeader()) return;
                selectProductForReturn(prodId);
            });

            $('#indentDate').on('change', function() {
                if ($('#productName').val()) {
                    clearProductFields();
                    Swal.fire('Info', 'Product data cleared. Please re-enter barcode for the new date.',
                        'info');
                }
            });

            $('input[name="returnType"]').on('change', function() {
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

            $('#saveBtn').on('click', saveReturn);

            $('#pendingIndentModal').on('hidden.bs.modal', function() {
                if (!$('#selectedIndentId').val()) {
                    $('#radioNew').prop('checked', true);
                    $('#productSection').show();
                }
            });

            $('#mrp').on('change', function() {
                applyFormMrpQty();
            });
        });

        function openItemPickerModal(data, isCodeSearch, code) {
            $('#itemPickerTitle').text('Select Item (agent stock only)');
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
            } else if (!isCodeSearch || !code) {
                // Load products this agent can return (has stock).
                setTimeout(function() { $('#modalSearchBtn').trigger('click'); }, 150);
            }
        }

        function renderItemPickerTable(data) {
            setTimeout(function() {
                $('#itemPickerLoader').hide();
                if (!data.length) {
                    $('#itemPickerTable tbody').html(
                        '<tr><td colspan="5" class="text-center text-muted">No agent stock found for this search</td></tr>');
                    $('#itemPickerTableWrap').show();
                    return;
                }
                $.each(data, function(i, item) {
                    const mrp = parseFloat(item.MRP ?? item.mrp ?? 0) || 0;
                    $('#itemPickerTable tbody').append(
                        `<tr data-id="${item.Prod_Id}"
                     data-code="${item.Prod_Code}"
                     data-name="${item.Prod_ShortNm}"
                     data-unit="${item.Unit_Id}"
                     data-unitname="${item.Unit_Name}"
                     data-mrp="${mrp}">
                    <td>${i + 1}</td>
                    <td>${item.Prod_Code}</td>
                    <td>${item.Prod_ShortNm}</td>
                    <td class="text-end">${mrp.toFixed(2)}</td>
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
                url: "{{ route('agent-return.pending-indents') }}",
                method: 'POST',
                data: {
                    agent_id: agentId,
                    indent_type_id: 3,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        renderPendingIndents(response.data);
                        $('#pendingIndentModal').modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $('#pendingIndentModal').modal('show');
                    } else {
                        Swal.fire('Info', 'No pending return requisitions found for this agent', 'info');
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
                            <tr><th>Sl</th><th>Item Name</th><th>Quantity</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            ${items.map((it, idx) => `
                                        <tr>
                                            <td>${idx + 1}</td>
                                            <td>${it.Prod_ShortNm}</td>
                                            <td>${it.Quantity} ${it.Unit_Name || ''}</td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm reject-indent-item"
                                                    data-indent-id="${indent.Indent_Id}"
                                                    data-indent-sl="${it.Indent_Sl}"
                                                    data-prod-name="${it.Prod_ShortNm}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </td>
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
                    barcode: '',
                    prod_id: it.Prod_Id,
                    unit_id: it.Unit_Id,
                    unit_name: it.Unit_Name || '',
                    product_name: it.Prod_ShortNm,
                    mrp: 0,
                    available_qty: 0,
                    pack_date: '',
                    quantity: it.Quantity,
                    requested_qty: it.Quantity
                }));

                renderItemsTable();
                $('#pendingIndentModal').modal('hide');
            });

            $(document).off('click', '.reject-indent-item').on('click', '.reject-indent-item', function() {
                const $btn = $(this);
                const indentId = $btn.data('indent-id');
                const indentSl = $btn.data('indent-sl');
                const agentId = $('#agentId').val();
                const prodName = $btn.data('prod-name');

                Swal.fire({
                    title: 'Reject Item',
                    text: `Reject "${prodName}" from this return requisition?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Reject',
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'No'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('agent-return.cancel-item') }}",
                        method: 'POST',
                        data: {
                            indent_sl: indentSl,
                            indent_id: indentId,
                            agent_id: agentId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            $btn.closest('tr').remove();
                            const remaining = $(`#detail-${indentId} tbody tr`).length;
                            if (remaining === 0) {
                                $(`#detail-${indentId}`).prev('tr').remove();
                                $(`#detail-${indentId}`).remove();
                                delete indentMap[indentId];
                                if (!$('#pendingIndentBody tr').length) {
                                    $('#pendingIndentModal').modal('hide');
                                    Swal.fire('Info', 'All items rejected. No pending requisitions.', 'info');
                                    return;
                                }
                            }
                            Swal.fire('Rejected', res.message || 'Item rejected', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject item', 'error');
                        }
                    });
                });
            });
        }

        function parseMrpValue(v) {
            const n = parseFloat(typeof v === 'object' && v !== null ? (v.MRP ?? v.mrp ?? v.Rate ?? v.rate) : v);
            return isNaN(n) ? 0 : Math.round(n * 100) / 100;
        }

        function mrpKey(v) {
            return parseMrpValue(v).toFixed(2);
        }

        function collectMrpOptions(product) {
            const map = {};
            (Array.isArray(product.Mrp_Stock) ? product.Mrp_Stock : []).forEach(function(row) {
                const mrp = parseMrpValue(row);
                if (mrp <= 0) return;
                map[mrpKey(mrp)] = {
                    mrp: mrp,
                    qty: row.Avil_Qnty == null ? null : parseFloat(row.Avil_Qnty)
                };
            });
            (Array.isArray(product.Sale_Rates) ? product.Sale_Rates : []).forEach(function(r) {
                const mrp = parseMrpValue(r);
                if (mrp <= 0) return;
                if (!map[mrpKey(mrp)]) {
                    map[mrpKey(mrp)] = { mrp: mrp, qty: null };
                }
            });
            const fallback = parseMrpValue(product.MRP);
            if (fallback > 0 && !map[mrpKey(fallback)]) {
                map[mrpKey(fallback)] = {
                    mrp: fallback,
                    qty: product.Avil_Qnty == null ? null : parseFloat(product.Avil_Qnty)
                };
            }
            return Object.keys(map).sort(function(a, b) {
                return parseFloat(b) - parseFloat(a);
            }).map(function(k) {
                return map[k];
            });
        }

        function resetMrpSelect($select) {
            $select.empty().append('<option value="">-- Select MRP --</option>').prop('disabled', true);
        }

        function fillMrpSelect($select, options, selectedMrp) {
            $select.empty();
            if (!options.length) {
                resetMrpSelect($select);
                return;
            }
            options.forEach(function(opt) {
                const qtyText = opt.qty == null ? '' : (' (Avail: ' + opt.qty + ')');
                $select.append(
                    '<option value="' + mrpKey(opt.mrp) + '">' + mrpKey(opt.mrp) + qtyText + '</option>'
                );
            });
            $select.prop('disabled', false);
            const selected = mrpKey(selectedMrp);
            if (selected !== '0.00' && $select.find('option[value="' + selected + '"]').length) {
                $select.val(selected);
            }
        }

        function qtyForMrp(options, mrp, fallbackQty) {
            const key = mrpKey(mrp);
            const row = options.find(function(o) {
                return mrpKey(o.mrp) === key;
            });
            if (row && row.qty != null && !isNaN(row.qty)) {
                return row.qty;
            }
            return fallbackQty;
        }

        function applyFormMrpQty() {
            const available = qtyForMrp(currentMrpOptions, $('#mrp').val(), currentTotalQty);
            $('#availableQty').val(available);
            $('#quantity').attr('max', available);
            const qty = parseFloat($('#quantity').val()) || 0;
            if (qty > available) {
                $('#quantity').val(available > 0 ? available : '');
            }
        }

        function applyIssueMrpQty() {
            const available = qtyForMrp(issueMrpOptions, $('#issueMrp').val(), issueTotalQty);
            $('#issueAvailableQty').val(available);
            const requested = parseFloat($('#issueRequestedQty').val()) || 0;
            const defaultQty = Math.min(requested, available);
            const currentQty = parseFloat($('#issueQty').val()) || 0;
            if (!currentQty || currentQty > defaultQty) {
                $('#issueQty').val(defaultQty > 0 ? defaultQty : '');
            }
            updateIssueRejectQty();
        }

        function isNumericBarcode(v) {
            return /^\d+$/.test(String(v || '').trim());
        }

        function applyFormBarcodePick(product, barcode, mrp, qty) {
            $('#productBarcode').val(barcode).data('scanned-barcode', barcode);
            $('#productName').val(product.Prod_ShortNm || '');
            $('#mrp').val(mrp > 0 ? mrpKey(mrp) : '');
            $('#availableQty').val(qty);
            $('#quantity').attr('max', qty);
            $('#packDate').val(product.Pack_Date || '');
            $('#productBarcode').data('prod-id', product.Prod_Id);
            $('#productBarcode').data('unit-id', product.Unit_Id);
            $('#productBarcode').data('unit-name', product.Unit_Name || '');
            currentTotalQty = qty;
            currentMrpOptions = [{ mrp: mrp, qty: qty }];
            if (qty <= 0) {
                Swal.fire('Warning', 'This barcode has no available stock with the agent!', 'warning');
            } else {
                $('#quantity').focus();
            }
            $('#productBarcode').addClass('is-valid');
            setTimeout(() => $('#productBarcode').removeClass('is-valid'), 2000);
        }

        function fetchProductById(prodId) {
            selectProductForReturn(prodId);
        }

        function pickUniqueItem(data, code) {
            if (!data || !data.length) return null;
            const needle = String(code).toLowerCase();
            const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === needle);
            if (exact.length === 1) return exact[0];
            if (data.length === 1) return data[0];
            return null;
        }

        function searchCodeOrOpenPicker(code) {
            if (!validateReturnHeader()) return;
            $.get("{{ route('agent-return.items') }}", {
                code: code,
                cat_id: 0,
                sub_cat_id: 0,
                agent_id: $('#agentId').val(),
                sale_date: $('#indentDate').val()
            }, function(data) {
                const unique = pickUniqueItem(data, code);
                if (unique) {
                    fetchProductById(unique.Prod_Id);
                    return;
                }
                openItemPickerModal(data || [], true, code);
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load items', 'error');
                openItemPickerModal([], true, code);
            });
        }

        function fetchProductDetails(barcode) {
            if (!validateReturnHeader()) return;
            const indentDate = $('#indentDate').val();
            const agentId = $('#agentId').val();

            $('#productName, #availableQty, #packDate, #mrp').val('');
            currentMrpOptions = [];
            currentTotalQty = 0;

            $.ajax({
                url: "{{ route('agent-return.get-product-info') }}",
                method: 'POST',
                data: {
                    barcode: barcode,
                    date: indentDate,
                    agent_id: agentId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const product = response.data;
                        if (isNumericBarcode(barcode)) {
                            $.get("{{ route('agent-return.product-barcodes') }}", {
                                prod_id: product.Prod_Id,
                                sale_date: indentDate,
                                agent_id: agentId
                            }).done(function(rows) {
                                const list = Array.isArray(rows) ? rows : [];
                                const match = list.find(function(r) {
                                    return String(r.Barcode) === String(barcode);
                                });
                                if (match) {
                                    applyFormBarcodePick(product, barcode, parseFloat(match.MRP) || parseMrpValue(product.MRP), parseFloat(match.Avil_Qnty) || 0);
                                } else if (list.length) {
                                    openBarcodePicker({ item: product, barcodes: list });
                                } else {
                                    applyFormBarcodePick(product, barcode, parseMrpValue(product.MRP), parseFloat(product.Avil_Qnty) || 0);
                                }
                            }).fail(function() {
                                applyFormBarcodePick(product, barcode, parseMrpValue(product.MRP), parseFloat(product.Avil_Qnty) || 0);
                            });
                        } else {
                            selectProductForReturn(product.Prod_Id);
                        }
                    } else {
                        searchCodeOrOpenPicker(barcode);
                    }
                },
                error: function() {
                    searchCodeOrOpenPicker(barcode);
                }
            });
        }

        function addItemRow() {
            if (!validateReturnHeader()) return;

            const barcode = $('#productBarcode').val().trim();
            const productName = $('#productName').val().trim();
            const mrp = parseFloat($('#mrp').val()) || 0;
            const availableQty = parseFloat($('#availableQty').val()) || 0;
            const packDate = $('#packDate').val();
            const qty = parseFloat($('#quantity').val());
            const prodId = $('#productBarcode').data('prod-id');
            const unitId = $('#productBarcode').data('unit-id');
            const unitName = $('#productBarcode').data('unit-name') || '';

            if (!isNumericBarcode(barcode)) {
                Swal.fire('Error', 'Select a stock barcode from the list (not product code)', 'error');
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
            if (!mrp) {
                Swal.fire('Error', 'Please select a barcode / MRP from the list', 'error');
                $('#productBarcode').focus();
                return;
            }
            if (qty > availableQty) {
                Swal.fire('Error', `Requested quantity (${qty}) exceeds available quantity (${availableQty})`, 'error');
                $('#quantity').focus();
                return;
            }

            if (indentItems.findIndex(item =>
                String(item.prod_id) === String(prodId) && mrpKey(item.mrp) === mrpKey(mrp)
            ) !== -1) {
                Swal.fire('Error', 'This product is already added at MRP ' + mrpKey(mrp), 'error');
                return;
            }

            indentItems.push({
                barcode,
                prod_id: prodId,
                unit_id: unitId,
                unit_name: unitName,
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
                const actions =
                    `<button class="btn btn-danger btn-sm" onclick="removeItemRow(${i})" title="Delete"><i class="fas fa-trash"></i></button>`;
                const qtyValue = parseFloat(item.quantity) || 0;
                const unit = item.unit_name ? (' ' + item.unit_name) : '';
                const mrpDisplay = parseMrpValue(item.mrp) > 0 ? mrpKey(item.mrp) : '-';
                dataTable.row.add([
                    i + 1,
                    item.product_name,
                    `<div class="text-end">${mrpDisplay}</div>`,
                    qtyValue + unit,
                    `<div class="text-center">${actions}</div>`
                ]);
            });
            dataTable.draw();
        }

        function saveReturn() {
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

            const returnType = $('input[name="returnType"]:checked').val();
            const indentId = $('#selectedIndentId').val();
            if (returnType === '1' && !indentId) {
                Swal.fire('Error', 'Please attach a return requisition first', 'error');
                return;
            }

            $('#saveBtn').prop('disabled', true).text('Saving...');

            const saveData = {
                indent_date: $('#indentDate').val(),
                agent_id: $('#agentId').val(),
                return_type: returnType,
                indent_id: indentId,
                items: indentItems.map(item => ({
                    prod_id: item.prod_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity,
                    mrp: parseMrpValue(item.mrp),
                    barcode: String(item.barcode || '').trim()
                })),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('agent-return.store') }}",
                method: 'POST',
                data: saveData,
                success: function(response) {
                    Swal.fire('Success!', response.message || 'Agent return saved successfully', 'success')
                        .then(() => resetForm());
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save agent return', 'error');
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false).text('Save Return');
                }
            });
        }

        function validateReturnHeader() {
            if (!validateIndentDate()) {
                return false;
            }
            if (!$('#agentId').val()) {
                Swal.fire('Error', 'Please select an agent first', 'error');
                $('#agentId').focus();
                return false;
            }
            return true;
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
            $('#saveBtn').prop('disabled', false).text('Save Return');
            dataTable.clear().draw();
        }
    </script>
@endpush
