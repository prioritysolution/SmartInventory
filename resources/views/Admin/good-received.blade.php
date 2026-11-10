@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
    <style>
        .scrollable-table {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: auto;
        }

        @@media (min-width: 1200px) {
            .modal-xl-custom {
                max-width: 1400px;
            }
        }

        .calc-label {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        #itemPickerTable tbody tr {
            cursor: pointer;
        }

        #itemPickerTable tbody tr:hover {
            background-color: #e8f4ff;
        }

        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            background: #fff;
        }

        .section-card h6 {
            font-weight: 600;
            margin-bottom: 12px;
            color: #495057;
        }

        .top-entry-row > [class*="col-"] {
            display: flex;
        }

        .top-entry-row .section-card {
            width: 100%;
        }

        .item-entry .form-label {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
                <h6 class="mb-0">Good Received (Purchase Entry)</h6>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="searchPurchaseBtn" style="width: 100px;">
                    <svg aria-hidden="true" class="me-2" width="18" height="18" viewBox="0 0 18 18">
                        <path
                            d="m18 16.5-5.14-5.18h-.35a7 7 0 1 0-1.19 1.19v.35L16.5 18zM12 7A5 5 0 1 1 2 7a5 5 0 0 1 10 0">
                        </path>
                    </svg> Search
                </button>
            </div>

            <div class="row mb-3 top-entry-row">
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Purchase Info</h6>
                            <div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="gstMode" id="withGst"
                                        value="1">
                                    <label class="form-check-label" for="withGst">With GST</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="gstMode" id="withoutGst"
                                        value="0" checked>
                                    <label class="form-check-label" for="withoutGst">Without GST</label>
                                </div>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Purchase Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="purchaseDate" min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Party<span class="text-danger">*</span></label>
                            <select class="form-select" id="partyId" data-si-ajax="parties" data-party-type="1">
                                <option value="">-- Select Party --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">INVOICE NO</label>
                            <input type="text" class="form-control" id="purchaseNo" maxlength="20" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transMode" id="transCash"
                                    value="1" checked>
                                <label class="form-check-label" for="transCash">Cash</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transMode" id="transBank"
                                    value="2">
                                <label class="form-check-label" for="transBank">Bank</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transMode" id="transCredit"
                                    value="3">
                                <label class="form-check-label" for="transCredit">Credit</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ref Voucher No</label>
                                <input type="text" class="form-control" id="refVoucherNo" maxlength="20"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3" id="bankSelectDiv" style="display:none;">
                                <label class="form-label">Select Bank<span class="text-danger">*</span></label>
                                <select class="form-select" id="bankAccountId">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->Account_Id }}">{{ $bank->Ledger_Name ?? $bank->Account_Desc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3" id="instrumentNoDiv" style="display:none;">
                                <label class="form-label">Instrument No<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="instrumentNo" maxlength="50"
                                    autocomplete="off" placeholder="Cheque / UTR / Instrument No">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT BOX: Item Section --}}
                <div class="col-md-6">
                    <div class="section-card item-entry h-100">
                        <h6>Item Section</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product Code<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="productCodeInput"
                                        placeholder="Enter product code" autocomplete="new-password" maxlength="20">
                                    <button class="btn btn-primary" type="button" id="productSearchBtn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Item Selected</label>
                                <input type="text" class="form-control calc-label" id="itemSelected" readonly
                                    placeholder="Search product to pick an item">
                                <input type="hidden" id="selectedItemId">
                                <input type="hidden" id="selectedHsnCode">
                                <input type="hidden" id="unitId">
                                <input type="hidden" id="originalCgst">
                                <input type="hidden" id="originalSgst">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control calc-label" id="unitDisplay" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Qty<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="1" min="1"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rate<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rate" step="0.01" min="0"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Amt</label>
                                <input type="text" class="form-control calc-label" id="totalAmount" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Disc %</label>
                                <input type="number" class="form-control" id="discountPercent" step="0.01"
                                    max="100" min="0" autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Disc Amt</label>
                                <input type="text" class="form-control calc-label" id="discountAmount" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Taxable Amt</label>
                                <input type="text" class="form-control calc-label" id="taxableAmount" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 gst-col">
                                <label class="form-label">CGST Rate</label>
                                <input type="text" class="form-control calc-label" id="cgstRate" readonly>
                            </div>
                            <div class="col-md-4 mb-3 gst-col">
                                <label class="form-label">CGST Amt</label>
                                <input type="text" class="form-control calc-label" id="cgstAmount" readonly>
                            </div>
                            <div class="col-md-4 mb-3 gst-col">
                                <label class="form-label">SGST Rate</label>
                                <input type="text" class="form-control calc-label" id="sgstRate" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 gst-col">
                                <label class="form-label">SGST Amt</label>
                                <input type="text" class="form-control calc-label" id="sgstAmount" readonly>
                            </div>
                            <div class="col-md-4 mb-3 gst-col">
                                <label class="form-label">Total GST</label>
                                <input type="text" class="form-control calc-label" id="totalGst" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Net Amt</label>
                                <input type="text" class="form-control calc-label" id="netAmount" readonly>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale MRP<span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="saleMrp" min="0.01"
                                    required autocomplete="off" >
                            </div>
                   <div class="col-md-4 mb-3" id="itemPurchaseDateDiv" style="display:none; overflow: visible;">
                                <label class="form-label">Pack Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="itemPurchaseDate"
                                    max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3 ms-md-auto">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-success w-100 d-block" id="addItemBtn">+ Add
                                    Item</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                {{-- LEFT BOX: Items Table --}}
                <div class="col-md-8">
                    <div class="section-card h-100">
                        <h6>Items List</h6>
                        <div class="table-responsive scrollable-table">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Rate</th>
                                        <th>Total Amt</th>
                                        <th>Disc %</th>
                                        <th>Disc Amt</th>
                                        <th>Taxable Amt</th>
                                        <th class="gst-col">CGST Rate</th>
                                        <th class="gst-col">CGST Amt</th>
                                        <th class="gst-col">SGST Rate</th>
                                        <th class="gst-col">SGST Amt</th>
                                        <th class="gst-col">Total GST</th>
                                        <th>Net Amt</th>
                                        <th>Sale MRP</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- RIGHT BOX: Summary --}}
                <div class="col-md-4">
                    <div class="section-card h-100">
                        <h6 class="ms-3">Purchase Summary</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Total Amount (A)</th>
                                <td><input type="text" class="form-control calc-label" id="summaryTotalAmount"
                                        readonly></td>
                            </tr>
                            <tr>
                                <th>Discount (%)</th>
                                <td><input type="number" step="0.01" min="0" max="100"
                                        class="form-control" id="summaryDiscPercent" placeholder="0.00"></td>
                            </tr>
                            <tr>
                                <th>Total Discount Amount (B)</th>
                                <td><input type="text" class="form-control calc-label" id="totalDiscountAmount"
                                        readonly></td>
                            </tr>
                            <tr>
                                <th>Total Taxable Amount (C)</th>
                                <td><input type="text" class="form-control calc-label" id="totalTaxableAmount"
                                        readonly></td>
                            </tr>
                            <tr>
                                <th>Total GST Amount (D)</th>
                                <td><input type="text" class="form-control calc-label" id="totalGSTAmount" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Freight Amount (E)</th>
                                <td><input type="number" step="0.01" min="0" max="999999.99"
                                        class="form-control" id="freightAmt" placeholder="0.00" autocomplete="off"></td>
                            </tr>
                            <tr>
                                <th>Round Off (+/-) (F)</th>
                                <td><input type="text" class="form-control calc-label" id="roundOff" readonly></td>
                            </tr>
                            <tr>
                                <th><strong>Net Amount (C+D+E+F)</strong></th>
                                <td><input type="text" class="form-control calc-label fw-bold" id="finalNetAmount"
                                        readonly></td>
                            </tr>
                        </table>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="savePurchase">Save</button>
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
                    <h5 class="modal-title" id="itemPickerTitle">Select Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Filters row --}}
                    <div class="row g-2 mb-3" id="modalFilterRow">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Category</label>
                            <select class="form-select form-select-sm" id="modalCateId">
                                <option value="0">-- All Categories --</option>
                                @foreach ($categories as $cat)
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


    {{-- Purchase Search Modal --}}
    <div class="modal fade" id="purchaseSearchModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="searchFromDate"
                                max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="searchToDate"
                                max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Select Party</label>
                            <select class="form-select" id="searchPartyId" data-si-ajax="parties" data-party-type="1">
                                <option value="0">-- All Parties --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bill No / Ref Bill No</label>
                            <input type="text" class="form-control" id="searchBillNo" maxlength="120">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="searchPurchaseGo">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="purchaseSearchTable" class="table table-bordered table-sm w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Invoice No</th>
                                    <th>Ref No</th>
                                    <th>Party Name</th>
                                    <th>Invoice Date</th>
                                    <th>Net Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseSearchBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Use filters above to search</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.item-picker-pagination')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemsArray = [];
        let itemPickerDT = null;
        let itemPickerFilters = null;
        let allItemsData = [];
        let currentPurchaseId = 0;
        let currentVouchId = 0;
        let isEditingItem = false;

        function showPackDateField(show) {
            if (show) {
                $('#itemPurchaseDateDiv').show();
                if (window.siDate && typeof window.siDate.init === 'function') {
                    setTimeout(function () {
                        window.siDate.init('#itemPurchaseDateDiv');
                    }, 0);
                }
            } else {
                $('#itemPurchaseDateDiv').hide();
                $('#itemPurchaseDate').val('');
            }
        }

        $(document).ready(function() {

            $('#bankAccountId').select2({
                placeholder: '-- Select Bank --',
                allowClear: true
            });
            $('input[name="gstMode"]').on('change', function() {
                const isWithGst = $(this).val() === '1';
                $('.gst-col').toggle(isWithGst);
                if (isWithGst) {
                    $('#cgstRate').val($('#originalCgst').val() || 0);
                    $('#sgstRate').val($('#originalSgst').val() || 0);
                } else {
                    $('#cgstRate').val(0);
                    $('#sgstRate').val(0);
                }
                calculateAmounts();
            });
            $('.gst-col').toggle($('input[name="gstMode"]:checked').val() === '1');

            function applyGoodReceivedItem(item) {
                const gst = item.GST_Data ? JSON.parse(item.GST_Data) : {
                    CGST: 0,
                    SGST: 0
                };
                clearItemSelection();
                $('#productCodeInput').val(item.Prod_Code);
                $('#selectedItemId').val(item.Prod_Id);
                $('#selectedHsnCode').val(item.Gst_Id);
                $('#itemSelected').val(item.Prod_ShortNm);
                $('#unitId').val(item.Unit_Id);
                $('#unitDisplay').val(item.Unit_Name);
                const isWithGst = $('input[name="gstMode"]:checked').val() === '1';
                $('#cgstRate').val(isWithGst ? gst.CGST : 0);
                $('#sgstRate').val(isWithGst ? gst.SGST : 0);
                $('#originalCgst').val(gst.CGST);
                $('#originalSgst').val(gst.SGST);
                if (item.Is_Fmcg == 1) {
                    showPackDateField(true);
                } else {
                    showPackDateField(false);
                }
                $('#quantity').focus();
            }

            function resolvePurchaseCode(data, code) {
                const exactMatch = (data || []).find(item =>
                    String(item.Prod_Code || '').toLowerCase() === String(code).toLowerCase()
                );
                const unique = exactMatch || ((data || []).length === 1 ? data[0] : null);
                if (unique) {
                    applyGoodReceivedItem(unique);
                    return;
                }
                openItemPickerModal(data || [], true, code);
            }

            function lookupProductCode(code) {
                $.get("{{ url('good-received/items') }}", {
                    code: code,
                    cat_id: 0,
                    sub_cat_id: 0
                }, function(data) {
                    resolvePurchaseCode(data, code);
                }).fail(function() {
                    openItemPickerModal([], true, code);
                });
            }

            // Product code search
            $('#productCodeInput').on('keypress', function(e) {
                if (e.which !== 13) return;
                e.preventDefault();
                const code = $(this).val().trim();
                if (!code) return;
                lookupProductCode(code);
            });



            // ── CLICK search icon → check exact match first, else open modal ──
            $('#productSearchBtn').on('click', function(e) {
                e.preventDefault();
                const code = $('#productCodeInput').val().trim();
                if (!code) {
                    openItemPickerModal([], false, '');
                    return;
                }
                lookupProductCode(code);
            });

            // ── Modal category change → load subcategories (single binding) ──
            $('#modalCateId').on('change', function() {
                const catId = parseInt($(this).val()) || 0;
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                if (!catId) return;
                $.get("{{ route('good-received.subcats') }}", {
                    cat_id: catId
                }, function(subs) {
                    subs.forEach(s => {
                        $('#modalSubCateId').append(
                            `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                        );
                    });
                });
            });

            // ── Modal search button ──
            $('#modalSearchBtn').on('click', function() {
                const catId = parseInt($('#modalCateId').val()) || 0;
                const subCatId = parseInt($('#modalSubCateId').val()) || 0;
                const code = $('#modalSearchInput').val().trim();

                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');

                itemPickerFilters = {
                    cat_id: catId,
                    sub_cat_id: subCatId,
                    code: code
                };
                loadItemPickerPage(1);
            });

            // ── Modal search input Enter key ──
            $('#modalSearchInput').on('keypress', function(e) {
                if (e.which === 13) $('#modalSearchBtn').trigger('click');
            });

            // ── Reset modal filters when closed ──
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
                $('#itemPickerPagination').hide().empty();
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
                allItemsData = data;

                // Always show filter row
                $('#modalFilterRow').show();

                // Partial code (e.g. "sm") must not auto-select category/subcategory.
                // Keep All Categories / All Sub Categories and show every matching item.
                $('#modalCateId').val('0');
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                $('#modalSearchInput').val(code || '');

                $('#itemPickerModal').modal('show');
                if (data.length > 0 || code) {
                    itemPickerFilters = { cat_id: 0, sub_cat_id: 0, code: code || '' };
                    loadItemPickerPage(1);
                }
            }



            // Item row click
            $(document).on('click', '#itemPickerTable tbody tr', function() {
                const cgst = parseFloat($(this).data('cgst')) || 0;
                const sgst = parseFloat($(this).data('sgst')) || 0;
                const isFmcg = $(this).data('fmcg') == 1;
                $('#productCodeInput').val($(this).data('code'));
                $('#selectedItemId').val($(this).data('id'));
                $('#selectedHsnCode').val($(this).data('hsn'));
                $('#itemSelected').val($(this).data('name'));
                $('#unitId').val($(this).data('unit'));
                $('#unitDisplay').val($(this).data('unitname'));
                if (isFmcg) {
                    showPackDateField(true);
                } else {
                    showPackDateField(false);
                }

                $('#itemPickerModal').modal('hide');
                clearItemCalc();
                const isWithGst = $('input[name="gstMode"]:checked').val() === '1';
                $('#cgstRate').val(isWithGst ? cgst : 0);
                $('#sgstRate').val(isWithGst ? sgst : 0);
                $('#originalCgst').val(cgst);
                $('#originalSgst').val(sgst);
                $('#quantity').focus();
            });

            $('#quantity, #rate, #discountPercent').on('input', function() {
                calculateAmounts();
            });

            $('#summaryDiscPercent').on('input', function() {
                if ($(this).prop('readonly')) return;
                const discPct = parseFloat($(this).val()) || 0;
                if (discPct > 100) {
                    $(this).val(100);
                    Swal.fire('Error', 'Discount % cannot be greater than 100', 'error');
                    return;
                }
                const totalAmt = itemsArray.reduce((s, i) => s + (parseFloat(i.total_amount) || 0), 0);
                const totalGST = itemsArray.reduce((s, i) => s + (parseFloat(i.total_gst) || 0), 0);
                const discAmt = totalAmt * (discPct / 100);
                const taxable = totalAmt - discAmt;
                $('#totalDiscountAmount').val(discAmt.toFixed(2));
                $('#totalTaxableAmount').val(taxable.toFixed(2));
                recalcSummaryTotals(taxable, totalGST);
            });

            $('#freightAmt').on('input', function() {
                const freight = parseFloat($(this).val()) || 0;
                if (freight < 0) {
                    $(this).val(0);
                    return;
                }
                if (freight > 999999.99) {
                    $(this).val('999999.99');
                    Swal.fire('Error', 'Freight Amount cannot exceed 999999.99', 'error');
                }
                const taxable = parseFloat($('#totalTaxableAmount').val()) || 0;
                const gst = parseFloat($('#totalGSTAmount').val()) || 0;
                recalcSummaryTotals(taxable, gst);
            });

            $('input[name="transMode"]').on('change', function() {
                if ($(this).val() === '2') {
                    $('#bankSelectDiv, #instrumentNoDiv').show();
                } else {
                    $('#bankSelectDiv, #instrumentNoDiv').hide();
                    $('#bankAccountId').val('').trigger('change');
                    $('#instrumentNo').val('');
                }
            });

            $('#addItemBtn').on('click', function() {
                if (!validateItemForm()) return;
                itemsArray.push({
                    item_id: $('#selectedItemId').val(),
                    hsn_code: parseInt($('#selectedHsnCode').val()) || 0,
                    item_name: $('#itemSelected').val(),
                    quantity: $('#quantity').val(),
                    unit_id: $('#unitId').val(),
                    unit_name: $('#unitDisplay').val(),
                    rate: $('#rate').val(),
                    total_amount: $('#totalAmount').val(),
                    discount_percent: $('#discountPercent').val() || 0,
                    discount_amount: $('#discountAmount').val() || 0,
                    taxable_amount: $('#taxableAmount').val(),
                    cgst_rate: $('#cgstRate').val(),
                    cgst_amount: $('#cgstAmount').val(),
                    sgst_rate: $('#sgstRate').val(),
                    sgst_amount: $('#sgstAmount').val(),
                    total_gst: $('#totalGst').val(),
                    net_amount: $('#netAmount').val(),
                    sale_mrp: $('#saleMrp').val() || 0,
                    item_purchase_date: $('#itemPurchaseDate').val() || '',
                });
                isEditingItem = false;
                renderItemsTable();
                clearItemSelection();
                $('#productCodeInput').val('');
                $('#itemPurchaseDateDiv').hide();
                $('#itemPurchaseDate').val('');
                $('#addItemBtn').text('+ Add Item').data('editing', false);
            });

            $(document).on('click', '.removeItem', function() {
                if (isEditingItem) return;
                itemsArray.splice($(this).data('index'), 1);
                renderItemsTable();
            });

            $(document).on('click', '.editItem', function() {
                if (isEditingItem) return;
                const index = $(this).data('index');
                const item = itemsArray[index];

                $('#selectedItemId').val(item.item_id);
                $('#selectedHsnCode').val(item.hsn_code);
                $('#itemSelected').val(item.item_name);
                $('#unitId').val(item.unit_id);
                $('#unitDisplay').val(item.unit_name);
                $('#quantity').val(item.quantity);
                $('#rate').val(item.rate);
                $('#discountPercent').val(item.discount_percent);
                $('#totalAmount').val(item.total_amount);
                $('#discountAmount').val(item.discount_amount);
                $('#taxableAmount').val(item.taxable_amount);
                $('#cgstRate').val(item.cgst_rate);
                $('#cgstAmount').val(item.cgst_amount);
                $('#sgstRate').val(item.sgst_rate);
                $('#sgstAmount').val(item.sgst_amount);
                $('#totalGst').val(item.total_gst);
                $('#netAmount').val(item.net_amount);
                $('#saleMrp').val(item.sale_mrp);
                $('#itemPurchaseDate').val(item.item_purchase_date);

                if (item.item_purchase_date) {
                    showPackDateField(true);
                }

                isEditingItem = true;
                itemsArray.splice(index, 1);
                renderItemsTable();
                $('#addItemBtn').text('Update Item').data('editing', true);
            });

            $('#savePurchase').on('click', function() {
                if (!$('#purchaseDate').val()) {
                    showValidationError('Purchase Date is required', '#purchaseDate');
                    return;
                }
                if (!$('#partyId').val()) {
                    showValidationError('Please select a Party', '#partyId');
                    return;
                }
                if (itemsArray.length === 0) {
                    showValidationError('Please add at least one item', '#productCodeInput');
                    return;
                }
                if ($('input[name="transMode"]:checked').val() === '2') {
                    if (!$('#bankAccountId').val()) {
                        showValidationError('Please select a Bank', '#bankAccountId');
                        return;
                    }
                    if (!$('#instrumentNo').val().trim()) {
                        showValidationError('Instrument No is required for Bank', '#instrumentNo');
                        return;
                    }
                }

                $(this).prop('disabled', true).text(currentPurchaseId > 0 ? 'Updating...' : 'Saving...');

                $.ajax({
                    url: "{{ route('good-received.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        purchase_id: currentPurchaseId,
                        vouch_id: currentVouchId, 
                        purchase_date: $('#purchaseDate').val(),
                        purchase_no: $('#purchaseNo').val(),
                        party_id: $('#partyId').val(),
                        trans_mode: $('input[name="transMode"]:checked').val(),
                        ref_vouch_no: $('#refVoucherNo').val(),
                        bank_id: $('#bankAccountId').val(),
                        instrument_no: $('#instrumentNo').val().trim(),
                        disc_percent: $('#summaryDiscPercent').prop('readonly') ? 0 : (parseFloat($(
                            '#summaryDiscPercent').val()) || 0),
                        disc_amt: $('#totalDiscountAmount').val() || 0,
                        freight_amt: $('#freightAmt').val() || 0,
                        round_off: $('#roundOff').val() || 0,
                        net_amt: $('#finalNetAmount').val() || 0,
                        items: itemsArray
                    },
                    success: function(res) {
                        const msg = currentPurchaseId > 0 ? 'Purchase Updated Successfully' :
                            res.message;
                        Swal.fire('Success', msg, 'success').then(() => resetForm());
                    },
                    error: function(xhr) {
                        $('#savePurchase').prop('disabled', false).text(currentPurchaseId > 0 ?
                            'Update' : 'Save');
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON
                            ?.message || 'Failed to save', 'error');
                    }
                });


            });

            $('#cancelBtn').on('click', resetForm);

            $(document).on('click', '.viewPurchase', function() {
                const purId = $(this).data('id');
                currentPurchaseId = purId;

                $.get("{{ url('good-received/details') }}/" + purId, function(data) {
                    $('#purchaseSearchModal').modal('hide');
                    currentVouchId = data.Voucher_Id ;
                    const purchaseHasGst = (data.Item_Details || []).some(item =>
                        (parseFloat(item.CGST_Prcnt) || 0) > 0 ||
                        (parseFloat(item.SGST_Prcnt) || 0) > 0 ||
                        (parseFloat(item.CGST_Amt) || 0) > 0 ||
                        (parseFloat(item.SGST_Amt) || 0) > 0
                    );
                    $('#withGst').prop('checked', purchaseHasGst);
                    $('#withoutGst').prop('checked', !purchaseHasGst);
                    $('.gst-col').toggle(purchaseHasGst);
                    $('#purchaseDate').val(data.Invoice_Date ? data.Invoice_Date.substring(0, 10) :
                        '');
                    if (typeof siSetAjaxSelect2Value === 'function') {
                        siSetAjaxSelect2Value('#partyId', data.Party_Id, data.Party_Name);
                    } else {
                        $('#partyId').val(data.Party_Id).trigger('change');
                    }
                    $('#purchaseNo').val(data.Ref_No);
                    const mode = String(data.Trans_Mode || '1');
                    $('input[name="transMode"][value="' + mode + '"]').prop('checked', true).trigger('change');
                    if (mode === '2') {
                        $('#bankAccountId').val(data.Bank_Ledg || '').trigger('change');
                        $('#instrumentNo').val(data.Instrument_No || '');
                    }

                    itemsArray = data.Item_Details.map(item => ({
                        item_id: item.Prod_Id,
                        hsn_code: parseInt(item.hsn_code) || 0,
                        item_name: item.Prod_Name,
                        quantity: parseFloat(item.qnty) || 0,
                        unit_id: item.Unit_Id,
                        unit_name: item.Unit_Name,
                        rate: item.Item_Rate,
                        total_amount: item.Item_Total,
                        discount_percent: item.Disc_Prcnt,
                        discount_amount: item.Disc_Amt,
                        taxable_amount: item.Taxable_Amt,
                        cgst_rate: item.CGST_Prcnt,
                        cgst_amount: item.CGST_Amt,
                        sgst_rate: item.SGST_Prcnt,
                        sgst_amount: item.SGST_Amt,
                        total_gst: (parseFloat(item.SGST_Amt) + parseFloat(item
                            .CGST_Amt)).toFixed(2),
                        net_amount: item.Net_Amt,
                        sale_mrp: item.MRP,
                        item_purchase_date: item.Pack_Date ? item.Pack_Date.substring(0,
                            10) : '',
                    }));

                    $('#freightAmt').val(data.Freight_Amt ?? 0);
                    renderItemsTable();
                    $('#savePurchase').text('Update');
                    $('#summaryDiscPercent').val(data.Disc_Percent);
                    $('#totalDiscountAmount').val(data.Disc_Amount);
                    $('#totalGSTAmount').val(data.GST_Amt);
                    $('#freightAmt').val(data.Freight_Amt ?? 0);
                    $('#roundOff').val(data.Round_Off);
                    $('#finalNetAmount').val(data.Net_Amt);

                }).fail(function() {
                    Swal.fire('Error', 'Failed to load purchase details', 'error');
                });
            });

            let purchaseSearchDT = null;

            $('#searchPurchaseBtn').on('click', function() {
                $('#purchaseSearchModal').modal('show');
            });

            $('#searchPurchaseGo').on('click', function() {
                const fromDate = $('#searchFromDate').val();
                const toDate = $('#searchToDate').val();
                const partyId = $('#searchPartyId').val() || 0;
                const billNo = $('#searchBillNo').val().trim();

                if (billNo && (!partyId || partyId === '0')) {
                    showValidationError('Please select a Party when searching by Bill No or Ref Bill No', '#searchPartyId');
                    return;
                }
                if ((!fromDate || !toDate) && !billNo) {
                    showValidationError('Enter Bill No or Ref Bill No, or select From Date and To Date',
                        !fromDate ? '#searchFromDate' : '#searchToDate');
                    return;
                }
                if ((fromDate && !toDate) || (!fromDate && toDate)) {
                    showValidationError('Please select both From Date and To Date',
                        !fromDate ? '#searchFromDate' : '#searchToDate');
                    return;
                }

                if (purchaseSearchDT) {
                    purchaseSearchDT.destroy();
                    purchaseSearchDT = null;
                }
                if (!window._grPurchaseSearchLazy) {
                    const $wrap = $('#purchaseSearchTable').closest('.table-responsive, .modal-body');
                    if (!$wrap.hasClass('si-lazy-scroll-box')) {
                        $wrap.addClass('si-lazy-scroll-box').css({ maxHeight: '360px', overflowY: 'auto' });
                    }
                    window._grPurchaseSearchLazy = SiLazyScroll.create({
                        scrollEl: $wrap,
                        tbody: '#purchaseSearchBody',
                        url: "{{ url('good-received/search') }}",
                        pageSize: 30,
                        colSpan: 7,
                        params: function () {
                            return {
                                from_date: $('#searchFromDate').val(),
                                to_date: $('#searchToDate').val(),
                                party_id: $('#searchPartyId').val() || 0,
                                bill_no: $('#searchBillNo').val().trim()
                            };
                        },
                        renderRow: function (row, i) {
                            return `<tr>
                            <td>${i + 1}</td>
                            <td>${row.Invoice_No ?? ''}</td>
                            <td>${row.Ref_No ?? ''}</td>
                            <td>${row.Party_Name ?? ''}</td>
                            <td>${siDate.toDisplay(row.Invoice_Date)}</td>
                            <td>${row.Net_Amt ?? ''}</td>
                            <td><button class="btn btn-sm btn-primary viewPurchase" data-id="${row.Pur_Id}">Edit</button></td>
                        </tr>`;
                        },
                        onError: function () {
                            Swal.fire('Error', 'Failed to load data', 'error');
                        }
                    });
                }
                window._grPurchaseSearchLazy.resetAndLoad();
            });

            $('#purchaseSearchModal').on('hidden.bs.modal', function() {
                if (purchaseSearchDT) {
                    purchaseSearchDT.destroy();
                    purchaseSearchDT = null;
                }
                $('#purchaseSearchBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Use filters above to search</td></tr>'
                );
                $('#searchFromDate, #searchToDate').val('');
                $('#searchBillNo').val('');
                $('#searchPartyId').val('0').trigger('change');
            });

        });

        function loadItemPickerPage(page) {
            window.fetchItemPickerPage(
                "{{ url('good-received/items') }}",
                itemPickerFilters,
                page,
                function(response) {
                    renderItemPickerTable(response.items || [], false, response);
                }
            );
        }

        function renderItemPickerTable(data, isCodeSearch, pagination) {
            setTimeout(function() {
                $('#itemPickerLoader').hide();

                if (!data.length) {
                    $('#itemPickerTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
                    $('#itemPickerTableWrap').show();
                    window.renderItemPickerPagination(pagination, loadItemPickerPage);
                    return;
                }

                $.each(data, function(i, item) {
                    const cgst = item.GST_Data ? JSON.parse(item.GST_Data).CGST : 0;
                    const sgst = item.GST_Data ? JSON.parse(item.GST_Data).SGST : 0;
                    $('#itemPickerTable tbody').append(
                        `<tr data-id="${item.Prod_Id}"
                     data-code="${item.Prod_Code}"
                     data-name="${item.Prod_ShortNm}"
                     data-unit="${item.Unit_Id}"
                     data-unitname="${item.Unit_Name}"
                     data-hsn="${item.Gst_Id ?? 0}"
                     data-cgst="${cgst}"
                     data-sgst="${sgst}"
                     data-fmcg="${item.Is_Fmcg ?? 0}"
                     data-cateid="${item.Prd_CateId ?? 0}"
                     data-subcateid="${item.Prd_SubCateId ?? 0}">
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
                window.renderItemPickerPagination(pagination, loadItemPickerPage);
            }, 0);
        }



        function calculateAmounts() {
            const qty = parseFloat($('#quantity').val()) || 0;
            const rate = parseFloat($('#rate').val()) || 0;
            const discPct = parseFloat($('#discountPercent').val()) || 0;

            const isWithGst = $('input[name="gstMode"]:checked').val() === '1';
            const cgstRate = isWithGst ? (parseFloat($('#cgstRate').val()) || 0) : 0;
            const sgstRate = isWithGst ? (parseFloat($('#sgstRate').val()) || 0) : 0;

            const totalAmt = qty * rate;
            const discAmt = totalAmt * (discPct / 100);
            const taxableAmt = totalAmt - discAmt;
            const cgstAmt = taxableAmt * (cgstRate / 100);
            const sgstAmt = taxableAmt * (sgstRate / 100);
            const totalGst = cgstAmt + sgstAmt;

            $('#totalAmount').val(totalAmt.toFixed(2));
            $('#discountAmount').val(discAmt.toFixed(2));
            $('#taxableAmount').val(taxableAmt.toFixed(2));
            $('#cgstAmount').val(cgstAmt.toFixed(2));
            $('#sgstAmount').val(sgstAmt.toFixed(2));
            $('#totalGst').val(totalGst.toFixed(2));
            $('#netAmount').val((taxableAmt + totalGst).toFixed(2));
        }

        function showValidationError(message, selector) {
            window.SIRequiredFieldFeedback.setNextField(selector);
            Swal.fire('Error', message, 'error');
        }

        function validateItemForm() {
            if (!$('#purchaseDate').val()) {
                showValidationError('Purchase Date is required before adding items', '#purchaseDate');
                return false;
            }
            const purchaseDate = new Date($('#purchaseDate').val());
            const today = new Date();
            const yearStart = new Date('{{ session('year_start') }}');
            const yearEnd = new Date('{{ session('year_end') }}');

            if (purchaseDate > today) {
                showValidationError('Purchase Date cannot be After Today', '#purchaseDate');
                return false;
            }
            if (purchaseDate < yearStart || purchaseDate > yearEnd) {
                showValidationError(
                    'Purchase Date must be between {{ session('year_start') }} and {{ session('year_end') }}',
                    '#purchaseDate'
                );
                return false;
            }
            if (!$('#selectedItemId').val()) {
                showValidationError('Please select an item', '#productCodeInput');
                return false;
            }

            const qty = parseFloat($('#quantity').val());
            const rate = parseFloat($('#rate').val());
            const discPct = parseFloat($('#discountPercent').val()) || 0;
            const totalAmt = parseFloat($('#totalAmount').val()) || 0;

            if (!$('#quantity').val() || qty <= 0) {
                showValidationError('Quantity must be greater than 0', '#quantity');
                return false;
            }
            if (!$('#rate').val()) {
                showValidationError('Rate is required', '#rate');
                return false;
            }
            if (discPct > 100) {
                showValidationError('Discount % cannot be greater than 100', '#discountPercent');
                return false;
            }
            if (rate > 99999999.99) {
                showValidationError('Rate cannot exceed 99999999.99', '#rate');
                return false;
            }
            const duplicateItem = itemsArray.some(item =>
                String(item.item_id) === String($('#selectedItemId').val()) &&
                Number(item.rate) === rate
            );
            if (duplicateItem) {
                showValidationError(
                    'This item with the same rate is already in the list. Edit the existing item instead.',
                    '#productCodeInput'
                );
                return false;
            }
            const saleMrp = parseFloat($('#saleMrp').val());
            if (!$('#saleMrp').val() || isNaN(saleMrp) || saleMrp <= 0) {
                showValidationError('Sale MRP is required', '#saleMrp');
                return false;
            }
            const netRate = rate * (1 - (Math.min(Math.max(discPct, 0), 100) / 100));
            if (saleMrp < netRate) {
                showValidationError(
                    `Sale MRP (${saleMrp}) cannot be less than discounted rate (${netRate.toFixed(2)})`,
                    '#saleMrp'
                );
                return false;
            }
            if (totalAmt > 99999999.99) {
                showValidationError('Total Amount (Qty × Rate) cannot exceed 99999999.99', '#quantity');
                return false;
            }
            if ($('#itemPurchaseDateDiv').is(':visible') && !$('#itemPurchaseDate').val()) {
                showValidationError('Pack Date is required for this category', '#itemPurchaseDate');
                return false;
            }
            if ($('#itemPurchaseDateDiv').is(':visible') && !isValidIsoDate($('#itemPurchaseDate').val())) {
                showValidationError('Enter a valid Pack Date in DD/MM/YYYY format', '#itemPurchaseDate');
                return false;
            }
            return true;
        }

        function isValidIsoDate(value) {
            const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
            if (!match) return false;
            const date = new Date(Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3])));
            return date.getUTCFullYear() === Number(match[1]) &&
                date.getUTCMonth() === Number(match[2]) - 1 &&
                date.getUTCDate() === Number(match[3]);
        }

        function renderItemsTable() {
            let html = '',
                totalAmt = 0,
                totalTaxable = 0,
                totalDiscount = 0,
                totalGST = 0;
            let hasItemDiscount = false,
                hasGST = false;

            itemsArray.forEach((item, index) => {
                totalAmt += parseFloat(item.total_amount) || 0;
                totalTaxable += parseFloat(item.taxable_amount) || 0;
                totalDiscount += parseFloat(item.discount_amount) || 0;
                totalGST += parseFloat(item.total_gst) || 0;
                if (parseFloat(item.discount_percent) > 0) hasItemDiscount = true;
                if (parseFloat(item.cgst_rate) > 0 || parseFloat(item.sgst_rate) > 0) hasGST = true;

                html += `<tr>
                <td>${index + 1}</td>
                <td style="white-space:nowrap; min-width:100px;">${item.item_name}</td>
                <td>${item.quantity}</td><td>${item.unit_name}</td><td>${item.rate}</td>
                <td>${item.total_amount}</td><td>${item.discount_percent}</td>
                <td>${item.discount_amount}</td><td>${item.taxable_amount}</td>
                <td class="gst-col">${item.cgst_rate}</td><td class="gst-col">${item.cgst_amount}</td>
                <td class="gst-col">${item.sgst_rate}</td><td class="gst-col">${item.sgst_amount}</td>
                <td class="gst-col">${item.total_gst}</td><td>${item.net_amount}</td><td>${item.sale_mrp}</td>
                <td style="white-space:nowrap; min-width:130px;">
                    <button class="btn btn-primary btn-sm editItem me-1" data-index="${index}" ${isEditingItem ? 'disabled' : ''}>Edit</button>
                    <button class="btn btn-danger btn-sm removeItem" data-index="${index}" ${isEditingItem ? 'disabled' : ''}>Delete</button>
                </td>
            </tr>`;
            });

            $('#itemsTableBody').html(html);
            $('.gst-col').toggle($('input[name="gstMode"]:checked').val() === '1');
            $('#summaryTotalAmount').val(totalAmt.toFixed(2));
            $('#totalGSTAmount').val(totalGST.toFixed(2));

            if (hasItemDiscount || hasGST) {
                $('#summaryDiscPercent').val('').prop('readonly', true).addClass('calc-label');
                $('#totalDiscountAmount').val(totalDiscount.toFixed(2));
                $('#totalTaxableAmount').val(totalTaxable.toFixed(2));
                recalcSummaryTotals(totalTaxable, totalGST);
            } else {
                const currentDiscPct = parseFloat($('#summaryDiscPercent').val()) || 0;
                const discAmt = totalAmt * (currentDiscPct / 100);
                $('#summaryDiscPercent').prop('readonly', false).removeClass('calc-label');
                $('#totalDiscountAmount').val(discAmt.toFixed(2));
                $('#totalTaxableAmount').val((totalAmt - discAmt).toFixed(2));
                recalcSummaryTotals(totalAmt - discAmt, totalGST);
            }
        }

        function recalcSummaryTotals(taxableAmt, totalGST) {
            const freight = parseFloat($('#freightAmt').val()) || 0;
            const netBeforeRound = parseFloat((taxableAmt + totalGST + freight).toFixed(2));
            const rounded = Math.round(netBeforeRound);
            const roundOff = parseFloat((rounded - netBeforeRound).toFixed(2));
            $('#roundOff').val(roundOff.toFixed(2));
            $('#finalNetAmount').val(rounded.toFixed(2));
        }

        function clearItemCalc() {
            $('#quantity, #rate, #discountPercent, #saleMrp').val('');
            $('#totalAmount, #discountAmount, #taxableAmount').val('');
            $('#cgstAmount, #sgstAmount, #totalGst, #netAmount').val('');
        }

        function clearItemSelection() {
            $('#selectedItemId, #selectedHsnCode, #unitId').val('');
            $('#itemSelected, #unitDisplay').val('');
            $('#cgstRate, #sgstRate').val('');
            $('#originalCgst, #originalSgst').val('');
            clearItemCalc();
        }

        function resetForm() {
            currentPurchaseId = 0;
            currentVouchId = 0; 
            isEditingItem = false;
            itemsArray = [];
            allItemsData = [];
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#purchaseNo, #refVoucherNo, #instrumentNo').val('');
            $('#purchaseDate').val('{{ date('Y-m-d') }}');
            $('#partyId, #bankAccountId').val('').trigger('change');
            $('#productCodeInput').val('');
            $('#bankSelectDiv, #instrumentNoDiv').hide();
            $('#transCash').prop('checked', true);
            $('#itemsTableBody').html('');
            $('#summaryTotalAmount, #totalTaxableAmount, #totalDiscountAmount, #totalGSTAmount, #freightAmt, #roundOff, #finalNetAmount')
                .val('');
            $('#summaryDiscPercent').val('').prop('readonly', false).removeClass('calc-label');
            $('#savePurchase').prop('disabled', false).text('Save');
            $('#addItemBtn').text('+ Add Item').data('editing', false);
            $('#itemPurchaseDateDiv').hide();
            $('#itemPurchaseDate').val('');
            $('#withoutGst').prop('checked', true);
            $('.gst-col').hide();
            clearItemSelection();
        }
    </script>
@endpush
