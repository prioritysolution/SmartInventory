@extends('AgentDashboard.Layouts.layout')

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

        /* @media print {
                                    body * {
                                        visibility: hidden;
                                    }
                                    #billContent, #billContent * {
                                        visibility: visible;
                                    }
                                    #billContent {
                                        position: absolute;
                                        left: 0;
                                        top: 0;
                                        width: 100%;
                                    }
                                    .modal-header, .modal-footer {
                                        display: none !important;
                                    }
                                } */


        .bill-container {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            max-width: 420px;
            margin: 0 auto;
            color: #000;
            word-wrap: break-word;
        }

        .bill-header {
            text-align: center;
            padding-bottom: 4px;
        }

        .bill-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .bill-header p {
            margin: 4px 0 0 0;
            font-size: 13px;
        }

        .bill-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 8px 0 10px;
            font-size: 16px;
        }

        .bill-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 4px 0;
            font-size: 13px;
        }

        .bill-customer,
        .bill-agent {
            margin: 6px 0;
            font-size: 13px;
        }

        .bill-agent {
            margin-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 13px;
        }

        .items-table th,
        .items-table td {
            border: 1px dashed #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        .items-table th {
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table .item-name {
            word-break: break-word;
        }

        .items-table .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .items-table .text-center {
            text-align: center;
            white-space: nowrap;
        }

        .bill-words {
            font-size: 12px;
            line-height: 1.3;
        }

        .bill-signature {
            margin-top: 24px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        #partyId + .select2-container { width: 100% !important; }
        #barcodeScanBtn { min-width: 42px; }
        #barcodeScanner {
            min-height: 240px;
            background: #111;
            border-radius: 8px;
            overflow: hidden;
        }
        #barcodeScanner video,
        #barcodeScanner canvas {
            width: 100% !important;
            border-radius: 8px;
        }
        #qrCodeSection { display: none; margin-top: 10px; }
    </style>
@endpush

