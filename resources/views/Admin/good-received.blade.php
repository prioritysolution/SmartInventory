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

        @media (min-width: 1200px) {
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
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid ">

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

            {{-- Top Row: Purchase Info (left) + Item Section (right) --}}
            <div class="row mb-3">
                {{-- LEFT BOX: Purchase Info --}}
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <h6>Purchase Info</h6>
                        <div class="mb-3">
                            <label class="form-label">Purchase Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="purchaseDate" min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Party<span class="text-danger">*</span></label>
                            <select class="form-select" id="partyId">
                                <option value="">-- Select Party --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->Party_Id }}">{{ $supplier->Party_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purchase No</label>
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
                                <label class="form-label">Select Bank</label>
                                <select class="form-select" id="bankAccountId">
                                    <option value="">-- Select Bank --</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3" id="bankRemarksDiv" style="display:none;">
                                <label class="form-label">Bank Remarks</label>
                                <input type="text" class="form-control" id="bankRemarks" maxlength="100"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT BOX: Item Section --}}
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <h6>Item Section</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Select Category</label>
                                <select class="form-select" id="categoryId">
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->Prd_CateId }}" data-fmcg="{{ $cat->Is_Fmcg }}">
                                            {{ $cat->Prd_CateNm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Item Selected</label>
                                <input type="text" class="form-control calc-label" id="itemSelected" readonly
                                    placeholder="Select category to pick an item">
                                <input type="hidden" id="selectedItemId">
                                <input type="hidden" id="selectedHsnCode">
                                <input type="hidden" id="unitId">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control calc-label" id="unitDisplay" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Qty<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="1" min="1"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Rate<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rate" step="0.01" min="0"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Total Amt</label>
                                <input type="text" class="form-control calc-label" id="totalAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Disc %</label>
                                <input type="number" class="form-control" id="discountPercent" step="0.01"
                                    max="100" min="0" autocomplete="off">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Disc Amt</label>
                                <input type="text" class="form-control calc-label" id="discountAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Taxable Amt</label>
                                <input type="text" class="form-control calc-label" id="taxableAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">CGST Rate</label>
                                <input type="text" class="form-control calc-label" id="cgstRate" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">CGST Amt</label>
                                <input type="text" class="form-control calc-label" id="cgstAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">SGST Rate</label>
                                <input type="text" class="form-control calc-label" id="sgstRate" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">SGST Amt</label>
                                <input type="text" class="form-control calc-label" id="sgstAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Total GST</label>
                                <input type="text" class="form-control calc-label" id="totalGst" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Net Amt</label>
                                <input type="text" class="form-control calc-label" id="netAmount" readonly>
                            </div>
                        </div>
                        <div class="row align-items-end mt-1">
                            <div class="col-md-3 mb-0">
                                <label class="form-label">Sale MRP</label>
                                <input type="number" step="0.01" class="form-control" id="saleMrp" min="0"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-0" id="itemPurchaseDateDiv" style="display:none;">
                                <label class="form-label">Pack Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="itemPurchaseDate"
                                    max="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-3 mb-0">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-success w-100 d-block" id="addItemBtn">+ Add
                                    Item</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Row: Items Table (left) + Summary (right) --}}
            <div class="row mb-3">

                {{-- LEFT BOX: Items Table + Transaction Mode --}}
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
                                        <th>CGST Rate</th>
                                        <th>CGST Amt</th>
                                        <th>SGST Rate</th>
                                        <th>SGST Amt</th>
                                        <th>Total GST</th>
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
                        <h6 class="ms-3"> Purchase Summary</h6>
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
                                <th>Round Off (+/-) (E)</th>
                                <td><input type="text" class="form-control calc-label" id="roundOff" readonly></td>
                            </tr>
                            <tr>
                                <th><strong>Net Amount (C+D+E)</strong></th>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemPickerTitle">Select Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Filter by Sub Category</label>
                            <select class="form-select" id="modalSubCatId">
                                <option value="0">-- All Sub Categories --</option>
                            </select>
                        </div>
                    </div>
                    <div id="itemPickerLoader" class="text-center py-3">
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
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="searchFromDate"
                                max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="searchToDate"
                                max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Select Party</label>
                            <select class="form-select" id="searchPartyId">
                                <option value="0">-- All Parties --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->Party_Id }}">{{ $supplier->Party_Name }}</option>
                                @endforeach
                            </select>
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
                                    <th>Invoice Date</th>
                                    <th>Net Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseSearchBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Use filters above to search</td>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemsArray = [];
        let itemPickerDT = null;
        let allItemsData = [];
        let currentPurchaseId = 0;
        let skipCategoryModal = false;

        $(document).ready(function() {

            $('#partyId').select2({
                placeholder: '-- Select Party --',
                allowClear: true
            });

            $('#categoryId').select2({
                placeholder: '-- Select Category --',
                allowClear: true
            });

            $('#bankAccountId').select2({
                placeholder: '-- Select Bank --',
                allowClear: true
            });

            // Modal select — dropdownParent is critical
            $('#modalSubCatId').select2({
                placeholder: '-- All Sub Categories --',
                allowClear: true,
                dropdownParent: $('#itemPickerModal')
            });

            $('#searchPartyId').select2({
                placeholder: '-- All Parties --',
                allowClear: true,
                dropdownParent: $('#purchaseSearchModal')
            });


            let currentCatIsFmcg = false;
            $('#categoryId').on('change', function() {
                const catId = $(this).val();
                const catName = $(this).find('option:selected').text();
                const isFmcg = parseInt($(this).find('option:selected').data('fmcg')) || 0;
                currentCatIsFmcg = (isFmcg == 1);

                if (currentCatIsFmcg) {
                    $('#itemPurchaseDateDiv').show();
                } else {
                    $('#itemPurchaseDateDiv').hide();
                    $('#itemPurchaseDate').val('');
                }
                if (!catId) {
                    clearItemSelection();
                    return;
                }
                if (skipCategoryModal) { 
                    skipCategoryModal = false;
                    return;
                }
                $('#itemPickerTitle').text('Select Item — ' + catName);
                $('#modalSubCatId').html('<option value="0">-- All Sub Categories --</option>').trigger(
                    'change');
                $('#itemPickerLoader').show();
                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');
                allItemsData = [];
                $('#itemPickerModal').modal('show');

                $.get("{{ url('good-received/subcategories') }}/" + catId, function(subs) {
                    $.each(subs, function(i, sub) {
                        $('#modalSubCatId').append(
                            `<option value="${sub.Prd_SubCateId}">${sub.Prd_SubCateNm}</option>`
                        );
                    });
                });

                $.get("{{ url('good-received/items') }}/" + catId + '/0', function(data) {
                    allItemsData = data;
                    console.log(data)
                    renderItemPickerTable(data);
                }).fail(function() {
                    $('#itemPickerModal').modal('hide');
                    $('#categoryId').val('');
                    Swal.fire('Error', 'Failed to load items', 'error');
                });
            });

            $('#modalSubCatId').on('change', function() {
                const catId = $('#categoryId').val();
                const subCatId = $(this).val();

                $('#itemPickerLoader').show();
                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');

                $.get("{{ url('good-received/items') }}/" + catId + '/' + subCatId, function(data) {
                    allItemsData = data;
                    renderItemPickerTable(data);
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load items', 'error');
                    $('#itemPickerLoader').hide();
                });
            });

            $(document).on('click', '#itemPickerTable tbody tr', function() {
                const cgst = parseFloat($(this).data('cgst')) || 0;
                const sgst = parseFloat($(this).data('sgst')) || 0;

                $('#selectedItemId').val($(this).data('id'));
                $('#selectedHsnCode').val($(this).data('hsn') || '');
                $('#itemSelected').val($(this).data('name'));
                $('#unitId').val($(this).data('unit'));
                $('#unitDisplay').val($(this).data('unitname'));

                $('#itemPickerModal').modal('hide');
                clearItemCalc();
                $('#cgstRate').val(cgst);
                $('#sgstRate').val(sgst);
                $('#quantity').focus();
            });

            $('#itemPickerModal').on('hidden.bs.modal', function() {
                if (!$('#selectedItemId').val()) {
                    $('#categoryId').val('').trigger('change');
                }
            });

            $('#quantity, #rate, #discountPercent').on('input', calculateAmounts);

            // 1. Replace summaryDiscPercent input handler
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




            $('input[name="transMode"]').on('change', function() {
                if ($(this).val() === '2') {
                    $('#bankSelectDiv, #bankRemarksDiv').show();
                } else {
                    $('#bankSelectDiv, #bankRemarksDiv').hide();
                    $('#bankAccountId').val('').trigger('change');
                    $('#bankRemarks').val('');
                }
            });

            $('#addItemBtn').on('click', function() {
                if (!validateItemForm()) return;

                itemsArray.push({
                    item_id: $('#selectedItemId').val(),
                    hsn_code: $('#selectedHsnCode').val() || '',
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

                renderItemsTable();
                clearItemSelection();
                $('#categoryId').val('').trigger('change');
                $('#addItemBtn').text('+ Add Item').data('editing', false);
            });

            $(document).on('click', '.removeItem', function() {
                itemsArray.splice($(this).data('index'), 1);
                renderItemsTable();
            });
            $(document).on('click', '.editItem', function() {
                const index = $(this).data('index');
                const item = itemsArray[index];

                skipCategoryModal = true; // ← add this
                $('#categoryId').val(item.category_id).trigger(
                    'change'); // ← use trigger('change') not change.select2

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

                itemsArray.splice(index, 1);
                renderItemsTable();
                $('#addItemBtn').text('Update Item').data('editing', true);
            });

            $('#savePurchase').on('click', function() {
                if (!$('#purchaseDate').val()) {
                    Swal.fire('Error', 'Purchase Date is required', 'error');
                    return;
                }
                if (!$('#partyId').val()) {
                    Swal.fire('Error', 'Please select a Party', 'error');
                    return;
                }

                if (itemsArray.length === 0) {
                    Swal.fire('Error', 'Please add at least one item', 'error');
                    return;
                }
                if ($('input[name="transMode"]:checked').val() === '2' && !$('#bankAccountId').val()) {
                    Swal.fire('Error', 'Please select a Bank', 'error');
                    return;
                }

                $(this).prop('disabled', true).text(currentPurchaseId > 0 ? 'Updating...' : 'Saving...');

                $.ajax({
                    url: "{{ route('good-received.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        purchase_id: currentPurchaseId,
                        purchase_date: $('#purchaseDate').val(),
                        purchase_no: $('#purchaseNo').val(),
                        party_id: $('#partyId').val(),
                        trans_mode: $('input[name="transMode"]:checked').val(),
                        ref_vouch_no: $('#refVoucherNo').val(),
                        bank_id: $('#bankAccountId').val(),
                        bank_remarks: $('#bankRemarks').val(),
                        disc_percent: $('#summaryDiscPercent').prop('readonly') ? 0 : (parseFloat($(
                            '#summaryDiscPercent').val()) || 0),
                        disc_amt: $('#totalDiscountAmount').val() || 0,
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

                    // Fill header
                    $('#purchaseDate').val(data.Invoice_Date ? data.Invoice_Date.substring(0, 10) :
                        '');
                    $('#partyId').val(data.Party_Id).trigger('change');
                    $('#purchaseNo').val(data.Ref_No);


                    // Fill items
                    itemsArray = data.Item_Details.map(item => ({
                        item_id: item.Prod_Id,
                        category_id: item.Cat_Id, // ← was ''
                        category_name: '',
                        hsn_code: item.hsn_code ?? '',
                        item_name: item.Prod_Name,
                        quantity: item.qnty,
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

                    renderItemsTable();
                    $('#savePurchase').text('Update');

                    // Fill summary after render
                    $('#summaryDiscPercent').val(data.Disc_Percent);
                    $('#totalDiscountAmount').val(data.Disc_Amount);
                    $('#totalGSTAmount').val(data.GST_Amt);
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

                if (!fromDate || !toDate) {
                    Swal.fire('Error', 'Please select From Date and To Date', 'error');
                    return;
                }
                if (!partyId || partyId == '0') {
                    Swal.fire('Error', 'Please select a Party', 'error');
                    return;
                }

                if (purchaseSearchDT) {
                    purchaseSearchDT.destroy();
                    purchaseSearchDT = null;
                }
                $('#purchaseSearchBody').html(
                    '<tr><td colspan="6" class="text-center">Loading...</td></tr>');

                $.get("{{ url('good-received/search') }}", {
                    from_date: fromDate,
                    to_date: toDate,
                    party_id: partyId
                }, function(data) {
                    let html = '';
                    if (!data.length) {
                        html =
                            '<tr><td colspan="6" class="text-center text-muted">No records found</td></tr>';
                        $('#purchaseSearchBody').html(html);
                        return;
                    }
                    data.forEach((row, i) => {
                        html += `<tr>
                <td>${i + 1}</td>
                <td>${row.Invoice_No}</td>
                <td>${row.Ref_No ?? ''}</td>
                <td>${row.Invoice_Date}</td>
                <td>${row.Net_Amt}</td>
                <td><button class="btn btn-sm btn-primary viewPurchase" data-id="${row.Pur_Id}">Edit</button></td>
            </tr>`;
                    });
                    $('#purchaseSearchBody').html(html);
                    purchaseSearchDT = $('#purchaseSearchTable').DataTable({
                        pageLength: 10,
                        ordering: true,
                        sDom: 'fBtlpi',
                        language: {
                            search: '',
                            searchPlaceholder: 'Search...',
                            sLengthMenu: 'Row Per Page _MENU_ Entries',
                            info: '_START_ - _END_ of _TOTAL_ items',
                            paginate: {
                                next: '<i class="isax isax-arrow-right-1"></i>',
                                previous: '<i class="isax isax-arrow-left"></i>'
                            }
                        }
                    });
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load data', 'error');
                    $('#purchaseSearchBody').html(
                        '<tr><td colspan="6" class="text-center text-danger">Failed to load</td></tr>'
                    );
                });
            });

            $('#purchaseSearchModal').on('hidden.bs.modal', function() {
                if (purchaseSearchDT) {
                    purchaseSearchDT.destroy();
                    purchaseSearchDT = null;
                }
                $('#purchaseSearchBody').html(
                    '<tr><td colspan="6" class="text-center text-muted">Use filters above to search</td></tr>'
                );
                $('#searchFromDate, #searchToDate').val('');
                $('#searchPartyId').val('0').trigger('change');
            });

        });

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
                    const cgst = item.GST_Data ? JSON.parse(item.GST_Data).CGST : 0;
                    const sgst = item.GST_Data ? JSON.parse(item.GST_Data).SGST : 0;
                    $('#itemPickerTable tbody').append(
                        `<tr data-id="${item.Prod_Id}"
                 data-code="${item.Prod_Code}"
                 data-name="${item.Prod_ShortNm}"
                 data-unit="${item.Unit_Id}"
                 data-unitname="${item.Unit_Name}"
                 data-hsn="${item.HSN_Code ?? ''}"
                 data-cgst="${cgst}"
                 data-sgst="${sgst}">
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

        function calculateAmounts() {
            const qty = parseFloat($('#quantity').val()) || 0;
            const rate = parseFloat($('#rate').val()) || 0;
            const discPct = parseFloat($('#discountPercent').val()) || 0;
            const cgstRate = parseFloat($('#cgstRate').val()) || 0;
            const sgstRate = parseFloat($('#sgstRate').val()) || 0;

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

        function validateItemForm() {
            if (!$('#purchaseDate').val()) {
                Swal.fire('Error', 'Purchase Date is required before adding items', 'error');
                return false;
            }

            const purchaseDate = new Date($('#purchaseDate').val());
            const today = new Date();
            const yearStart = new Date('{{ session('year_start') }}');
            const yearEnd = new Date('{{ session('year_end') }}');

            if (purchaseDate > today) {
                Swal.fire('Error', 'Purchase Date cannot be After Today', 'error');
                return false;
            }

            if (purchaseDate < yearStart || purchaseDate > yearEnd) {
                Swal.fire('Error',
                    'Purchase Date must be between {{ session('year_start') }} and {{ session('year_end') }}', 'error'
                );
                return false;
            }

            if (!$('#selectedItemId').val()) {
                Swal.fire('Error', 'Please select an item', 'error');
                return false;
            }

            const qty = parseFloat($('#quantity').val());
            const rate = parseFloat($('#rate').val());
            const discPct = parseFloat($('#discountPercent').val()) || 0;
            const totalAmt = parseFloat($('#totalAmount').val()) || 0;

            if (!$('#quantity').val() || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                return false;
            }
            if (qty > 99999999.99) {
                Swal.fire('Error', 'Quantity cannot exceed 99999999.99', 'error');
                return false;
            }
            if (!$('#rate').val()) {
                Swal.fire('Error', 'Rate is required', 'error');
                return false;
            }
            if (discPct > 100) {
                Swal.fire('Error', 'Discount % cannot be greater than 100', 'error');
                return false;
            }

            if (rate > 99999999.99) {
                Swal.fire('Error', 'Rate cannot exceed 99999999.99', 'error');
                return false;
            }
            const saleMrp = parseFloat($('#saleMrp').val());
            if (saleMrp > 0 && saleMrp < rate) {
                Swal.fire('Error', `Sale MRP (${saleMrp}) cannot be less than Rate (${rate})`, 'error');
                $('#saleMrp').focus();
                return false;
            }

            if (totalAmt > 99999999.99) {
                Swal.fire('Error', 'Total Amount (Qty × Rate) cannot exceed 99999999.99', 'error');
                return false;
            }
            if ($('#itemPurchaseDateDiv').is(':visible') && !$('#itemPurchaseDate').val()) {
                Swal.fire('Error', 'Pack Date is required for this category', 'error');
                return false;
            }

            return true;

        }



        // 2. Replace renderItemsTable function
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
            <td>${index + 1}</td><td style="white-space:nowrap; min-width:100px;">${item.item_name}</td><td>${item.quantity}</td>
            <td>${item.unit_name}</td><td>${item.rate}</td><td>${item.total_amount}</td>
            <td>${item.discount_percent}</td><td>${item.discount_amount}</td><td>${item.taxable_amount}</td>
            <td>${item.cgst_rate}</td><td>${item.cgst_amount}</td>
            <td>${item.sgst_rate}</td><td>${item.sgst_amount}</td>
            <td>${item.total_gst}</td><td>${item.net_amount}</td>
            <td>${item.sale_mrp}</td>
            <td style="white-space:nowrap; min-width:130px;">
              <button class="btn btn-primary btn-sm editItem me-1" data-index="${index}">Edit</button>
              <button class="btn btn-danger btn-sm removeItem" data-index="${index}">Delete</button>
            </td>
        </tr>`;
            });

            $('#itemsTableBody').html(html);
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
            // Net = Taxable Amount + Total GST + Round Off
            const netBeforeRound = parseFloat((taxableAmt + totalGST).toFixed(2));
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
            clearItemCalc();
        }

        function resetForm() {
            currentPurchaseId = 0;
            currentCatIsFmcg = false;
            itemsArray = [];
            allItemsData = [];
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#purchaseDate, #purchaseNo, #refVoucherNo, #bankRemarks').val('');
            $('#partyId, #bankAccountId, #categoryId').val('').trigger('change');
            $('#bankSelectDiv, #bankRemarksDiv').hide();
            $('#transCash').prop('checked', true);
            $('#itemsTableBody').html('');
            $('#summaryTotalAmount, #totalTaxableAmount, #totalDiscountAmount, #totalGSTAmount, #roundOff, #finalNetAmount')
                .val('');
            $('#summaryDiscPercent').val('').prop('readonly', false).removeClass('calc-label');
            $('#savePurchase').prop('disabled', false).text('Save');
            $('#addItemBtn').text('+ Add Item').data('editing', false);
            $('#itemPurchaseDateDiv').hide();
            $('#itemPurchaseDate').val('');

            clearItemSelection();
        }
    </script>
@endpush
