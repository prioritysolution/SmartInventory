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
                            <label class="form-label">Issue Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="indentDate" min="{{ session('year_start') }}"
                                max="{{ session('year_end') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Agent<span class="text-danger">*</span></label>
                            <select class="form-select select2-agent" id="agentId">
                                <option value="">Select Agent</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->Agent_Id }}"
                                        data-name="{{ $agent->Agent_Name }}"
                                        data-code="{{ $agent->Agent_Code }}">
                                        {{ $agent->Agent_Name }} ({{ $agent->Agent_Code }})
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
                                <label class="form-label">MRP<span class="text-danger">*</span></label>
                                <select class="form-select" id="mrp" disabled>
                                    <option value="">-- Select MRP --</option>
                                </select>
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
                        <table id="itemsTable" class="table table-nowrap">
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

    <!-- Issue Indent Item Modal -->
    <div class="modal fade" id="issueItemModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="issueRowIndex">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="issueItemName" readonly>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Requested Qty</label>
                            <input type="text" class="form-control" id="issueRequestedQty" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Qty</label>
                            <input type="text" class="form-control" id="issueAvailableQty" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barcode<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="issueBarcode"
                            placeholder="Scan barcode and press Enter" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scanned Product</label>
                        <input type="text" class="form-control" id="issueScannedName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">MRP<span class="text-danger">*</span></label>
                        <select class="form-select" id="issueMrp" disabled>
                            <option value="">-- Select MRP --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issue Qty<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="issueQty" step="0.01" min="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reject Qty</label>
                        <input type="text" class="form-control" id="issueRejectQty" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmIssueBtn">Issue</button>
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
        let issueScanProduct = null;
        let currentMrpOptions = [];
        let currentTotalQty = 0;
        let issueMrpOptions = [];
        let issueTotalQty = 0;
        const orgName = @json(session('org_name'));
        const branchName = @json(session('branch_name'));
        const generatedBy = @json(session('user_name', 'User'));

        $(document).ready(function() {

            $('.select2-agent').select2({
                placeholder: 'Search agent...',
                allowClear: true,
                width: '100%'
            });

            dataTable = $('#itemsTable').DataTable({
                bFilter: true,
                sDom: 'fBtlpi',
                ordering: true,
                language: {
                    search: ' ',
                    searchPlaceholder: 'Search items...',
                    sLengthMenu: 'Row Per Page _MENU_ Entries',
                    info: '_START_ - _END_ of _TOTAL_ items',
                    paginate: {
                        next: '<i class="isax isax-arrow-right-1"></i>',
                        previous: '<i class="isax isax-arrow-left"></i>'
                    }
                }
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
                if (!validateIndentDate()) return;
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

            $('#pendingIndentModal').on('hidden.bs.modal', function() {
                if (!$('#selectedIndentId').val()) {
                    $('#radioNew').prop('checked', true);
                    $('#productSection').show();
                }
            });

            $('#issueBarcode').on('keydown', function(e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                scanIssueBarcode();
            });

            $('#confirmIssueBtn').on('click', confirmIssueItem);
            $('#issueQty').on('input', updateIssueRejectQty);
            $('#mrp').on('change', function() {
                applyFormMrpQty();
            });
            $('#issueMrp').on('change', function() {
                applyIssueMrpQty();
            });

            $('#issueItemModal').on('shown.bs.modal', function() {
                $('#issueBarcode').trigger('focus');
                if ($('#issueBarcode').val().trim()) {
                    scanIssueBarcode();
                }
            });

            $('#issueItemModal').on('hidden.bs.modal', function() {
                $('#issueRowIndex').val('');
                $('#issueBarcode, #issueScannedName, #issueAvailableQty, #issueQty, #issueRejectQty').val('');
                resetMrpSelect($('#issueMrp'));
                issueScanProduct = null;
                issueMrpOptions = [];
                issueTotalQty = 0;
            });
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
                        '<tr><td colspan="5" class="text-center text-muted">No items found</td></tr>');
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
                url: "{{ route('agent-indent.pending-indents') }}",
                method: 'POST',
                data: {
                    agent_id: agentId,
                    indent_type_id: 1,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    const rows = (response.success && Array.isArray(response.data)) ? response.data : [];
                    const usable = rows.filter(function(indent) {
                        let items = indent.Item_Data || [];
                        if (typeof items === 'string') {
                            try { items = JSON.parse(items); } catch (e) { items = []; }
                        }
                        return Array.isArray(items) && items.length > 0;
                    });

                    if (!usable.length) {
                        Swal.fire('Info', 'No pending indents found for this agent', 'info');
                        $('#radioNew').prop('checked', true);
                        $('#productSection').show();
                        return;
                    }

                    renderPendingIndents(usable);
                    $('#pendingIndentModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#pendingIndentModal').modal('show');
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
                let items = indent.Item_Data || [];
                if (typeof items === 'string') {
                    try { items = JSON.parse(items); } catch (e) { items = []; }
                }
                if (!Array.isArray(items) || !items.length) return;

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
                                    <td>${it.Prod_ShortNm || ''}</td>
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
                const mapped = indentMap[indentId];
                if (!mapped) return;
                const { items, date } = mapped;
                if (!items || !items.length) {
                    Swal.fire('Warning', 'No items found in this indent', 'warning');
                    return;
                }

                $('#selectedIndentId').val(indentId);
                // Use today's date as issue date (not old requisition date)
                $('#indentDate').val('{{ date('Y-m-d') }}').prop('disabled', false);
                indentItems = items.map(it => ({
                    indent_sl: it.Indent_Sl || 0,
                    barcode: '',
                    prod_id: it.Prod_Id,
                    unit_id: it.Unit_Id,
                    unit_name: it.Unit_Name || '',
                    product_name: it.Prod_ShortNm,
                    mrp: 0,
                    available_qty: 0,
                    pack_date: '',
                    quantity: it.Quantity,
                    requested_qty: it.Quantity,
                    issued: false
                }));
                renderItemsTable();
                $('#pendingIndentModal').modal('hide');
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

        function applyProductDetails(product) {
            if (product.Prod_Code) {
                $('#productBarcode').val(product.Prod_Code);
            }
            $('#productName').val(product.Prod_ShortNm);
            currentTotalQty = parseFloat(product.Avil_Qnty) || 0;
            currentMrpOptions = collectMrpOptions(product);
            if (!currentMrpOptions.length) {
                currentMrpOptions = [{
                    mrp: parseMrpValue(product.MRP),
                    qty: currentTotalQty
                }];
            }
            fillMrpSelect($('#mrp'), currentMrpOptions, product.MRP);
            applyFormMrpQty();
            $('#packDate').val(product.Pack_Date);
            $('#productBarcode').data('prod-id', product.Prod_Id);
            $('#productBarcode').data('unit-id', product.Unit_Id);
            $('#productBarcode').data('unit-name', product.Unit_Name || '');
            const available = parseFloat($('#availableQty').val()) || 0;
            const anyStock = currentMrpOptions.some(function(o) {
                const q = o.qty == null ? currentTotalQty : o.qty;
                return q > 0;
            });
            if (available > 0) {
                $('#quantity').focus();
            } else {
                $('#mrp').focus();
                if (!anyStock) {
                    Swal.fire('Warning', 'This product is out of stock!', 'warning');
                }
            }
            $('#productBarcode').addClass('is-valid');
            setTimeout(() => $('#productBarcode').removeClass('is-valid'), 2000);
        }

        function fetchProductById(prodId) {
            if (!validateIndentDate()) return;
            const indentDate = $('#indentDate').val();
            $('#productName, #availableQty, #packDate').val('');
            resetMrpSelect($('#mrp'));
            currentMrpOptions = [];
            currentTotalQty = 0;

            $.get("{{ route('agent-indent.item-info') }}", {
                prod_id: prodId,
                sale_date: indentDate
            }).done(function(product) {
                applyProductDetails(product);
            }).fail(function(xhr) {
                $('#productName, #availableQty, #packDate').val('');
                resetMrpSelect($('#mrp'));
                currentMrpOptions = [];
                currentTotalQty = 0;
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

        function pickUniqueItem(data, code) {
            if (!data || !data.length) return null;
            const needle = String(code).toLowerCase();
            const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === needle);
            if (exact.length === 1) return exact[0];
            if (data.length === 1) return data[0];
            return null;
        }

        function searchCodeOrOpenPicker(code) {
            $.get("{{ route('agent-indent.items') }}", {
                code: code,
                cat_id: 0,
                sub_cat_id: 0
            }, function(data) {
                const unique = pickUniqueItem(data, code);
                if (unique) {
                    fetchProductById(unique.Prod_Id);
                    return;
                }
                openItemPickerModal(data || [], true, code);
            }).fail(function() {
                openItemPickerModal([], true, code);
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

            $('#productName, #availableQty, #packDate').val('');
            resetMrpSelect($('#mrp'));
            currentMrpOptions = [];
            currentTotalQty = 0;

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
                        searchCodeOrOpenPicker(barcode);
                    }
                },
                error: function() {
                    searchCodeOrOpenPicker(barcode);
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
            const unitName = $('#productBarcode').data('unit-name') || '';

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
            if (!mrp) {
                Swal.fire('Error', 'Please select an MRP', 'error');
                $('#mrp').focus();
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
                quantity: qty,
                requested_qty: qty,
                issued: true
            });
            $('#indentDate').prop('disabled', true);
            renderItemsTable();
            clearProductFields();
            $('#productBarcode').focus();
        }

        function deleteItemRow(index) {
            const isRequisition = $('input[name="indentType"]:checked').val() === '1';
            const item = indentItems[index];
            if (!item) return;

            if (!isRequisition || !item.indent_sl) {
                Swal.fire({
                    title: 'Delete Product',
                    text: 'Remove this product from the list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        indentItems.splice(index, 1);
                        renderItemsTable();
                    }
                });
                return;
            }

            const indentId = $('#selectedIndentId').val();
            const agentId = $('#agentId').val();
            Swal.fire({
                title: 'Delete Product',
                text: 'Delete this product from the requisition? It will not appear again when you open this agent requisition.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('agent-indent.delete-item') }}",
                    method: 'POST',
                    data: {
                        indent_sl: item.indent_sl,
                        indent_id: indentId,
                        agent_id: agentId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        indentItems.splice(index, 1);
                        renderItemsTable();
                        if ((res.remaining_count || 0) <= 0) {
                            resetForm();
                        }
                        Swal.fire('Deleted', res.message || 'Item deleted', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete item', 'error');
                    }
                });
            });
        }

        function renderItemsTable() {
            if (!dataTable) {
                return;
            }
            dataTable.clear();
            const isRequisition = $('input[name="indentType"]:checked').val() === '1';
            indentItems.forEach(function(item, i) {
                let actions = '';
                if (isRequisition) {
                    const issueBtn = item.issued
                        ? `<button class="btn btn-success btn-sm me-1" onclick="openIssueModal(${i})" title="Issued — click to re-scan"><i class="fas fa-check"></i> <i class="fas fa-barcode"></i></button>`
                        : `<button class="btn btn-primary btn-sm me-1" onclick="openIssueModal(${i})" title="Scan barcode to issue"><i class="fas fa-barcode"></i></button>`;
                    actions = issueBtn
                        + `<button class="btn btn-danger btn-sm" onclick="deleteItemRow(${i})" title="Delete"><i class="fas fa-trash"></i></button>`;
                } else {
                    actions = `<button class="btn btn-danger btn-sm" onclick="deleteItemRow(${i})" title="Delete"><i class="fas fa-trash"></i></button>`;
                }
                const requested = parseFloat(item.requested_qty || item.quantity) || 0;
                const issuedQty = parseFloat(item.quantity) || 0;
                const qtyValue = isRequisition && item.issued ? issuedQty : (isRequisition ? requested : issuedQty);
                const unit = item.unit_name ? (' ' + item.unit_name) : '';
                const qtyDisplay = qtyValue + unit;
                const mrpDisplay = parseMrpValue(item.mrp) > 0 ? mrpKey(item.mrp) : '-';
                const nameDisplay = isRequisition && item.issued
                    ? `<span class="text-success fw-semibold">${item.product_name}</span>`
                    : item.product_name;
                dataTable.row.add([
                    i + 1,
                    nameDisplay,
                    `<div class="text-end">${mrpDisplay}</div>`,
                    qtyDisplay,
                    `<div class="text-center">${actions}</div>`
                ]);
            });
            dataTable.draw();
        }

        function updateIssueRejectQty() {
            const requested = parseFloat($('#issueRequestedQty').val()) || 0;
            const issueQty = parseFloat($('#issueQty').val()) || 0;
            $('#issueRejectQty').val(Math.max(0, requested - issueQty));
        }

        function openIssueModal(index) {
            const item = indentItems[index];
            if (!item) return;
            $('#issueRowIndex').val(index);
            $('#issueItemName').val(item.product_name);
            $('#issueRequestedQty').val(item.requested_qty || item.quantity);
            $('#issueAvailableQty').val(item.available_qty || '');
            $('#issueScannedName').val('');
            resetMrpSelect($('#issueMrp'));
            issueMrpOptions = [];
            issueTotalQty = 0;
            $('#issueBarcode').val(item.issued ? (item.barcode || '') : '');
            $('#issueQty').val(item.issued ? item.quantity : (item.requested_qty || item.quantity));
            updateIssueRejectQty();
            issueScanProduct = null;
            $('#issueItemModal').modal('show');
        }

        function applyIssueProductRates(product, preferredMrp) {
            issueTotalQty = parseFloat(product.Avil_Qnty) || 0;
            issueMrpOptions = collectMrpOptions(product);
            if (!issueMrpOptions.length) {
                issueMrpOptions = [{
                    mrp: parseMrpValue(product.MRP),
                    qty: issueTotalQty
                }];
            }
            fillMrpSelect($('#issueMrp'), issueMrpOptions, preferredMrp || product.MRP);
            applyIssueMrpQty();
        }

        function scanIssueBarcode() {
            const barcode = $('#issueBarcode').val().trim();
            if (!barcode) {
                Swal.fire('Error', 'Please scan or enter barcode', 'error');
                return;
            }
            if (!validateIndentDate()) return;
            const indentDate = $('#indentDate').val();
            const index = parseInt($('#issueRowIndex').val(), 10);
            const row = indentItems[index];
            if (!row) return;

            $('#issueScannedName, #issueAvailableQty').val('');
            resetMrpSelect($('#issueMrp'));
            $.ajax({
                url: "{{ route('agent-indent.get-product-info') }}",
                method: 'POST',
                data: {
                    barcode: barcode,
                    date: indentDate,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (!response.success || !response.data) {
                        Swal.fire('Product Not Found', response.message || 'Invalid barcode', 'warning');
                        return;
                    }
                    const product = response.data;
                    if (parseInt(product.Prod_Id, 10) !== parseInt(row.prod_id, 10)) {
                        Swal.fire('Wrong Product',
                            'Scanned item does not match this requisition line (' + row.product_name + ').',
                            'warning');
                        return;
                    }
                    $('#issueScannedName').val(product.Prod_ShortNm);
                    issueScanProduct = product;
                    applyIssueProductRates(product, row.mrp || product.MRP);
                    const available = parseFloat($('#issueAvailableQty').val()) || 0;
                    const anyStock = issueMrpOptions.some(function(o) {
                        const q = o.qty == null ? issueTotalQty : o.qty;
                        return q > 0;
                    });
                    if (available <= 0 && !anyStock) {
                        Swal.fire('Warning', 'This product is out of stock!', 'warning');
                        return;
                    }
                    $('#issueMrp').trigger('focus');
                },
                error: function() {
                    Swal.fire('Error', 'Failed to fetch product details', 'error');
                }
            });
        }

        function confirmIssueItem() {
            const index = parseInt($('#issueRowIndex').val(), 10);
            const row = indentItems[index];
            if (!row) return;
            const barcode = $('#issueBarcode').val().trim();
            const qty = parseFloat($('#issueQty').val());
            const available = parseFloat($('#issueAvailableQty').val()) || 0;
            const requested = parseFloat(row.requested_qty || row.quantity) || 0;
            const mrp = parseFloat($('#issueMrp').val()) || 0;

            if (!barcode || !issueScanProduct) {
                Swal.fire('Error', 'Scan the barcode first', 'error');
                $('#issueBarcode').trigger('focus');
                return;
            }
            if (parseInt(issueScanProduct.Prod_Id, 10) !== parseInt(row.prod_id, 10)) {
                Swal.fire('Wrong Product', 'Scanned item does not match this requisition line', 'warning');
                return;
            }
            if (!mrp) {
                Swal.fire('Error', 'Please select an MRP', 'error');
                $('#issueMrp').trigger('focus');
                return;
            }
            if (!qty || qty <= 0) {
                Swal.fire('Error', 'Issue quantity must be greater than 0', 'error');
                $('#issueQty').trigger('focus');
                return;
            }
            if (qty > requested) {
                Swal.fire('Error', 'Issue qty cannot exceed requested qty (' + requested + ')', 'error');
                $('#issueQty').trigger('focus');
                return;
            }
            if (qty > available) {
                Swal.fire('Error', 'Issue qty cannot exceed available qty (' + available + ') at MRP ' + mrpKey(mrp), 'error');
                $('#issueQty').trigger('focus');
                return;
            }

            row.barcode = barcode;
            row.unit_id = issueScanProduct.Unit_Id || row.unit_id;
            row.unit_name = issueScanProduct.Unit_Name || row.unit_name || '';
            row.mrp = mrp;
            row.available_qty = available;
            row.pack_date = issueScanProduct.Pack_Date || '';
            row.quantity = qty;
            row.issued = true;
            renderItemsTable();
            $('#issueItemModal').modal('hide');
        }

        function clearProductFields() {
            $('#productBarcode, #productName, #availableQty, #packDate, #quantity').val('');
            resetMrpSelect($('#mrp'));
            currentMrpOptions = [];
            currentTotalQty = 0;
            $('#productBarcode').removeClass('is-valid is-invalid').removeData('prod-id unit-id unit-name');
            $('#quantity').removeAttr('max');
        }

        function fmtPrintQty(n) {
            const v = parseFloat(n);
            if (isNaN(v)) return '0';
            return Number.isInteger(v) ? String(v) : v.toFixed(2);
        }

        function printIndentBill() {
            const $agent = $('#agentId option:selected');
            const agentName = $agent.data('name') || $agent.text() || '';
            const agentCode = $agent.data('code') || '';
            const issueDateRaw = $('#indentDate').val();
            const issueDate = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay(issueDateRaw)
                : issueDateRaw;
            const indentType = $('input[name="indentType"]:checked').val();
            const formLabel = indentType === '1' ? 'Requisition Issue' : 'New Indent';

            let rowsHtml = '';
            let totalAmount = 0;
            indentItems.forEach(function(item, i) {
                const issueQty = parseFloat(item.quantity) || 0;
                const unit = item.unit_name ? (' ' + item.unit_name) : '';
                const mrpVal = parseMrpValue(item.mrp);
                const mrp = mrpVal > 0 ? mrpKey(item.mrp) : '-';
                const amount = mrpVal * issueQty;
                totalAmount += amount;
                rowsHtml += `<tr>
                    <td class="c">${i + 1}</td>
                    <td>${item.product_name || ''}</td>
                    <td class="r">${fmtPrintQty(issueQty)}${unit}</td>
                    <td class="r">${mrp}</td>
                    <td class="r">${amount.toFixed(2)}</td>
                </tr>`;
            });

            const billHtml = `
            <div class="sheet">
                <div class="org">
                    <h2>${orgName || 'Smart Inventory'}</h2>
                    ${branchName ? `<p class="branch">${branchName}</p>` : ''}
                    <h3 class="title">Agent Indent Bill</h3>
                </div>
                <table class="meta">
                    <tr><td class="lbl">Issue Date</td><td>: ${issueDate || ''}</td>
                        <td class="lbl">Form</td><td>: ${formLabel}</td></tr>
                    <tr><td class="lbl">Agent Name</td><td>: ${agentName}</td>
                        <td class="lbl">Agent Code</td><td>: ${agentCode}</td></tr>
                </table>
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:40px;">Sl</th>
                            <th>Item Name</th>
                            <th style="width:120px;">Issue Quantity</th>
                            <th style="width:90px;">MRP</th>
                            <th style="width:110px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml || '<tr><td colspan="5" class="c">No items</td></tr>'}
                    </tbody>
                </table>
                <div class="total-wrap">Total Amount : ${totalAmount.toFixed(2)}</div>
                <div class="sign-wrap">
                    <div class="sign-box"><div class="sign-line">Authorized Signature</div></div>
                    <div class="sign-box"><div class="sign-line">Agent Signature</div></div>
                </div>
                <div class="print-foot">Generated By : ${generatedBy || 'User'}</div>
            </div>`;

            let iframe = document.getElementById('indentPrintFrame');
            if (iframe) {
                if (iframe.dataset.blobUrl) {
                    URL.revokeObjectURL(iframe.dataset.blobUrl);
                }
                iframe.remove();
            }

            iframe = document.createElement('iframe');
            iframe.id = 'indentPrintFrame';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
            document.body.appendChild(iframe);

            const fullHtml = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title></title>
            <style>
                @page { size: A4; margin: 14mm 12mm 18mm 12mm; }
                html, body { margin: 0; padding: 0; }
                body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
                .sheet { width: 100%; min-height: 100%; position: relative; padding-bottom: 28px; box-sizing: border-box; }
                .print-foot {
                    position: fixed;
                    left: 0;
                    bottom: 4mm;
                    font-size: 11px;
                    text-align: left;
                }
                .org { text-align: center; width: 100%; margin: 0 0 14px 0; }
                .org h2 { margin: 0 0 4px; font-size: 20px; text-transform: uppercase; text-align: center; }
                .org .branch { margin: 0 0 10px; font-size: 12px; text-align: center; }
                .org .title {
                    display: block;
                    width: 100%;
                    margin: 10px 0 0;
                    padding: 0;
                    font-size: 16px;
                    font-weight: bold;
                    text-align: center;
                    text-decoration: underline;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .meta { width: 100%; margin-bottom: 14px; }
                .meta td { padding: 3px 0; vertical-align: top; }
                .meta .lbl { width: 110px; font-weight: bold; }
                table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
                table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; }
                table.items th { background: #f2f2f2; text-align: center; }
                .c { text-align: center; }
                .r { text-align: right; white-space: nowrap; }
                .total-wrap {
                    margin-top: 12px;
                    text-align: right;
                    font-size: 14px;
                    font-weight: bold;
                }
                .sign-wrap { display: flex; justify-content: space-between; margin-top: 70px; padding: 0 10px; }
                .sign-box { width: 40%; text-align: center; }
                .sign-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 6px; font-weight: bold; }
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .print-foot { position: fixed; left: 0; bottom: 4mm; }
                }
            </style></head><body>${billHtml}</body></html>`;

            const blobUrl = URL.createObjectURL(new Blob([fullHtml], { type: 'text/html' }));
            iframe.dataset.blobUrl = blobUrl;
            iframe.onload = function() {
                setTimeout(function() {
                    const prevTitle = document.title;
                    document.title = ' ';
                    const restoreTitle = function() {
                        document.title = prevTitle;
                        window.removeEventListener('focus', restoreTitle);
                    };
                    window.addEventListener('focus', restoreTitle);
                    try {
                        iframe.contentWindow.document.title = '';
                    } catch (e) {}
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(restoreTitle, 1500);
                }, 100);
            };
            iframe.src = blobUrl;
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
            if (indentType === '1') {
                const notIssued = indentItems.filter(item => !item.issued);
                if (notIssued.length) {
                    Swal.fire('Error', 'Scan barcode and issue each requisition item before saving', 'error');
                    return;
                }
            }

            $('#saveBtn').prop('disabled', true).text('Saving...');

            const saveData = {
                indent_date: $('#indentDate').val(),
                agent_id: $('#agentId').val(),
                indent_type: indentType,
                indent_id: indentId,
                items: indentItems.map(item => ({
                    prod_id: item.prod_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity,
                    mrp: parseMrpValue(item.mrp),
                    rejected: 0
                })),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('agent-indent.store') }}",
                method: 'POST',
                data: saveData,
                success: function(response) {
                    printIndentBill();
                    Swal.fire('Success!', response.message || 'Agent indent saved successfully', 'success')
                        .then(function() {
                            window.location.reload();
                        });
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
            $('#selectedIndentId').val('');
            $('#indentDate').val('{{ date('Y-m-d') }}').prop('disabled', false);
            $('#radioNew').prop('checked', true);
            $('#productSection').show();
            clearProductFields();
            $('#saveBtn').prop('disabled', false).text('Save Indent');
            if ($('#agentId').hasClass('select2-hidden-accessible')) {
                $('#agentId').val(null).trigger('change');
            } else {
                $('#agentId').val('');
            }
            renderItemsTable();
        }
    </script>
@endpush
