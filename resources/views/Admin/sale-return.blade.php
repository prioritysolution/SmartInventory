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

        .calc-label {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        #barcodeInput:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
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
        @media (min-width: 1200px) {
    .modal-xl-custom {
        max-width: 1400px;
    }
}

    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

           <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
    <h6 class="mb-0">Counter Sale Return</h6>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="searchSaleReturnBtn" style="width: 100px;">
        <svg aria-hidden="true" class="me-2" width="18" height="18" viewBox="0 0 18 18">
            <path d="m18 16.5-5.14-5.18h-.35a7 7 0 1 0-1.19 1.19v.35L16.5 18zM12 7A5 5 0 1 1 2 7a5 5 0 0 1 10 0"></path>
        </svg> Search
    </button>
</div>


            <div class="row mb-3 top-entry-row">

                {{-- LEFT BOX: Sale Info --}}
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Sale Info</h6>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sale Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="saleDate" min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-1" id="customerLabel">Select Customer<span
                                        class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline mb-1">
                                        <input class="form-check-input" type="radio" name="saleType" id="saleInternal"
                                            value="internal" checked>
                                        <label class="form-check-label" for="saleInternal">Internal</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-1 me-0">
                                        <input class="form-check-input" type="radio" name="saleType" id="saleOutside"
                                            value="outside">
                                        <label class="form-check-label" for="saleOutside">Outside</label>
                                    </div>
                                </div>
                            </div>
                            <div id="customerDiv">
                                <select class="form-select" id="partyId">
                                    <option value="">-- Select Customer --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->Party_Id }}">
                                            {{ $customer->Party_Name }}-{{ $customer->Party_Code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="cashDiv" style="display:none;">
                                <input type="text" class="form-control calc-label" value="Cash">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sale No</label>
                            <input type="text" class="form-control" id="saleNo" maxlength="20" autocomplete="off">
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Item Section</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="findSaleBtn">
                                <i class="fa-solid fa-file-invoice me-1"></i> Find Sale
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="isax isax-scan"></i> Scan Barcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="barcodeInput"
                                        placeholder="Scan or type barcode & press Enter" autocomplete="off">
                                    <button class="btn btn-primary" type="button" id="productSearchBtn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Item Selected</label>
                                <input type="text" class="form-control calc-label" id="itemSelected" readonly
                                    placeholder="Scan barcode to select item">
                                <input type="hidden" id="selectedItemId">
                                <input type="hidden" id="selectedHsnCode">
                                <input type="hidden" id="unitId">
                                <input type="hidden" id="saleMrp">
                                <input type="hidden" id="maxReturnQty">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control calc-label" id="unitDisplay" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Qty<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="0.01" min="0.01"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rate<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rate" step="0.01" min="0"
                                    autocomplete="off" readonly>
                                <select class="form-select" id="rateSelect" style="display:none;"></select>
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
                                    max="100" min="0" autocomplete="off" readonly>
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
                            <div class="col-md-4 mb-3 ms-md-auto">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-success w-100 d-block" id="addItemBtn">+ Add
                                    Item</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Row: Items Table + Transaction Mode (left) + Summary (right) --}}
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
                                        <th class="gst-col">CGST Rate</th>
                                        <th class="gst-col">CGST Amt</th>
                                        <th class="gst-col">SGST Rate</th>
                                        <th class="gst-col">SGST Amt</th>
                                        <th class="gst-col">Total GST</th>
                                        <th>Net Amt</th>
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
                        <h6 class="ms-3">Sale Summary</h6>
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
                            <button type="button" class="btn btn-primary" id="saveSale">Save</button>
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

    {{-- Sale Return Search Modal --}}