@section('content')
    @php
        $today = date('Y-m-d');
        $saleDateMax = min($today, $year_end);
        $saleDateDefault = min($saleDateMax, max($year_start, $today));
    @endphp
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
                <h6 class="mb-0">Agent Item Sale</h6>
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
                            <input type="date" class="form-control" id="saleDate" min="{{ $year_start }}"
                                max="{{ $saleDateMax }}" value="{{ $saleDateDefault }}">
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
                                <input type="text" class="form-control calc-label" value="Cash" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trans Mode<span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transMode" id="transCash"
                                        value="1" checked>
                                    <label class="form-check-label" for="transCash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transMode" id="transUpi"
                                        value="2">
                                    <label class="form-check-label" for="transUpi">UPI</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transMode" id="transCredit"
                                        value="3">
                                    <label class="form-check-label" for="transCredit">Credit</label>
                                </div>
                            </div>
                        </div>
                        <div id="qrCodeSection">
                            <label class="form-label">Scan QR to Pay</label>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=UPI_PAYMENT_LINK" alt="QR Code">
                        </div>
                    </div>
                </div>

                {{-- RIGHT BOX: Item Section --}}
                <div class="col-md-6">
                    <div class="section-card item-entry h-100">
                        <h6>Item Section</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="isax isax-scan"></i> Scan Barcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="barcodeInput"
                                        placeholder="Scan or type barcode & press Enter" autocomplete="off">
                                    <button class="btn btn-outline-primary" type="button" id="barcodeScanBtn" title="Scan barcode">
                                        <i class="fas fa-barcode"></i>
                                    </button>
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
                                <input type="hidden" id="itemSaleDate">
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

    {{-- Barcode Camera Scanner --}}
    <div class="modal fade" id="barcodeScanModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scan Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="barcodeScanner"></div>
                    <p class="text-muted small text-center mt-2 mb-0">Point the camera at the barcode</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bill Modal --}}
    <div class="modal fade" id="billModal" tabindex="-1" role="dialog" aria-labelledby="billModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billModalLabel">Sale Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="billContent">
                    <!-- Bill content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printBill()">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const SESSION_YEAR_START = "{{ $year_start }}";
        const SESSION_YEAR_END = "{{ $year_end }}";
        const SESSION_SALE_DATE_DEFAULT = "{{ $saleDateDefault }}";
        const SESSION_SALE_DATE_MAX = "{{ $saleDateMax }}";
        let itemsArray = [];
        let currentSaleId = 0;
        const GST_TYPE = {{ session('gst_type', 1) }};
        const ORG_NAME = @json(session('org_name', ''));
        const BRANCH_NAME = @json(session('branch_name', ''));
        const AGENT_NAME = @json(session('agent_name', ''));
        const AGENT_CODE = @json(session('agent_code', ''));
        let itemPickerDT = null;
        let allItemsData = [];
        let barcodeScanner = null;
        let barcodeScannerRunning = false;

        function showBillModal(billData) {
            if (!billData) {
                Swal.fire('Error', 'No bill data available', 'error');
                return;
            }
            document.getElementById('billContent').innerHTML = generateBillHTML(billData);
            new bootstrap.Modal(document.getElementById('billModal')).show();
        }

        function pickVal(obj, keys, fallback) {
            if (!obj) return fallback;
            for (let i = 0; i < keys.length; i++) {
                const val = obj[keys[i]];
                if (val !== undefined && val !== null && val !== '') return val;
            }
            return fallback;
        }

        function fmtAmt(n) {
            const val = parseFloat(n);
            return isNaN(val) ? '0.00' : val.toFixed(2);
        }

        function fmtDate(d) {
            return (window.siDate && siDate.toDisplay) ? siDate.toDisplay(d) : (d || '');
        }

        function numberToWords(amount) {
            var num = Math.round((parseFloat(amount) || 0) * 100) / 100;
            var rupees = Math.floor(num);
            var paise = Math.round((num - rupees) * 100);
            var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function twoDigits(n) {
                if (n < 20) return ones[n];
                return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
            }
            function threeDigits(n) {
                if (n < 100) return twoDigits(n);
                return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + twoDigits(n % 100) : '');
            }
            function toWords(n) {
                if (n === 0) return 'Zero';
                var crore = Math.floor(n / 10000000);
                var lakh = Math.floor((n % 10000000) / 100000);
                var thousand = Math.floor((n % 100000) / 1000);
                var rest = n % 1000;
                var parts = [];
                if (crore) parts.push(twoDigits(crore) + ' Crore');
                if (lakh) parts.push(twoDigits(lakh) + ' Lakh');
                if (thousand) parts.push(twoDigits(thousand) + ' Thousand');
                if (rest) parts.push(threeDigits(rest));
                return parts.join(' ');
            }

            var text = 'Rs. ' + toWords(rupees);
            if (paise > 0) {
                text += ' and ' + twoDigits(paise) + ' Paise';
            }
            return text + ' Only';
        }

        function captureBillSnapshot() {
            const isOutside = $('input[name="saleType"]:checked').val() === 'outside';
            const customer = isOutside ? 'Cash' : (($('#partyId option:selected').text() || '').trim() || 'Cash');
            const agentLabel = [AGENT_NAME, AGENT_CODE].filter(Boolean).join(' - ');
            return {
                Org_Name: ORG_NAME,
                Branch_Name: BRANCH_NAME,
                Invoice_No: '',
                Invoice_Date: $('#saleDate').val(),
                Cust_Name: customer,
                Agent_Name: agentLabel,
                Discount: $('#totalDiscountAmount').val() || 0,
                Disc_Percent: $('#summaryDiscPercent').val() || 0,
                Round_Off: $('#roundOff').val() || 0,
                Tot_Amount: $('#finalNetAmount').val() || 0,
                Gross_Amt: $('#summaryTotalAmount').val() || 0,
                items: itemsArray.map(item => ({
                    item_name: item.item_name,
                    qty: item.quantity,
                    rate: item.sale_mrp || item.rate,
                    amount: item.total_amount
                }))
            };
        }

        function mergeBillData(apiBill, snapshot) {
            const src = apiBill || {};
            const items = (src.items && src.items.length) ? src.items
                : (src.Item_Details && src.Item_Details.length) ? src.Item_Details
                : (snapshot.items || []);
            return {
                Org_Name: pickVal(src, ['Org_Name', 'org_name'], snapshot.Org_Name),
                Branch_Name: pickVal(src, ['Branch_Name', 'branch_name'], snapshot.Branch_Name),
                Invoice_No: pickVal(src, ['Invoice_No', 'invoice_no', 'Sale_No'], snapshot.Invoice_No),
                Invoice_Date: pickVal(src, ['Invoice_Date', 'invoice_date', 'Sale_Date'], snapshot.Invoice_Date),
                Cust_Name: pickVal(src, ['Cust_Name', 'cust_name', 'Party_Name'], snapshot.Cust_Name),
                Agent_Name: pickVal(src, ['Agent_Name', 'agent_name'], snapshot.Agent_Name),
                Discount: pickVal(src, ['Discount', 'Disc_Amt', 'discount'], snapshot.Discount),
                Disc_Percent: pickVal(src, ['Disc_Percent', 'Disc_Perc', 'disc_percent'], snapshot.Disc_Percent),
                Round_Off: pickVal(src, ['Round_Off', 'round_off'], snapshot.Round_Off),
                Tot_Amount: pickVal(src, ['Tot_Amount', 'Net_Amt', 'net_amount'], snapshot.Tot_Amount),
                Gross_Amt: pickVal(src, ['Gross_Amt', 'Tot_Amt', 'gross'], snapshot.Gross_Amt),
                items: items
            };
        }

        function generateBillHTML(billData) {
            let itemsHtml = '';
            const rows = billData.items && Array.isArray(billData.items) ? billData.items : [];
            rows.forEach((item) => {
                const name = pickVal(item, ['item_name', 'Item_Name', 'Prod_ShortNm', 'Prod_Name'], 'N/A');
                const qty = pickVal(item, ['qty', 'Qty', 'quantity', 'Qnty'], '0');
                const rate = pickVal(item, ['rate', 'Rate', 'MRP'], 0);
                const amount = pickVal(item, ['amount', 'Amount', 'net_amount', 'Net_Amt', 'total_amount', 'Item_Total'], 0);
                itemsHtml += `
                <tr>
                    <td class="item-name">${name}</td>
                    <td class="text-center">${qty}</td>
                    <td class="text-right">${fmtAmt(rate)}</td>
                    <td class="text-right">${fmtAmt(amount)}</td>
                </tr>`;
            });

            const discount = parseFloat(billData.Discount) || 0;
            const roundOff = parseFloat(billData.Round_Off) || 0;
            const totalAmount = parseFloat(billData.Tot_Amount) || 0;
            const grossAmt = parseFloat(billData.Gross_Amt) || 0;
            const agentLabel = billData.Agent_Name || [AGENT_NAME, AGENT_CODE].filter(Boolean).join(' - ');
            let discPct = parseFloat(billData.Disc_Percent) || 0;
            if (!discPct && grossAmt > 0 && discount > 0) {
                discPct = (discount / grossAmt) * 100;
            }
            const discPctLabel = discPct > 0 ? fmtAmt(discPct) : '';

            return `
        <div class="bill-container">
            <div class="bill-header">
                <h3>${billData.Org_Name || 'Smart Inventory'}</h3>
                ${billData.Branch_Name ? `<p>${billData.Branch_Name}</p>` : ''}
            </div>
            <div class="bill-title">BILL</div>
            <div class="bill-meta">
                <span>No. : ${billData.Invoice_No || 'N/A'}</span>
                <span>Date : ${fmtDate(billData.Invoice_Date) || 'N/A'}</span>
            </div>
            <div class="bill-customer">Customer Name : ${billData.Cust_Name || 'Cash'}</div>
            <div class="bill-agent">Agent Name : ${agentLabel}</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th class="text-center" style="width:14%;">QTY</th>
                        <th class="text-right" style="width:20%;">MRP</th>
                        <th class="text-right" style="width:22%;">AMT</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml || '<tr><td colspan="4" class="text-center">No items</td></tr>'}
                    <tr>
                        <td colspan="3" class="text-right">Amount :</td>
                        <td class="text-right">${fmtAmt(grossAmt || totalAmount)}</td>
                    </tr>
                    <tr>
                        <td colspan="3">(-) Disc @ ${discPctLabel}% :</td>
                        <td class="text-right">${discount > 0 ? fmtAmt(discount) : ''}</td>
                    </tr>
                    ${roundOff != 0 ? `<tr>
                        <td colspan="3">Round Off</td>
                        <td class="text-right">${fmtAmt(roundOff)}</td>
                    </tr>` : ''}
                    <tr>
                        <td colspan="3" class="bill-words">${numberToWords(totalAmount)}</td>
                        <td class="text-right"><strong>${fmtAmt(totalAmount)}</strong></td>
                    </tr>
                </tbody>
            </table>
            <div class="bill-signature">Signature</div>
        </div>`;
        }

        function printBill() {
            const billHtml = document.getElementById('billContent').innerHTML;
            let iframe = document.getElementById('printFrame');
            if (iframe) iframe.remove();
            iframe = document.createElement('iframe');
            iframe.id = 'printFrame';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'position:fixed;top:0;left:0;width:58mm;height:500mm;border:0;opacity:0;pointer-events:none;z-index:-1;';
            document.body.appendChild(iframe);
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page {
    size: 58mm 400mm;
    margin: 2mm 1.5mm;
}
html, body {
    margin: 0;
    padding: 0;
    width: 58mm;
}
body { width: 58mm; }
.bill-container {
    width: 54mm;
    max-width: 54mm;
    margin: 0 auto;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000;
    line-height: 1.25;
    word-wrap: break-word;
}
.bill-header { text-align:center; padding-bottom:2px; }
.bill-header h3 { font-size:11px; font-weight:bold; text-transform:uppercase; margin:0; line-height:1.2; }
.bill-header p { font-size:9px; margin:2px 0 0; }
.bill-title { text-align:center; font-weight:bold; text-decoration:underline; text-transform:uppercase; letter-spacing:1px; margin:4px 0 6px; font-size:11px; }
.bill-meta { display:block; overflow:hidden; margin:2px 0; font-size:9px; }
.bill-meta span { display:inline-block; width:49%; vertical-align:top; }
.bill-meta span:last-child { text-align:right; }
.bill-customer, .bill-agent { margin:3px 0 6px; font-size:9px; }
.items-table { width:100%; border-collapse:collapse; font-size:9px; }
.items-table th, .items-table td { border:1px dashed #000; padding:2px 3px; text-align:left; vertical-align:top; }
.items-table th { font-weight:bold; text-transform:uppercase; }
.items-table .item-name { word-break:break-word; }
.text-right { text-align:right; white-space:nowrap; }
.text-center { text-align:center; white-space:nowrap; }
.bill-words { font-size:8px; line-height:1.2; }
.bill-signature { margin-top:14px; font-size:9px; }
</style>
</head>
<body>${billHtml}</body>
</html>`);
            doc.close();
            setTimeout(function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 150);
        }


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

            if (GST_TYPE == 2) {
                $('#cgstRate, #cgstAmount, #sgstRate, #sgstAmount, #totalGst').closest('.gst-col').hide();
                $('th.gst-col').hide();
                $('#totalGSTAmount').closest('tr').hide();
            }


            $('input[name="transMode"]').on('change', function () {
                $('#qrCodeSection').toggle($(this).val() === '2');
            });

            $('#barcodeScanBtn').on('click', function (e) {
                e.preventDefault();
                if (!ensureSaleDate()) return;
                $('#barcodeScanModal').modal('show');
            });

            $('#barcodeScanModal').on('shown.bs.modal', function () {
                startBarcodeScanner();
            });

            $('#barcodeScanModal').on('hidden.bs.modal', function () {
                stopBarcodeScanner();
            });

            // ── Barcode Scanner ──────────────────────────────────────────
            $('#barcodeInput').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupBarcode();
                }
            });


            function getBarcodeScanFormats() {
                if (typeof Html5QrcodeSupportedFormats === 'undefined') {
                    return undefined;
                }
                return [
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.QR_CODE
                ];
            }

            function stopBarcodeScanner() {
                if (!barcodeScanner || !barcodeScannerRunning) {
                    if (barcodeScanner) {
                        try { barcodeScanner.clear(); } catch (e) {}
                        barcodeScanner = null;
                    }
                    $('#barcodeScanner').empty();
                    return;
                }
                barcodeScannerRunning = false;
                barcodeScanner.stop().then(function () {
                    try { barcodeScanner.clear(); } catch (e) {}
                    barcodeScanner = null;
                    $('#barcodeScanner').empty();
                }).catch(function () {
                    barcodeScanner = null;
                    $('#barcodeScanner').empty();
                });
            }

            function startBarcodeScanner() {
                if (typeof Html5Qrcode === 'undefined') {
                    $('#barcodeScanModal').modal('hide');
                    Swal.fire('Error', 'Barcode scanner could not load. Check your internet connection.', 'error');
                    return;
                }
                if (barcodeScannerRunning) {
                    return;
                }

                $('#barcodeScanner').empty();
                barcodeScanner = new Html5Qrcode('barcodeScanner');
                const config = {
                    fps: 12,
                    qrbox: function (viewfinderWidth, viewfinderHeight) {
                        return {
                            width: Math.floor(Math.min(viewfinderWidth * 0.9, 280)),
                            height: Math.floor(Math.min(viewfinderHeight * 0.35, 120))
                        };
                    }
                };
                const formats = getBarcodeScanFormats();
                if (formats) {
                    config.formatsToSupport = formats;
                }

                let scanHandled = false;
                barcodeScanner.start(
                    { facingMode: 'environment' },
                    config,
                    function (decodedText) {
                        if (scanHandled || !decodedText) return;
                        scanHandled = true;
                        $('#barcodeInput').val(decodedText.trim());
                        $('#barcodeScanModal').modal('hide');
                        lookupBarcode();
                    }
                ).then(function () {
                    barcodeScannerRunning = true;
                }).catch(function () {
                    barcodeScannerRunning = false;
                    barcodeScanner = null;
                    $('#barcodeScanModal').modal('hide');
                    Swal.fire('Camera Error', 'Unable to open the camera. Allow camera access and try again.', 'error');
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

            function loadItemByProdId(prodId) {
                if (!ensureSaleDate()) return;
                $.get("{{ route('agent.sale.item-info') }}", {
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

                $.get("{{ route('agent.sale.barcode') }}", {
                        barcode: barcode,
                        sale_date: saleDate
                    })
                    .done(function(item) {
                        populateItemFields(item);
                        $('#barcodeInput').focus();
                    })
                    .fail(function() {
                        $.get("{{ route('agent.sale.items') }}", {
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
                const yearStartObj = new Date(SESSION_YEAR_START);
                const yearEndObj = new Date(SESSION_SALE_DATE_MAX);
                if (saleDateObj > yearEndObj) {
                    Swal.fire('Error', 'Sale Date cannot be after ' + SESSION_SALE_DATE_MAX, 'error');
                    $('#saleDate').focus();
                    return false;
                }
                if (saleDateObj < yearStartObj) {
                    Swal.fire('Error',
                        'Sale Date must be between ' + SESSION_YEAR_START + ' and ' + SESSION_SALE_DATE_MAX,
                        'error');
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
                $.get("{{ route('agent.sale.items') }}", {
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
                $.get("{{ route('agent.sale.subcats') }}", {
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
                $.get("{{ route('agent.sale.items') }}", {
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
                // Partial code (e.g. "sm") must not auto-select category/subcategory.
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
                $.get("{{ route('agent.sale.item-info') }}", {
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

            // ── Trans Mode handled above for UPI QR ────────────────────

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
                    item_sale_date: $('#itemSaleDate').val() || null,
                });
                renderItemsTable();
                clearItemSelection();
                $('#saleDate').prop('disabled', true);
                $('#barcodeInput').val('').focus();

                $('#addItemBtn').text('+ Add Item').data('editing', false);
            });

            $(document).on('click', '.removeItem', function() {
                itemsArray.splice($(this).data('index'), 1);
                renderItemsTable();
            });

            $(document).on('click', '.editItem', function() {
                const index = $(this).data('index');
                const item = itemsArray[index];

                // Populate the form with item data for editing
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

                // Remove the item from array temporarily
                itemsArray.splice(index, 1);
                renderItemsTable();

                // Change button text to indicate editing mode
                $('#addItemBtn').text('Update Item').data('editing', true);

                // Focus on quantity field for editing
                $('#quantity').focus();

                // Disable sale date during editing
                $('#saleDate').prop('disabled', true);
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

                $(this).prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('agent.sale.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        sale_id: currentSaleId,
                        sale_date: $('#saleDate').val(),
                        party_id: isOutside ? 0 : $('#partyId').val(),
                        trans_mode: $('input[name="transMode"]:checked').val(),
                        disc_percent: $('#summaryDiscPercent').prop('readonly') ? 0 : (parseFloat($('#summaryDiscPercent').val()) || 0),
                        disc_amt: $('#totalDiscountAmount').val() || 0,
                        round_off: $('#roundOff').val() || 0,
                        net_amt: $('#finalNetAmount').val() || 0,
                        items: itemsArray
                    },
                    success: function(res) {
                        const snapshot = captureBillSnapshot();
                        Swal.fire('Success', res.message, 'success').then(() => {
                            const bill = mergeBillData(res.bill_data, snapshot);
                            if (!bill.Invoice_No) {
                                const m = (res.message || '').match(/No Is\s+(.+)$/i);
                                if (m) bill.Invoice_No = m[1].trim();
                            }
                            showBillModal(bill);
                            resetForm();
                        });
                    },
                    error: function(xhr) {
                        $('#saveSale').prop('disabled', false).text('Save');
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save', 'error');
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
            $.get("{{ route('agent.sale.sale-rates') }}", { prod_id: prodId })
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
            const existingItem = itemsArray.find(function(row) {
                return String(row.item_id) === String(item.Prod_Id)
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
            $('#itemSaleDate').val(item.Pack_Date || '');
            $('#quantity').val(1);
            calculateAmounts();
            if (item.Avil_Qnty != null) {
                $('#barcodeInput').data('available-qty', item.Avil_Qnty);
                $('#barcodeInput').data('pack-date', item.Pack_Date);
                if (item.Avil_Qnty <= 0) {
                    Swal.fire('Warning', 'This product is out of stock!', 'warning');
                }
            } else {
                $('#barcodeInput').removeData('available-qty');
                $('#barcodeInput').removeData('pack-date');
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
            const yearStart = new Date(SESSION_YEAR_START);
            const yearEnd = new Date(SESSION_SALE_DATE_MAX);

            if (saleDate > yearEnd) {
                Swal.fire('Error', 'Sale Date cannot be after ' + SESSION_SALE_DATE_MAX, 'error');
                return false;
            }

            if (saleDate < yearStart) {
                Swal.fire('Error', 'Sale Date must be between ' + SESSION_YEAR_START + ' and ' + SESSION_SALE_DATE_MAX,
                    'error');
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
            if (alreadyAdded && !$('#addItemBtn').data('editing')) {
                Swal.fire('Error', 'This item at this rate is already in the list', 'error');
                return false;
            }

            if (!$('#quantity').val() || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                return false;
            }
            if (!isNaN(availableQty) && availableQty <= 0) {
                Swal.fire('Error', 'This product is out of stock!', 'error');
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
            const showGst = GST_TYPE != 2;

            itemsArray.forEach((item, index) => {
                totalAmt += parseFloat(item.total_amount) || 0;
                totalTaxable += parseFloat(item.taxable_amount) || 0;
                totalDiscount += parseFloat(item.discount_amount) || 0;
                totalGST += parseFloat(item.total_gst) || 0;
                if (parseFloat(item.discount_percent) > 0) hasItemDiscount = true;
                if (parseFloat(item.cgst_rate) > 0 || parseFloat(item.sgst_rate) > 0) hasGST = true;

                const gstCells = showGst
                    ? `<td>${item.cgst_rate}</td>
            <td>${item.cgst_amount}</td>
            <td>${item.sgst_rate}</td>
            <td>${item.sgst_amount}</td>
            <td>${item.total_gst}</td>`
                    : '';

                html += `<tr>
            <td>${index + 1}</td>
            <td style="white-space:nowrap; min-width:100px;">${item.item_name}</td>
            <td>${item.quantity}</td>
            <td>${item.unit_name}</td>
            <td>${item.rate}</td>
            <td>${item.total_amount}</td>
            <td>${item.discount_percent}</td>
            <td>${item.discount_amount}</td>
            <td>${item.taxable_amount}</td>
            ${gstCells}
            <td>${item.net_amount}</td>
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
            $('#quantity, #rate, #discountPercent').val('');
            $('#totalAmount, #discountAmount, #taxableAmount').val('');
            $('#cgstAmount, #sgstAmount, #totalGst, #netAmount').val('');
        }

        function clearItemSelection() {
            $('#selectedItemId, #selectedHsnCode, #unitId').val('');
            $('#itemSelected, #unitDisplay, #saleMrp, #itemSaleDate').val('');
            $('#cgstRate, #sgstRate').val('');
            resetRateSelect();
            clearItemCalc();
            $('#addItemBtn').text('+ Add Item').data('editing', false);
        }

        function resetForm() {
            itemsArray = [];
            currentSaleId = 0;
            $('#saleDate').val(SESSION_SALE_DATE_DEFAULT);
            $('#partyId').val('').trigger('change');
            $('#saleInternal').prop('checked', true);
            $('#customerDiv').show();
            $('#cashDiv').hide();
            $('#transCash').prop('checked', true);
            $('#qrCodeSection').hide();
            $('#itemsTableBody').html('');
            $('#summaryTotalAmount, #totalTaxableAmount, #totalDiscountAmount, #totalGSTAmount, #roundOff, #finalNetAmount')
                .val('');
            $('#summaryDiscPercent').val('').prop('readonly', false).removeClass('calc-label');
            $('#saveSale').prop('disabled', false).text('Save');
            $('#saleDate').prop('disabled', false);
            clearItemSelection();
            $('#barcodeInput').focus();
            $('#addItemBtn').text('+ Add Item').data('editing', false);
        }
    </script>
@endpush
