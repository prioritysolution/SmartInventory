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

            #barcodeInput:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
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
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                max-width: 350px;
                margin: 0 auto;
            }

            .bill-header {
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 10px;
            }

            .bill-header h3 {
                margin: 0;
                font-size: 16px;
                font-weight: bold;
            }

            .bill-header p {
                margin: 5px 0 0 0;
                font-size: 12px;
            }

            .bill-details {
                margin-bottom: 10px;
            }

            .bill-details p {
                margin: 3px 0;
                display: flex;
                justify-content: space-between;
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
                font-size: 10px;
            }

            .items-table th,
            .items-table td {
                border: 1px solid #000;
                padding: 4px 2px;
                text-align: left;
            }

            .items-table th {
                background-color: #f0f0f0;
                font-weight: bold;
            }

            .items-table .text-right {
                text-align: right;
            }

            .total-section {
                border-top: 2px solid #000;
                padding-top: 8px;
                margin-top: 10px;
            }

            .total-section p {
                margin: 3px 0;
                display: flex;
                justify-content: space-between;
            }

            .total-section .final-total {
                font-weight: bold;
                font-size: 14px;
                border-top: 1px solid #000;
                padding-top: 5px;
                margin-top: 5px;
            }

            .bill-footer {
                text-align: center;
                margin-top: 15px;
                font-size: 10px;
                border-top: 1px dashed #000;
                padding-top: 8px;
            }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
                <h6 class="mb-0">Counter Sale</h6>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="searchSaleBtn" style="width: 100px;">
                    <svg aria-hidden="true" class="me-2" width="18" height="18" viewBox="0 0 18 18">
                        <path
                            d="m18 16.5-5.14-5.18h-.35a7 7 0 1 0-1.19 1.19v.35L16.5 18zM12 7A5 5 0 1 1 2 7a5 5 0 0 1 10 0">
                        </path>
                    </svg> Search
                </button>
            </div>


            <div class="row mb-3">

                {{-- LEFT BOX: Sale Info --}}
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <h6>Sale Info</h6>
                        <div class="mb-3">
                            <label class="form-label">Sale Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="saleDate" min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Customer<span class="text-danger">*</span></label>
                            <select class="form-select" id="partyId">
                                <option value="">-- Select Customer --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->Party_Id }}">
                                        {{ $customer->Party_Name }}-{{ $customer->Party_Code }}</option>
                                @endforeach
                            </select>
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

                        {{-- Barcode Row --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="isax isax-scan"></i> Scan Barcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="barcodeInput"
                                        placeholder="Scan or type barcode & press Enter" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Item Selected</label>
                                <input type="text" class="form-control calc-label" id="itemSelected" readonly
                                    placeholder="Scan barcode to select item">
                                <input type="hidden" id="selectedItemId">
                                <input type="hidden" id="selectedHsnCode">
                                <input type="hidden" id="unitId">
                                <input type="hidden" id="saleMrp">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control calc-label" id="unitDisplay" readonly>
                            </div>
                        </div>

                        {{-- Calc Fields --}}
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Qty<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="0.01" min="0.01"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Rate<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rate" step="0.01" min="0"
                                    autocomplete="off" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Total Amt</label>
                                <input type="text" class="form-control calc-label" id="totalAmount" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Disc %</label>
                                <input type="number" class="form-control" id="discountPercent" step="0.01"
                                    max="100" min="0" autocomplete="off" readonly>
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
                                <label class="form-label">&nbsp;</label>
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
                                        <th>CGST Rate</th>
                                        <th>CGST Amt</th>
                                        <th>SGST Rate</th>
                                        <th>SGST Amt</th>
                                        <th>Total GST</th>
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

    {{-- Sale Search Modal --}}
    <div class="modal fade" id="saleSearchModal" tabindex="-1" data-bs-backdrop="static">
           <div class="modal-dialog modal-lg modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Sale</h5>
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
                            <button type="button" class="btn btn-primary w-100" id="searchSaleGo">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="saleSearchTable" class="table table-bordered table-sm w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Invoice No</th>
                                    <th>Invoice Date</th>
                                    <th>Net Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="saleSearchBody">
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


    {{-- Bill Modal --}}
    <div class="modal fade" id="billModal" tabindex="-1" role="dialog" aria-labelledby="billModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document" style="max-width: 400px;">
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
    <script>
        const SESSION_YEAR_START = "{{ session('year_start') }}";
        const SESSION_YEAR_END = "{{ session('year_end') }}";
        let itemsArray = [];
        let currentSaleId = 0;
        let saleSearchDT = null;

        function showBillModal(billData) {
            if (!billData) {
                alert('No bill data available');
                return;
            }

            const billHtml = generateBillHTML(billData);
            document.getElementById('billContent').innerHTML = billHtml;

            // Show modal using Bootstrap
            const billModal = new bootstrap.Modal(document.getElementById('billModal'));
            billModal.show();
        }

        // Function to generate bill HTML
        function generateBillHTML(billData) {
            let itemsHtml = '';

            if (billData.items && Array.isArray(billData.items)) {
                billData.items.forEach(item => {
                    itemsHtml += `
                <tr>
                    <td>${item.item_name || 'N/A'}</td>
                    <td class="text-right">${item.qty }</td>
                    <td class="text-right">${parseFloat(item.rate ).toFixed(2)}</td>
                    <td class="text-right">${parseFloat(item.amount ).toFixed(2)}</td>
                </tr>
            `;
                });
            }

            const discount = parseFloat(billData.Discount);
            const totalAmount = parseFloat(billData.Tot_Amount);

            return `
        <div class="bill-container">
            <div class="bill-header">
                <h3>${billData.Cust_Name}</h3>
                <p>Sale Invoice</p>
            </div>
            
            <div class="bill-details">
                <p><strong>Invoice No:</strong> <span>${billData.Invoice_No || 'N/A'}</span></p>
                <p><strong>Date:</strong> <span>${billData.Invoice_Date || 'N/A'}</span></p>
                <p><strong>Customer:</strong> <span>${billData.Cust_Name || 'N/A'}</span></p>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            
            <div class="total-section">
                ${discount > 0 ? `<p><strong>Discount:</strong> <span>${discount.toFixed(2)}</span></p>` : ''}
                <p class="final-total"><strong>Total Amount:</strong> <span>${totalAmount.toFixed(2)}</span></p>
            </div>
            
            <div class="bill-footer">
                <p>Thank you for your business!</p>
                <p>Visit Again!</p>
            </div>
        </div>
    `;
        }

        // Function to print bill
        function printBill() {
            const billHtml = document.getElementById('billContent').innerHTML;

            let iframe = document.getElementById('printFrame');
            if (iframe) iframe.remove();

            iframe = document.createElement('iframe');
            iframe.id = 'printFrame';
            iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:80mm;height:auto;border:none;';
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
    size: 80mm auto;
    margin: 0;
}

html, body {
    margin: 0;
    padding: 0;
    width: 100%;
}

body {
    display: flex;
    justify-content: center;
}
.bill-container {
    width: 76mm;
    margin: 0 auto; 
}
    .bill-header { text-align:center; border-bottom:1px dashed #000; padding-bottom:5px; margin-bottom:5px; }
    .bill-header h3 { font-size:14px; font-weight:bold; }
    .bill-header p { font-size:11px; }
    .bill-details p { display:flex; justify-content:space-between; margin:2px 0; }
    .items-table { width:100%; border-collapse:collapse; margin:5px 0; font-size:10px; }
    .items-table th, .items-table td { border:1px solid #000; padding:2px 3px; }
    .items-table th { background:#eee; }
    .text-right { text-align:right; }
    .total-section { border-top:1px dashed #000; padding-top:5px; margin-top:5px; }
    .total-section p { display:flex; justify-content:space-between; margin:2px 0; }
    .final-total { font-weight:bold; font-size:12px; border-top:1px solid #000; padding-top:3px; margin-top:3px; }
    .bill-footer { text-align:center; border-top:1px dashed #000; padding-top:5px; margin-top:8px; font-size:10px; }
</style>
</head>
<body>${billHtml}</body>
</html>`);
            doc.close();

            iframe.contentWindow.onload = function() {
                iframe.contentWindow.print();
            };
        }


        $(document).ready(function() {

            $('#partyId').select2({
                placeholder: '-- Select Customer --',
                allowClear: true
            });
            $('#bankAccountId').select2({
                placeholder: '-- Select Bank --',
                allowClear: true
            });


            // Function to show bill modal




            $('#searchSaleBtn').on('click', function() {
                $('#saleSearchModal').modal('show');
            });

            $('#searchSaleGo').on('click', function() {
                const fromDate = $('#searchFromDate').val();
                const toDate = $('#searchToDate').val();

                if (!fromDate || !toDate) {
                    Swal.fire('Error', 'Please select From Date and To Date', 'error');
                    return;
                }

                if (saleSearchDT) {
                    saleSearchDT.destroy();
                    saleSearchDT = null;
                }
                $('#saleSearchBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

                $.get("{{ url('counter-sale/search') }}", {
                    from_date: fromDate,
                    to_date: toDate
                }, function(data) {
                    let html = '';
                    if (!data.length) {
                        html =
                            '<tr><td colspan="5" class="text-center text-muted">No records found</td></tr>';
                        $('#saleSearchBody').html(html);
                        return;
                    }
                    data.forEach((row, i) => {
                        html += `<tr>
                <td>${i + 1}</td>
                <td>${row.Invoice_No}</td>
                <td>${row.Invoice_Date}</td>
                <td>${row.Net_Amt}</td>
                <td><button class="btn btn-sm btn-primary viewSale" data-id="${row.Sale_Id}">Edit</button></td>
            </tr>`;
                    });
                    $('#saleSearchBody').html(html);
                    saleSearchDT = $('#saleSearchTable').DataTable({
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
                    $('#saleSearchBody').html(
                        '<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>'
                    );
                });
            });


            $(document).on('click', '.viewSale', function() {
                const saleId = $(this).data('id');
                currentSaleId = saleId;

                $.get("{{ url('counter-sale/details') }}/" + saleId, function(data) {
                    $('#saleSearchModal').modal('hide');

                    // Fill header
                    $('#saleDate').val(data.Invoice_Date ? data.Invoice_Date.substring(0, 10) : '');
                    $('#partyId').val(data.Party_Id).trigger('change');
                    $('#saleNo').val(data.Ref_No);

                    // Set transaction mode based on data (you may need to adjust this based on your data structure)
                    $('input[name="transMode"][value="1"]').prop('checked',
                        true); // Default to Cash

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
                        total_gst: (parseFloat(item.SGST_Amt) + parseFloat(item
                            .CGST_Amt)).toFixed(2),
                        net_amount: item.Net_Amt,
                        sale_mrp: item.MRP
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
                    Swal.fire('Error', 'Failed to load sale details', 'error');
                });
            });

            $('#saleSearchModal').on('hidden.bs.modal', function() {
                if (saleSearchDT) {
                    saleSearchDT.destroy();
                    saleSearchDT = null;
                }
                $('#saleSearchBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Use filters above to search</td></tr>'
                );
                $('#searchFromDate, #searchToDate, #searchSaleNo').val('');
            });
            // ── Barcode Scanner ──────────────────────────────────────────
            $('#barcodeInput').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupBarcode();
                }
            });


            function lookupBarcode() {
                const barcode = $('#barcodeInput').val().trim();
                const saleDate = $('#saleDate').val();

                if (!barcode) return;

                if (!saleDate) {
                    Swal.fire('Error', 'Please select sale date first', 'error');
                    $('#saleDate').focus();
                    return;
                }
                const saleDateObj = new Date(saleDate);
                const yearStartObj = new Date('{{ session('year_start') }}');
                const yearEndObj = new Date('{{ session('year_end') }}');
                const today = new Date();

                if (saleDateObj > today) {
                    Swal.fire('Error', 'Sale Date cannot be After Today', 'error');
                    $('#saleDate').focus();
                    return;
                }

                if (saleDateObj < yearStartObj || saleDateObj > yearEndObj) {
                    Swal.fire('Error',
                        'Sale Date must be between {{ session('year_start') }} and {{ session('year_end') }}',
                        'error');
                    $('#saleDate').focus();
                    return;
                }
                $.get("{{ route('counter-sale.barcode') }}", {
                        barcode: barcode,
                        sale_date: saleDate
                    })
                    .done(function(item) {
                        populateItemFields(item);
                        $('#barcodeInput').focus();
                    })
                    .fail(function() {
                        Swal.fire('Not Found', 'No item found for barcode: ' + barcode, 'warning');
                        $('#barcodeInput').select();
                    });
            }


            $('#quantity').on('input', calculateAmounts);

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
                    $('#bankSelectDiv, #bankRemarksDiv').show();
                } else {
                    $('#bankSelectDiv, #bankRemarksDiv').hide();
                    $('#bankAccountId').val('').trigger('change');
                    $('#bankRemarks').val('');
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
                    sale_mrp: $('#saleMrp').val() || 0,
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
                if (!$('#partyId').val()) {
                    Swal.fire('Error', 'Please select a Customer', 'error');
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

                $(this).prop('disabled', true).text(currentSaleId > 0 ? 'Updating...' : 'Saving...');

                $.ajax({
                    url: "{{ route('counter-sale.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        sale_id: currentSaleId,
                        sale_date: $('#saleDate').val(),
                        sale_no: $('#saleNo').val(),
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
                        const msg = currentSaleId > 0 ? 'Sale Updated Successfully' : res
                            .message;
                        Swal.fire('Success', msg, 'success').then(() => {
                            // Show bill if available
                            if (res.show_bill && res.bill_data) {
                                showBillModal(res.bill_data);
                            }
                            resetForm();
                        });
                    },

                    error: function(xhr) {
                        $('#saveSale').prop('disabled', false).text(currentSaleId > 0 ?
                            'Update' : 'Save');
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON
                            ?.message || 'Failed to save', 'error');
                    }
                });
            });

            $('#cancelBtn').on('click', resetForm);
        });

        // ── Helper Functions ──────────────────────────────────────────────

        function populateItemFields(item) {
            const productId = item.Prod_Id;
            const existingItem = itemsArray.find(existingItem => existingItem.item_id == productId);

            if (existingItem) {
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
            $('#rate').val(item.MRP);
            $('#discountPercent').val(item.Discount || 0);
            $('#cgstRate').val(cgst);
            $('#sgstRate').val(sgst);
            $('#quantity').val(1);
            $('#saleMrp').val(item.MRP);

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
            const yearStart = new Date('{{ session('year_start') }}');
            const yearEnd = new Date('{{ session('year_end') }}');

            if (saleDate > today) {
                Swal.fire('Error', 'Sale Date cannot be After Today', 'error');
                return false;
            }

            if (saleDate < yearStart || saleDate > yearEnd) {
                Swal.fire('Error', 'Sale Date must be between {{ session('year_start') }} and {{ session('year_end') }}',
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

            if (!$('#quantity').val() || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                return false;
            }
            if (qty > availableQty) {
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
            <td>${index + 1}</td>
            <td style="white-space:nowrap; min-width:100px;">${item.item_name}</td>
            <td>${item.quantity}</td>
            <td>${item.unit_name}</td>
            <td>${item.rate}</td>
            <td>${item.total_amount}</td>
            <td>${item.discount_percent}</td>
            <td>${item.discount_amount}</td>
            <td>${item.taxable_amount}</td>
            <td>${item.cgst_rate}</td>
            <td>${item.cgst_amount}</td>
            <td>${item.sgst_rate}</td>
            <td>${item.sgst_amount}</td>
            <td>${item.total_gst}</td>
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
            $('#itemSelected, #unitDisplay, #saleMrp').val('');
            $('#cgstRate, #sgstRate').val('');
            clearItemCalc();
            $('#addItemBtn').text('+ Add Item').data('editing', false);
        }

        function resetForm() {
            itemsArray = [];
            currentSaleId = 0;
            $('#saleDate, #saleNo, #refVoucherNo, #bankRemarks').val('');
            $('#partyId, #bankAccountId').val('').trigger('change');
            $('#bankSelectDiv, #bankRemarksDiv').hide();
            $('#transCash').prop('checked', true);
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