<div class="modal fade" id="saleReturnSearchModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-xl-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Counter Sale Return</h5>
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
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="searchSaleReturnGo">Search</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="saleReturnSearchTable" class="table table-bordered table-sm w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                                <th>Net Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="saleReturnSearchBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Use filters above to search</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Find Sale (by customer / date) --}}
    <div class="modal fade" id="findSaleModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Find Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="findFromDate"
                                min="{{ session('year_start') }}" max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="findToDate"
                                min="{{ session('year_start') }}" max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Customer (optional)</label>
                            <select class="form-select" id="findPartyId">
                                <option value="0">-- All / Outside --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->Party_Id }}">
                                        {{ $customer->Party_Name }}-{{ $customer->Party_Code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="findSaleGo">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="findSaleTable" class="table table-bordered table-sm w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Invoice No</th>
                                    <th>Ref No</th>
                                    <th>Invoice Date</th>
                                    <th>Customer</th>
                                    <th>Net Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="findSaleBody">
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

    {{-- Sale product details --}}
    <div class="modal fade" id="saleItemsModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleItemsTitle">Sale Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="saleItemsTable" class="table table-bordered table-sm w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Item Name</th>
                                    <th>Sold</th>
                                    <th>Returned</th>
                                    <th>Remaining</th>
                                    <th>Unit</th>
                                    <th>Rate</th>
                                    <th>Disc %</th>
                                    <th>Taxable</th>
                                    <th>GST</th>
                                    <th>Net Amt</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="saleItemsBody">
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No items</td>
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
        const SESSION_YEAR_START = "{{ session('year_start') }}";
        const SESSION_YEAR_END = "{{ session('year_end') }}";
        const GST_TYPE = {{ session('gst_type', 1) }};
        let itemsArray = [];
        let currentSaleId = 0;
        let saleReturnSearchDT = null;
        let itemPickerDT = null;
        let allItemsData = [];
        let findSaleDT = null;
        let saleItemsDT = null;
        let skipFindSaleReset = false;
        let reopenFindSale = true;
        let findSaleHeader = null;
        let findSaleItems = [];

        $(document).ready(function() {

            $('#partyId').select2({
                placeholder: '-- Select Customer --',
                allowClear: true
            });

            $('input[name="saleType"]').on('change', function() {
                if ($(this).val() === 'outside') {
                    $('#customerDiv').hide();
                    $('#cashDiv').show();
                    $('#partyId').val('').trigger('change');
                } else {
                    $('#customerDiv').show();
                    $('#cashDiv').hide();
                    $('#partyId').val('').trigger('change');
                }
            });
            $('#bankAccountId').select2({
                placeholder: '-- Select Bank --',
                allowClear: true
            });
            $('#findPartyId').select2({
                placeholder: '-- All / Outside --',
                allowClear: true,
                dropdownParent: $('#findSaleModal')
            });
            if (GST_TYPE == 2) {
                $('#cgstRate, #cgstAmount, #sgstRate, #sgstAmount, #totalGst').closest('.gst-col').hide();
                $('th:contains("CGST"), th:contains("SGST"), th:contains("Total GST")').hide();
                $('#totalGSTAmount').closest('tr').hide();
            }

            // Sale Return Search functionality
            $('#searchSaleReturnBtn').on('click', function() {
                $('#saleReturnSearchModal').modal('show');
            });

            $('#searchSaleReturnGo').on('click', function() {
                const fromDate = $('#searchFromDate').val();
                const toDate = $('#searchToDate').val();

                if (!fromDate || !toDate) {
                    Swal.fire('Error', 'Please select From Date and To Date', 'error');
                    return;
                }

                if (saleReturnSearchDT) {
                    saleReturnSearchDT.destroy();
                    saleReturnSearchDT = null;
                }
                $('#saleReturnSearchBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

                $.get("{{ url('sale-return/search') }}", {
                    from_date: fromDate,
                    to_date: toDate
                }, function(data) {
                    let html = '';
                    if (!data.length) {
                        html = '<tr><td colspan="5" class="text-center text-muted">No records found</td></tr>';
                        $('#saleReturnSearchBody').html(html);
                        return;
                    }
                    data.forEach((row, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${row.Invoice_No}</td>
                            <td>${siDate.toDisplay(row.Invoice_Date)}</td>
                            <td>${row.Net_Amt}</td>
                            <td><button class="btn btn-sm btn-primary viewSaleReturn" data-id="${row.Sale_Id}">Edit</button></td>
                        </tr>`;
                    });
                    $('#saleReturnSearchBody').html(html);
                    saleReturnSearchDT = $('#saleReturnSearchTable').DataTable({
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
                    $('#searchFromDate, #searchToDate').val('');
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load data', 'error');
                    $('#saleReturnSearchBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>');
                });
            });

            // View Sale Return Details
            $(document).on('click', '.viewSaleReturn', function() {
                const saleReturnId = $(this).data('id');
                currentSaleId = saleReturnId;

                $.get("{{ url('sale-return/details') }}/" + saleReturnId, function(data) {
                    $('#saleReturnSearchModal').modal('hide');

                    // Fill header
                    $('#saleDate').val(data.Invoice_Date ? data.Invoice_Date.substring(0, 10) : '');
                    $('#partyId').val(data.Party_Id).trigger('change');
                    $('#saleNo').val(data.Ref_No);

                    const mode = String(data.Trans_Mode || '1');
                    $('input[name="transMode"][value="' + mode + '"]').prop('checked', true).trigger('change');
                    if (mode === '2') {
                        $('#bankAccountId').val(data.Bank_Ledg || '').trigger('change');
                        $('#instrumentNo').val(data.Instrument_No || '');
                    }

                    // Fill items
                    itemsArray = data.Item_Details.map(item => ({
                        item_id: item.Prod_Id,
                        category_id: item.Cat_Id,
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
                        total_gst: (parseFloat(item.SGST_Amt) + parseFloat(item.CGST_Amt)).toFixed(2),
                        net_amount: item.Net_Amt,
                        sale_mrp: item.MRP,
                        item_sale_date: item.Pack_Date
                    }));

                    renderItemsTable();
                    $('#saveSale').text('Update');

                    // Fill summary after render
                    $('#summaryDiscPercent').val(data.Disc_Percent);
                    $('#totalDiscountAmount').val(data.Disc_Amount);
                    $('#totalTaxableAmount').val(data.Taxable_Amt);
                    $('#totalGSTAmount').val(data.GST_Amt);
                    $('#roundOff').val(data.Round_Off);
                    $('#finalNetAmount').val(data.Net_Amt);

                    // Disable sale date when editing
                    $('#saleDate').prop('disabled', true);

                }).fail(function() {
                    Swal.fire('Error', 'Failed to load sale return details', 'error');
                });
            });

            $('#saleReturnSearchModal').on('hidden.bs.modal', function() {
                if (saleReturnSearchDT) {
                    saleReturnSearchDT.destroy();
                    saleReturnSearchDT = null;
                }
                $('#saleReturnSearchBody').html('<tr><td colspan="5" class="text-center text-muted">Use filters above to search</td></tr>');
                $('#searchFromDate, #searchToDate').val('');
            });

            $('#findSaleBtn').on('click', function() {
                const party = $('#partyId').val();
                if (party) {
                    $('#findPartyId').val(party).trigger('change');
                }
                $('#findSaleModal').modal('show');
            });

            $('#findSaleGo').on('click', function() {
                const fromDate = $('#findFromDate').val();
                const toDate = $('#findToDate').val();
                const partyId = $('#findPartyId').val() || 0;
                if (!fromDate || !toDate) {
                    Swal.fire('Error', 'Please select From Date and To Date', 'error');
                    return;
                }
                if (findSaleDT) {
                    findSaleDT.destroy();
                    findSaleDT = null;
                }
                $('#findSaleBody').html(
                    '<tr><td colspan="7" class="text-center">Loading...</td></tr>');
                $.get("{{ url('sale-return/find-sale') }}", {
                    from_date: fromDate,
                    to_date: toDate,
                    party_id: partyId
                }, function(data) {
                    let html = '';
                    if (!data.length) {
                        $('#findSaleBody').html(
                            '<tr><td colspan="7" class="text-center text-muted">No records found</td></tr>'
                        );
                        return;
                    }
                    data.forEach((row, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${row.Invoice_No ?? ''}</td>
                            <td>${row.Ref_No ?? ''}</td>
                            <td>${siDate.toDisplay(row.Invoice_Date)}</td>
                            <td>${row.Party_Name ?? 'Outside / Cash'}</td>
                            <td>${row.Net_Amt ?? ''}</td>
                            <td><button type="button" class="btn btn-sm btn-primary viewSaleItems" data-id="${row.Sale_Id}">Details</button></td>
                        </tr>`;
                    });
                    $('#findSaleBody').html(html);
                    findSaleDT = $('#findSaleTable').DataTable({
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
                    Swal.fire('Error', 'Failed to load sales', 'error');
                    $('#findSaleBody').html(
                        '<tr><td colspan="7" class="text-center text-danger">Failed to load</td></tr>'
                    );
                });
            });

            $(document).on('click', '.viewSaleItems', function() {
                const saleId = $(this).data('id');
                reopenFindSale = true;
                $.get("{{ url('sale-return/find-sale-details') }}/" + saleId, function(data) {
                    findSaleHeader = {
                        Sale_Id: data.Sale_Id,
                        Party_Id: data.Party_Id,
                        Invoice_No: data.Invoice_No,
                        Ref_No: data.Ref_No,
                        Invoice_Date: data.Invoice_Date
                    };
                    findSaleItems = data.Item_Details || [];
                    if (typeof findSaleItems === 'string') {
                        findSaleItems = JSON.parse(findSaleItems || '[]');
                    }
                    if (!Array.isArray(findSaleItems)) {
                        findSaleItems = [];
                    }
                    $('#saleItemsTitle').text('Sale Items' + (data.Invoice_No ? ' - ' + data.Invoice_No : ''));
                    renderSaleItemsTable(findSaleItems);
                    skipFindSaleReset = true;
                    $('#findSaleModal').one('hidden.bs.modal', function() {
                        $('#saleItemsModal').modal('show');
                    });
                    $('#findSaleModal').modal('hide');
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load sale items', 'error');
                });
            });

            $(document).on('click', '.addReturnItem', function() {
                const idx = $(this).data('index');
                const item = findSaleItems[idx];
                if (!item) return;
                if (!populateReturnFromSale(item, findSaleHeader)) return;
                reopenFindSale = false;
                $('#saleItemsModal').modal('hide');
            });

            $('#findSaleModal').on('hidden.bs.modal', function() {
                if (skipFindSaleReset) {
                    skipFindSaleReset = false;
                    return;
                }
                if (findSaleDT) {
                    findSaleDT.destroy();
                    findSaleDT = null;
                }
                $('#findSaleBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Use filters above to search</td></tr>'
                );
                $('#findFromDate, #findToDate').val('');
                $('#findPartyId').val('0').trigger('change');
            });

            $('#saleItemsModal').on('hidden.bs.modal', function() {
                if (saleItemsDT) {
                    saleItemsDT.destroy();
                    saleItemsDT = null;
                }
                $('#saleItemsBody').html(
                    '<tr><td colspan="12" class="text-center text-muted">No items</td></tr>'
                );
                if (reopenFindSale) {
                    $('#findSaleModal').modal('show');
                }
                reopenFindSale = true;
            });
            // ── Barcode Scanner ──────────────────────────────────────────
            $('#barcodeInput').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupBarcode();
                }
            });


            function pickUniqueItem(data, code) {
                if (!data || !data.length) return null;
                const needle = String(code).toLowerCase();
                const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === needle);
                if (exact.length === 1) return exact[0];
                if (data.length === 1) return data[0];
                return null;
            }

            function loadItemByProdId(prodId) {
                if (!ensureSaleDate()) return;
                $.get("{{ route('sale-return.item-info') }}", {
                    prod_id: prodId,
                    sale_date: $('#saleDate').val()
                }).done(function(item) {
                    populateItemFields(item);
                    $('#barcodeInput').focus();
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
                });
            }

            function resolveItemsOrOpenPicker(data, code) {
                const unique = pickUniqueItem(data, code);
                if (unique) {
                    loadItemByProdId(unique.Prod_Id);
                    return;
                }
                openItemPickerModal(data || [], true, code);
            }

            function lookupBarcode() {
                const barcode = $('#barcodeInput').val().trim();
                const saleDate = $('#saleDate').val();

                if (!barcode) return;
                if (!ensureSaleDate()) return;

                $.get("{{ route('sale-return.barcode') }}", {
                        barcode: barcode,
                        sale_date: saleDate
                    })
                    .done(function(item) {
                        populateItemFields(item);
                        $('#barcodeInput').focus();
                    })
                    .fail(function() {
                        $.get("{{ route('sale-return.items') }}", {
                            code: barcode,
                            cat_id: 0,
                            sub_cat_id: 0
                        }, function(data) {
                            resolveItemsOrOpenPicker(data, barcode);
                        }).fail(function() {
                            openItemPickerModal([], true, barcode);
                        });
                    });
            }

            function ensureSaleDate() {
                const saleDate = $('#saleDate').val();
                if (!saleDate) {
                    Swal.fire('Error', 'Please select sale date first', 'error');
                    $('#saleDate').focus();
                    return false;
                }
                const saleDateObj = new Date(saleDate);
                const yearStartObj = new Date('{{ session("year_start") }}');
                const yearEndObj = new Date('{{ session("year_end") }}');
                const today = new Date();
                if (saleDateObj > today) {
                    Swal.fire('Error', 'Sale Date cannot be After Today', 'error');
                    $('#saleDate').focus();
                    return false;
                }
                if (saleDateObj < yearStartObj || saleDateObj > yearEndObj) {
                    Swal.fire('Error', 'Sale Date must be between {{ session("year_start") }} and {{ session("year_end") }}', 'error');
                    $('#saleDate').focus();
                    return false;
                }
                return true;
            }

            $('#productSearchBtn').on('click', function(e) {
                e.preventDefault();
                if (!ensureSaleDate()) return;
                const code = $('#barcodeInput').val().trim();
                if (!code) {
                    openItemPickerModal([], false, '');
                    return;
                }
                $.get("{{ route('sale-return.items') }}", {
                    code: code,
                    cat_id: 0,
                    sub_cat_id: 0
                }, function(data) {
                    resolveItemsOrOpenPicker(data, code);
                }).fail(function() {
                    openItemPickerModal([], true, code);
                });
            });

            $('#modalCateId').on('change', function() {
                const catId = parseInt($(this).val()) || 0;
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                if (!catId) return;
                $.get("{{ route('sale-return.subcats') }}", {
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
                $.get("{{ route('sale-return.items') }}", {
                    cat_id: catId,
                    sub_cat_id: subCatId,
                    code: code
                }, function(data) {
                    renderItemPickerTable(data, false);
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
                $('#modalFilterRow').show();
                $('#modalCateId').val('0');
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                $('#modalSearchInput').val(code || '');
                $('#itemPickerModal').modal('show');
                if (data.length > 0) {
                    $('#itemPickerLoader').show();
                    renderItemPickerTable(data, isCodeSearch);
                }
            }

            $(document).on('click', '#itemPickerTable tbody tr', function() {
                const $row = $(this);
                const prodId = $row.data('id');
                if (!prodId) return;
                if (!ensureSaleDate()) return;
                $.get("{{ route('sale-return.item-info') }}", {
                    prod_id: prodId,
                    sale_date: $('#saleDate').val()
                }).done(function(item) {
                    $('#itemPickerModal').modal('hide');
                    populateItemFields(item);
                    if ($row.data('hsn')) {
                        $('#selectedHsnCode').val($row.data('hsn'));
                    }
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
                });
            });


            $('#quantity').on('input', calculateAmounts);

            $('#rateSelect').on('change', function() {
                const selected = $(this).val();
                $('#rate').val(selected);
                $('#saleMrp').val(selected);
                calculateAmounts();
            });

            $('#summaryDiscPercent').on('input', function() {
                if ($(this).prop('readonly')) return;
                const discPct = parseFloat($(this).val()) || 0;
                if (discPct > 100) {
                    $(this).val(100);
                    Swal.fire('Error', 'Discount % cannot exceed 100', 'error');
                    return;
                }
                const totalTaxable = itemsArray.reduce((s, i) => s + (parseFloat(i.taxable_amount) || 0),
                    0);
                const totalGST = itemsArray.reduce((s, i) => s + (parseFloat(i.total_gst) || 0), 0);
                const discAmt = totalTaxable * (discPct / 100);
                $('#totalDiscountAmount').val(discAmt.toFixed(2));
                recalcSummaryTotals(totalTaxable, totalGST);
            });

            // ── Trans Mode ───────────────────────────────────────────────
            $('input[name="transMode"]').on('change', function() {
                if ($(this).val() === '2') {
                    $('#bankSelectDiv, #instrumentNoDiv').show();
                } else {
                    $('#bankSelectDiv, #instrumentNoDiv').hide();
                    $('#bankAccountId').val('').trigger('change');
                    $('#instrumentNo').val('');
                }
            });

            // ── Add Item ─────────────────────────────────────────────────
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
                    sale_mrp: $('#saleMrp').val() || $('#rate').val() || 0,
                    max_return_qty: $('#maxReturnQty').val() || '',
                });
                renderItemsTable();
                clearItemSelection();
                $('#saleDate').prop('disabled', true);
                $('#barcodeInput').val('').focus();
            });

            $(document).on('click', '.removeItem', function() {
                itemsArray.splice($(this).data('index'), 1);
                renderItemsTable();
            });

            $(document).on('click', '.editItem', function() {
    const index = $(this).data('index');
    const item = itemsArray[index];
    
    // Populate item fields with existing data
    $('#selectedItemId').val(item.item_id);
    $('#selectedHsnCode').val(item.hsn_code);
    $('#itemSelected').val(item.item_name);
    $('#unitId').val(item.unit_id);
    $('#unitDisplay').val(item.unit_name);
    $('#quantity').val(item.quantity);
    $('#rate').val(item.rate);
    $('#saleMrp').val(item.sale_mrp || item.rate);
    $('#discountPercent').val(item.discount_percent);
    $('#cgstRate').val(item.cgst_rate);
    $('#sgstRate').val(item.sgst_rate);
    $('#maxReturnQty').val(item.max_return_qty || '');
    loadSaleRatesForItem(item.item_id, item.rate);
    
    // Calculate amounts
    calculateAmounts();
    
    // Remove item from array (will be re-added when user clicks Add Item)
    itemsArray.splice(index, 1);
    renderItemsTable();
    
    // Focus on quantity for editing
    $('#quantity').focus().select();
});


            // ── Save ─────────────────────────────────────────────────────
            $('#saveSale').on('click', function() {
                if (!$('#saleDate').val()) {
                    Swal.fire('Error', 'Sale Date is required', 'error');
                    return;
                }
                const isOutside = $('input[name="saleType"]:checked').val() === 'outside';
                if (!isOutside && !$('#partyId').val()) {
                    Swal.fire('Error', 'Please select a Customer', 'error');
                    return;
                }
            
                if (itemsArray.length === 0) {
                    Swal.fire('Error', 'Please add at least one item', 'error');
                    return;
                }
                if ($('input[name="transMode"]:checked').val() === '2') {
                    if (!$('#bankAccountId').val()) {
                        Swal.fire('Error', 'Please select a Bank', 'error');
                        return;
                    }
                    if (!$('#instrumentNo').val().trim()) {
                        Swal.fire('Error', 'Instrument No is required for Bank', 'error');
                        return;
                    }
                }

                $(this).prop('disabled', true).text(currentSaleId > 0 ? 'Updating...' : 'Saving...');

                $.ajax({
                    url: "{{ route('sale-return.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                         sale_id: currentSaleId,
                        sale_date: $('#saleDate').val(),
                        sale_no: $('#saleNo').val(),
                        party_id: isOutside ? 0 : $('#partyId').val(),
                        trans_mode: $('input[name="transMode"]:checked').val(),
                        ref_vouch_no: $('#refVoucherNo').val(),
                        bank_id: $('#bankAccountId').val(),
                        instrument_no: $('#instrumentNo').val().trim(),
                        disc_percent: $('#summaryDiscPercent').prop('readonly') ? 0 : (parseFloat($(
                            '#summaryDiscPercent').val()) || 0),
                        disc_amt: $('#totalDiscountAmount').val() || 0,
                        round_off: $('#roundOff').val() || 0,
                        net_amt: $('#finalNetAmount').val() || 0,
                        items: itemsArray
                    },
                    success: function(res) {
            const msg = currentSaleId > 0 ? 'Sale Updated Successfully' : res.message;
            Swal.fire('Success', msg, 'success').then(() => resetForm());
        },
                    error: function(xhr) {
                         $('#saveSale').prop('disabled', false).text(currentSaleId > 0 ? 'Update' : 'Save');
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON
                            ?.message || 'Failed to save', 'error');
                    }
                });
            });

            $('#cancelBtn').on('click', resetForm);
        });

        // ── Helper Functions ──────────────────────────────────────────────

        function renderItemPickerTable(data, isCodeSearch) {
            setTimeout(function() {
                $('#itemPickerLoader').hide();
                if (!data.length) {
                    $('#itemPickerTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
                    $('#itemPickerTableWrap').show();
                    return;
                }
                $.each(data, function(i, item) {
                    const gst = item.GST_Data ? (typeof item.GST_Data === 'string' ? JSON.parse(item.GST_Data) : item.GST_Data) : {};
                    $('#itemPickerTable tbody').append(
                        `<tr data-id="${item.Prod_Id}"
                     data-name="${item.Prod_ShortNm}"
                     data-unit="${item.Unit_Id}"
                     data-unitname="${item.Unit_Name}"
                     data-hsn="${item.Gst_Id ?? 0}"
                     data-cgst="${gst.CGST ?? 0}"
                     data-sgst="${gst.SGST ?? 0}">
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

        function parseSaleRateValue(r) {
            if (r == null) return NaN;
            if (typeof r === 'object') return parseFloat(r.MRP ?? r.mrp ?? r.Rate ?? r.rate);
            return parseFloat(r);
        }

        function applySaleRateOptions(saleRates, currentMrp) {
            const rates = [];
            (Array.isArray(saleRates) ? saleRates : []).forEach(function(r) {
                const n = parseSaleRateValue(r);
                if (n > 0 && rates.indexOf(n.toFixed(2)) === -1) {
                    rates.push(n.toFixed(2));
                }
            });
            if (rates.length > 1) {
                $('#rate').hide();
                $('#rateSelect').empty().css('display', 'block');
                rates.forEach(function(r) {
                    $('#rateSelect').append('<option value="' + r + '">' + r + '</option>');
                });
                const current = (parseFloat(currentMrp) > 0 ? parseFloat(currentMrp).toFixed(2) : rates[0]);
                const selected = rates.indexOf(current) >= 0 ? current : rates[0];
                $('#rateSelect').val(selected);
                $('#rate').val(selected);
                $('#saleMrp').val(selected);
            } else {
                resetRateSelect();
                $('#rate').val(parseFloat(currentMrp) > 0 ? parseFloat(currentMrp).toFixed(2) : '');
                $('#saleMrp').val(parseFloat(currentMrp) > 0 ? parseFloat(currentMrp).toFixed(2) : '0');
            }
        }

        function loadSaleRatesForItem(prodId, currentMrp) {
            if (!prodId) return;
            $.get("{{ route('sale-return.sale-rates') }}", { prod_id: prodId })
                .done(function(rows) {
                    if (String($('#selectedItemId').val()) !== String(prodId)) return;
                    applySaleRateOptions(rows, currentMrp);
                    calculateAmounts();
                });
        }

        function resetRateSelect() {
            $('#rateSelect').hide().empty();
            $('#rate').show();
        }

        function populateItemFields(item) {
            const productId = item.Prod_Id;
            const existingItem = itemsArray.find(function(row) {
                return String(row.item_id) === String(productId)
                    && parseFloat(row.rate) === parseFloat(item.MRP ?? item.Rate ?? 0);
            });
            const saleRates = Array.isArray(item.Sale_Rates) ? item.Sale_Rates
                : (Array.isArray(item.sale_rates) ? item.sale_rates : []);
            if (existingItem && saleRates.length === 1) {
                Swal.fire('Product Already Added',
                    `${item.Prod_ShortNm} is already in the list with quantity: ${existingItem.quantity}`,
                    'warning');
                $('#barcodeInput').val('').focus();
                return;
            }
            clearItemCalc();
            // Parse GST details if it's a string
            const gst = item.Gst_Details ? (typeof item.Gst_Details === 'string' ? JSON.parse(item.Gst_Details) : item
                .Gst_Details) : {};
            const cgst = parseFloat(gst.CGST) || 0;
            const sgst = parseFloat(gst.SGST) || 0;

            $('#selectedItemId').val(item.Prod_Id);
            $('#selectedHsnCode').val('');
            $('#itemSelected').val(item.Prod_ShortNm);
            $('#unitId').val(item.Unit_Id);
            $('#unitDisplay').val(item.Unit_Name);
            const stockMrp = parseFloat(item.MRP ?? item.Rate) || 0;
            applySaleRateOptions(saleRates, stockMrp);
            loadSaleRatesForItem(item.Prod_Id, stockMrp);
            $('#discountPercent').val(item.Discount || 0);
            $('#cgstRate').val(GST_TYPE == 2 ? 0 : cgst);
            $('#sgstRate').val(GST_TYPE == 2 ? 0 : sgst);
            $('#quantity').val(1);

            calculateAmounts();
            // Store additional data
            $('#barcodeInput').data('available-qty', item.Avil_Qnty);
            $('#barcodeInput').data('pack-date', item.Pack_Date);

            // Show available quantity info
            if (item.Avil_Qnty <= 0) {
                Swal.fire('Warning', 'This product is out of stock!', 'warning');
            }
            $('#saleDate').prop('disabled', true);
            $('#quantity').focus();
        }


        function calculateAmounts() {
            const qty = parseFloat($('#quantity').val());
            const rate = parseFloat($('#rate').val());
            const discPct = parseFloat($('#discountPercent').val());
            const cgstRate = parseFloat($('#cgstRate').val());
            const sgstRate = parseFloat($('#sgstRate').val());

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
             if (!$('#saleDate').val()) {
        Swal.fire('Error', 'Sale Date is required before adding items', 'error');
        return false;
    }
    
    const saleDate = new Date($('#saleDate').val());
    const today = new Date();
    const yearStart = new Date('{{ session("year_start") }}');
    const yearEnd = new Date('{{ session("year_end") }}');
    
    if (saleDate > today) {
        Swal.fire('Error', 'Sale Date cannot be After Today', 'error');
        return false;
    }
    
    if (saleDate < yearStart || saleDate > yearEnd) {
       Swal.fire('Error', 'Sale Date must be between {{ session("year_start") }} and {{ session("year_end") }}', 'error');
        return false;
    }
            if (!$('#selectedItemId').val()) {
                Swal.fire('Error', 'Please select an item', 'error');
                return false;
            }
            const qty = parseFloat($('#quantity').val());
            const rate = parseFloat($('#rate').val());
            const discPct = parseFloat($('#discountPercent').val()) || 0;
            const totAmt = parseFloat($('#totalAmount').val()) || 0;
            const availableQty = parseFloat($('#barcodeInput').data('available-qty'));
            const alreadyAdded = itemsArray.find(function(row) {
                return String(row.item_id) === String($('#selectedItemId').val())
                    && parseFloat(row.rate) === rate;
            });
            if (alreadyAdded) {
                Swal.fire('Error', 'This item at this rate is already in the list', 'error');
                return false;
            }

            if (!$('#quantity').val() || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                return false;
            }
            const maxReturnQty = parseFloat($('#maxReturnQty').val());
            if (!isNaN(maxReturnQty) && maxReturnQty > 0 && qty > maxReturnQty) {
                Swal.fire('Error', `Return quantity cannot exceed remaining sold qty (${maxReturnQty})`, 'error');
                return false;
            }
            if (!isNaN(availableQty) && qty > availableQty) {
                Swal.fire('Error', `You cannot sell this product as available quantity = ${availableQty}`, 'error');
                return false;
            }
            if (qty > 99999999.99) {
                Swal.fire('Error', 'Quantity cannot exceed 99999999.99', 'error');
                return false;
            }
            if (totAmt > 99999999.99) {
                Swal.fire('Error', 'Total Amount cannot exceed 99999999.99', 'error');
                return false;
            }
            return true;
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
            <td>${index + 1}</td><td>${item.item_name}</td><td>${item.quantity}</td>
            <td>${item.unit_name}</td><td>${item.rate}</td><td>${item.total_amount}</td>
            <td>${item.discount_percent}</td><td>${item.discount_amount}</td><td>${item.taxable_amount}</td>
            <td class="gst-col">${item.cgst_rate}</td><td class="gst-col">${item.cgst_amount}</td>
            <td class="gst-col">${item.sgst_rate}</td><td class="gst-col">${item.sgst_amount}</td>
            <td class="gst-col">${item.total_gst}</td><td>${item.net_amount}</td>
           <td style="white-space:nowrap; min-width:130px;">
    <button class="btn btn-primary btn-sm editItem me-1" data-index="${index}">Edit</button>
    <button class="btn btn-danger btn-sm removeItem" data-index="${index}">Delete</button>
</td>



        </tr>`;
            });

            $('#itemsTableBody').html(html);
            if (GST_TYPE == 2) {
                $('.gst-col').hide();
            }
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
            $('#quantity, #rate, #discountPercent').val('');
            $('#totalAmount, #discountAmount, #taxableAmount').val('');
            $('#cgstAmount, #sgstAmount, #totalGst, #netAmount').val('');
        }

        function clearItemSelection() {
            $('#selectedItemId, #selectedHsnCode, #unitId').val('');
            $('#itemSelected, #unitDisplay, #saleMrp').val('');
            $('#cgstRate, #sgstRate').val('');
            $('#maxReturnQty').val('');
            resetRateSelect();
            clearItemCalc();
        }

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : String(str)).html();
        }

        function renderSaleItemsTable(items) {
            if (saleItemsDT) {
                saleItemsDT.destroy();
                saleItemsDT = null;
            }
            if (!items.length) {
                $('#saleItemsBody').html(
                    '<tr><td colspan="12" class="text-center text-muted">No items found</td></tr>'
                );
                return;
            }
            let html = '';
            items.forEach((item, i) => {
                const gst = (parseFloat(item.CGST_Amt) || 0) + (parseFloat(item.SGST_Amt) || 0);
                const sold = item.Sold_Qty ?? item.qnty ?? '';
                const returned = item.Returned_Qty ?? 0;
                const remaining = item.Remaining_Qty ?? item.qnty ?? '';
                const canReturn = parseFloat(remaining) > 0;
                html += `<tr>
                    <td>${i + 1}</td>
                    <td>${escapeHtml(item.Prod_Name)}</td>
                    <td>${sold}</td>
                    <td>${returned}</td>
                    <td>${remaining}</td>
                    <td>${escapeHtml(item.Unit_Name)}</td>
                    <td>${item.Item_Rate ?? ''}</td>
                    <td>${item.Disc_Prcnt ?? 0}</td>
                    <td>${item.Taxable_Amt ?? ''}</td>
                    <td>${gst.toFixed(2)}</td>
                    <td>${item.Net_Amt ?? ''}</td>
                    <td>${canReturn
                        ? `<button type="button" class="btn btn-sm btn-success addReturnItem" data-index="${i}">Add Return</button>`
                        : '<span class="text-muted">Returned</span>'}</td>
                </tr>`;
            });
            $('#saleItemsBody').html(html);
            saleItemsDT = $('#saleItemsTable').DataTable({
                pageLength: 10,
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
        }

        function populateReturnFromSale(item, header) {
            const existing = itemsArray.find(function(x) {
                return String(x.item_id) === String(item.Prod_Id)
                    && parseFloat(x.rate) === parseFloat(item.Item_Rate);
            });
            if (existing) {
                Swal.fire('Product Already Added',
                    `${item.Prod_Name} is already in the return list`,
                    'warning');
                return false;
            }
            const remaining = parseFloat(item.Remaining_Qty ?? item.qnty) || 0;
            if (remaining <= 0) {
                Swal.fire('Already Returned', 'No remaining quantity for this item', 'warning');
                return false;
            }
            if (header && parseInt(header.Party_Id, 10) > 0) {
                $('#saleInternal').prop('checked', true);
                $('#customerDiv').show();
                $('#cashDiv').hide();
                $('#partyId').val(header.Party_Id).trigger('change');
            } else if (header) {
                $('#saleOutside').prop('checked', true);
                $('#customerDiv').hide();
                $('#cashDiv').show();
                $('#partyId').val('').trigger('change');
            }
            if (header && !$('#saleNo').val()) {
                $('#saleNo').val(header.Invoice_No || header.Ref_No || '');
            }
            clearItemSelection();
            $('#selectedItemId').val(item.Prod_Id);
            $('#selectedHsnCode').val(item.hsn_code || 0);
            $('#itemSelected').val(item.Prod_Name);
            $('#unitId').val(item.Unit_Id);
            $('#unitDisplay').val(item.Unit_Name);
            $('#quantity').val(remaining);
            $('#maxReturnQty').val(remaining);
            resetRateSelect();
            $('#rate').val(item.Item_Rate);
            $('#discountPercent').val(item.Disc_Prcnt || 0);
            $('#saleMrp').val(item.MRP || item.Item_Rate || 0);
            if (GST_TYPE == 2) {
                $('#cgstRate, #sgstRate').val(0);
            } else {
                $('#cgstRate').val(item.CGST_Prcnt || 0);
                $('#sgstRate').val(item.SGST_Prcnt || 0);
            }
            calculateAmounts();
            $('#quantity').focus();
            return true;
        }

        function resetForm() {
            itemsArray = [];
            currentSaleId = 0;
            $('#saleNo, #refVoucherNo, #instrumentNo').val('');
            $('#saleDate').val('{{ date('Y-m-d') }}');
            $('#partyId, #bankAccountId').val('').trigger('change');
            $('#bankSelectDiv, #instrumentNoDiv').hide();
            $('#transCash').prop('checked', true);
            $('#itemsTableBody').html('');
            $('#summaryTotalAmount, #totalTaxableAmount, #totalDiscountAmount, #totalGSTAmount, #roundOff, #finalNetAmount')
                .val('');
            $('#summaryDiscPercent').val('').prop('readonly', false).removeClass('calc-label');
            $('#saveSale').prop('disabled', false).text('Save');
            $('#saleDate').prop('disabled', false);
            clearItemSelection();
            $('#barcodeInput').focus();
            $('#saleInternal').prop('checked', true);
            $('#customerDiv').show();
            $('#cashDiv').hide();
        }
    </script>
@endpush
