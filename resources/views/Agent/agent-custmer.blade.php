@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Agent Item Sale</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                    <input type="date" id="saleDate" class="form-control" min="{{ $year_start }}"
                        max="{{ min($year_end, date('Y-m-d')) }}" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select id="partyId" class="form-select">
                        <option value="">-- Select Customer --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->Party_Id }}">{{ $c->Party_Name }} - {{ $c->Party_Code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Scan Barcode</label>
                    <input type="text" id="barcodeInput" class="form-control"
                        placeholder="Scan or type barcode & press Enter" autocomplete="off">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Item Selected</label>
                    <input type="text" id="itemSelected" class="form-control bg-light" readonly
                        placeholder="Scan barcode to select item">
                    <input type="hidden" id="selectedItemId">
                    <input type="hidden" id="selectedHsnCode">
                    <input type="hidden" id="unitId">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" id="unitDisplay" class="form-control bg-light" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" id="quantity" class="form-control" step="0.01" min="0.01" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rate</label>
                    <input type="number" id="rate" class="form-control bg-light" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success w-100" id="addItemBtn">Add</button>
                </div>
            </div>

            <div class="row g-3 mb-2">
                <div class="col-md-12">
                    <label class="form-label">Trans Mode <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="transMode" id="transCash" value="1" checked>
                            <label class="form-check-label" for="transCash">Cash</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="transMode" id="transBank" value="2">
                            <label class="form-check-label" for="transBank">Upi</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="transMode" id="transCredit" value="3">
                            <label class="form-check-label" for="transCredit">Credit</label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="qrCodeSection" class="qr-section" style="display:none;">
                <label class="form-label">Scan QR to Pay</label>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=UPI_PAYMENT_LINK"
                    alt="QR Code">
            </div>

            {{-- Hidden calc fields --}}
            <input type="hidden" id="totalAmount">
            <input type="hidden" id="netAmount">
            <input type="hidden" id="discountPercent">
            <input type="hidden" id="discountAmount">
            <input type="hidden" id="taxableAmount">
            <input type="hidden" id="cgstRate">
            <input type="hidden" id="cgstAmount">
            <input type="hidden" id="sgstRate">
            <input type="hidden" id="sgstAmount">
            <input type="hidden" id="totalGst">
            <input type="hidden" id="saleMrp">
            <input type="hidden" id="itemSaleDate">
            <input type="hidden" id="summaryTotalAmount">
            <input type="hidden" id="totalDiscountAmount">
            <input type="hidden" id="totalTaxableAmount">
            <input type="hidden" id="totalGSTAmount">
            <input type="hidden" id="roundOff">
            <input type="hidden" id="finalNetAmount">
        </div>
    </div>

    {{-- Items Table --}}
    <div id="itemsSection" class="d-none">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Items List</h6>
                <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Item</th>
                                <th>Qty - Unit</th>
                                <th>Rate</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-3">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSale">Save</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

{{-- Bill Modal --}}
<div class="modal fade" id="billModal" tabindex="-1">
    <div class="modal-dialog modal-sm" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Bill</h5>
                <button type="button" data-bs-dismiss="modal"
                    style="background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;padding:0 0.5rem;">&times;</button>
            </div>
            <div class="modal-body" id="billContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/plugins/select2/css/select2.min.css') }}">
<script src="{{ asset('agenttemplate/assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const baseUrl   = "{{ url('/') }}";
    const csrfToken = "{{ csrf_token() }}";
    const yearStart = "{{ $year_start }}";
    const yearEnd   = "{{ $year_end }}";
    const today     = "{{ date('Y-m-d') }}";

    let itemsArray    = [];
    let currentSaleId = 0;

    $(document).ready(function () {

        $('#barcodeInput').on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); lookupBarcode(); }
        });

        $('#quantity').on('input', calculateAmounts);

        $('input[name="transMode"]').on('change', function () {
            if ($(this).val() === '2') {
                $('#qrCodeSection').show();
            } else {
                $('#qrCodeSection').hide();
            }
        });

        $('#addItemBtn').on('click', function () {
            if (!validateItemForm()) return;
            itemsArray.push({
                item_id:          $('#selectedItemId').val(),
                hsn_code:         $('#selectedHsnCode').val() || '',
                item_name:        $('#itemSelected').val(),
                quantity:         $('#quantity').val(),
                unit_id:          $('#unitId').val(),
                unit_name:        $('#unitDisplay').val(),
                rate:             $('#rate').val(),
                total_amount:     $('#totalAmount').val(),
                discount_percent: $('#discountPercent').val() || 0,
                discount_amount:  $('#discountAmount').val() || 0,
                taxable_amount:   $('#taxableAmount').val(),
                cgst_rate:        $('#cgstRate').val(),
                cgst_amount:      $('#cgstAmount').val(),
                sgst_rate:        $('#sgstRate').val(),
                sgst_amount:      $('#sgstAmount').val(),
                total_gst:        $('#totalGst').val(),
                net_amount:       $('#netAmount').val(),
                sale_mrp:         $('#saleMrp').val() || 0,
                item_sale_date:   $('#itemSaleDate').val() || null,
            });
            renderItemsTable();
            clearItemSelection();
            $('#barcodeInput').val('').focus();
            $('#saleDate').prop('disabled', true);
        });

        $(document).on('click', '.removeItem', function () {
            itemsArray.splice($(this).data('index'), 1);
            renderItemsTable();
        });

        $('#saveSale').on('click', function () {
            if (!$('#saleDate').val())   { Swal.fire('Error', 'Sale Date is required', 'error'); return; }
            if (!$('#partyId').val())    { Swal.fire('Error', 'Please select a Customer', 'error'); return; }
            if (!itemsArray.length)      { Swal.fire('Error', 'Please add at least one item', 'error'); return; }

            $(this).prop('disabled', true).text('Saving...');

            const totAmt     = itemsArray.reduce((s, i) => s + parseFloat(i.total_amount), 0);
            const discAmt    = itemsArray.reduce((s, i) => s + parseFloat(i.discount_amount || 0), 0);
            const taxableAmt = itemsArray.reduce((s, i) => s + parseFloat(i.taxable_amount), 0);
            const totGst     = itemsArray.reduce((s, i) => s + parseFloat(i.total_gst), 0);
            const netBeforeRound = parseFloat((taxableAmt + totGst).toFixed(2));
            const rounded    = Math.round(netBeforeRound);
            const roundOff   = parseFloat((rounded - netBeforeRound).toFixed(2));

            $.ajax({
                url: baseUrl + '/agent/customer-return/save',
                type: 'POST',
                data: {
                    _token:     csrfToken,
                    sale_id:    currentSaleId,
                    sale_date:  $('#saleDate').val(),
                    party_id:   $('#partyId').val(),
                    trans_mode: $('input[name="transMode"]:checked').val(),
                    disc_amt:   discAmt.toFixed(2),
                    round_off:  roundOff.toFixed(2),
                    net_amt:    rounded.toFixed(2),
                    items:      itemsArray,
                },
                success: function (res) {
                    Swal.fire('Success', res.message, 'success').then(() => {
                        if (res.show_bill && res.bill_data) showBillModal(res.bill_data);
                        resetForm();
                    });
                },
                error: function (xhr) {
                    $('#saveSale').prop('disabled', false).text('Save');
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save', 'error');
                }
            });
        });

        $('#partyId').select2({ placeholder: '-- Select Customer --', allowClear: true });
        $('#cancelBtn').on('click', resetForm);
    });

    function lookupBarcode() {
        const barcode  = $('#barcodeInput').val().trim();
        const saleDate = $('#saleDate').val();
        if (!barcode) return;
        if (!saleDate) { Swal.fire('Error', 'Please select sale date first', 'error'); return; }

        $.get(baseUrl + '/agent/customer-return/barcode', { barcode, sale_date: saleDate })
            .done(function (item) { populateItemFields(item); })
            .fail(function () {
                Swal.fire('Not Found', 'No item found for barcode: ' + barcode, 'warning');
                $('#barcodeInput').select();
            });
    }

    function populateItemFields(item) {
        const existing = itemsArray.find(i => i.item_id == item.Prod_Id);
        if (existing) {
            Swal.fire('Warning', item.Prod_ShortNm + ' is already added', 'warning');
            $('#barcodeInput').val('').focus();
            return;
        }

        const gst  = item.Gst_Details ? (typeof item.Gst_Details === 'string' ? JSON.parse(item.Gst_Details) : item.Gst_Details) : {};
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
        $('#saleMrp').val(item.MRP);
        $('#itemSaleDate').val(item.Pack_Date || '');
        $('#quantity').val(1);
        calculateAmounts();

        $('#barcodeInput').data('available-qty', item.Avil_Qnty);
        if (item.Avil_Qnty <= 0) {
            Swal.fire('Warning', 'This product is out of stock!', 'warning');
        }

        $('#saleDate').prop('disabled', true);
        $('#quantity').focus();
    }

    function calculateAmounts() {
        const qty      = parseFloat($('#quantity').val()) || 0;
        const rate     = parseFloat($('#rate').val()) || 0;
        const discPct  = parseFloat($('#discountPercent').val()) || 0;
        const cgstRate = parseFloat($('#cgstRate').val()) || 0;
        const sgstRate = parseFloat($('#sgstRate').val()) || 0;

        const totalAmt   = qty * rate;
        const discAmt    = totalAmt * (discPct / 100);
        const taxableAmt = totalAmt - discAmt;
        const cgstAmt    = taxableAmt * (cgstRate / 100);
        const sgstAmt    = taxableAmt * (sgstRate / 100);
        const totalGst   = cgstAmt + sgstAmt;

        $('#totalAmount').val(totalAmt.toFixed(2));
        $('#discountAmount').val(discAmt.toFixed(2));
        $('#taxableAmount').val(taxableAmt.toFixed(2));
        $('#cgstAmount').val(cgstAmt.toFixed(2));
        $('#sgstAmount').val(sgstAmt.toFixed(2));
        $('#totalGst').val(totalGst.toFixed(2));
        $('#netAmount').val((taxableAmt + totalGst).toFixed(2));
    }

    function validateItemForm() {
        if (!$('#saleDate').val())       { Swal.fire('Error', 'Sale Date is required', 'error'); return false; }
        if (!$('#selectedItemId').val()) { Swal.fire('Error', 'Please scan an item', 'error'); return false; }

        const qty = parseFloat($('#quantity').val());
        if (!qty || qty <= 0) { Swal.fire('Error', 'Quantity must be > 0', 'error'); return false; }

        const availableQty = parseFloat($('#barcodeInput').data('available-qty'));
        if (availableQty <= 0) { Swal.fire('Error', 'This product is out of stock!', 'error'); return false; }
        if (qty > availableQty) {
            Swal.fire('Error', `You cannot sell this product as available quantity = ${availableQty}`, 'error');
            return false;
        }
        return true;
    }

    function renderItemsTable() {
        let html = '', totAmt = 0, taxable = 0, discAmt = 0, totGst = 0;

        itemsArray.forEach((item, idx) => {
            totAmt  += parseFloat(item.total_amount) || 0;
            taxable += parseFloat(item.taxable_amount) || 0;
            discAmt += parseFloat(item.discount_amount || 0);
            totGst  += parseFloat(item.total_gst) || 0;

            html += `<tr>
                <td>${idx + 1}</td>
                <td>${item.item_name}</td>
                <td>${item.quantity} - ${item.unit_name}</td>
                <td>${item.rate}</td>
                <td>${item.total_amount}</td>
                <td>
                    <button class="btn btn-danger btn-sm removeItem" data-index="${idx}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        $('#itemsTableBody').html(html);

        if (itemsArray.length) {
            const netBeforeRound = parseFloat((taxable + totGst).toFixed(2));
            const rounded  = Math.round(netBeforeRound);
            const roundOff = parseFloat((rounded - netBeforeRound).toFixed(2));

            $('#summaryTotalAmount').val(totAmt.toFixed(2));
            $('#totalDiscountAmount').val(discAmt.toFixed(2));
            $('#totalTaxableAmount').val(taxable.toFixed(2));
            $('#totalGSTAmount').val(totGst.toFixed(2));
            $('#roundOff').val(roundOff.toFixed(2));
            $('#finalNetAmount').val(rounded.toFixed(2));
            $('#itemsSection').removeClass('d-none');
        } else {
            $('#itemsSection').addClass('d-none');
        }
    }

    function clearItemSelection() {
        $('#selectedItemId, #selectedHsnCode, #unitId, #itemSelected, #unitDisplay').val('');
        $('#rate, #quantity, #totalAmount, #netAmount').val('');
        $('#discountPercent, #discountAmount, #taxableAmount').val('');
        $('#cgstRate, #cgstAmount, #sgstRate, #sgstAmount, #totalGst, #saleMrp, #itemSaleDate').val('');
    }

    function showBillModal(billData) {
        let itemsHtml = '', itemTotal = 0;
        if (billData.items) {
            billData.items.forEach((item, idx) => {
                const amt = parseFloat(item.amount || 0);
                itemTotal += amt;
                itemsHtml += `<tr>
                    <td>${idx + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${parseFloat(item.qty).toFixed(2)}</td>
                    <td>${parseFloat(item.rate).toFixed(2)}</td>
                    <td>${amt.toFixed(2)}</td>
                </tr>`;
            });
        }
        const discount  = parseFloat(billData.Discount || 0);
        const netAmount = parseFloat(billData.Tot_Amount || itemTotal);

        $('#billContent').html(`
            <div style="font-family:Arial,sans-serif;font-size:12px;width:105mm;margin:0 auto;">
                <div style="text-align:center;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:8px;">
                    <strong style="font-size:15px;">Sale Invoice</strong>
                </div>
                <p><strong>Invoice No:</strong> ${billData.Invoice_No || ''}</p>
                <p><strong>Date:</strong> ${siDate.toDisplay(billData.Invoice_Date)}</p>
                <p><strong>Customer:</strong> ${billData.Cust_Name || ''}</p>
                <table style="width:100%;border-collapse:collapse;font-size:11px;margin:8px 0;">
                    <thead>
                        <tr style="background:#f0f0f0;">
                            <th style="border:1px solid #000;padding:3px;">#</th>
                            <th style="border:1px solid #000;padding:3px;">Item</th>
                            <th style="border:1px solid #000;padding:3px;">Qty</th>
                            <th style="border:1px solid #000;padding:3px;">Rate</th>
                            <th style="border:1px solid #000;padding:3px;">Amt</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
                <div style="border-top:2px solid #000;padding-top:6px;">
                    <p><strong>Item Total:</strong> ${itemTotal.toFixed(2)}</p>
                    ${discount > 0 ? `<p><strong>Discount:</strong> ${discount.toFixed(2)}</p>` : ''}
                    <p style="font-size:14px;font-weight:bold;"><strong>Net Amount:</strong> ${netAmount.toFixed(2)}</p>
                </div>
                <div style="text-align:center;margin-top:12px;border-top:1px dashed #000;padding-top:8px;font-size:10px;">
                    <p>Thank you for your business!</p>
                </div>
            </div>
        `);

        new bootstrap.Modal(document.getElementById('billModal')).show();
    }

    function resetForm() {
        itemsArray    = [];
        currentSaleId = 0;
        $('#saleDate').prop('disabled', false).val(today);
        $('#partyId').val('').trigger('change');
        $('#transCash').prop('checked', true);
        $('#qrCodeSection').hide();
        $('#barcodeInput').val('');
        $('#itemsTableBody').html('');
        $('#itemsSection').addClass('d-none');
        $('#summaryTotalAmount, #totalDiscountAmount, #totalTaxableAmount, #totalGSTAmount, #roundOff, #finalNetAmount').val('');
        $('#saveSale').prop('disabled', false).text('Save');
        clearItemSelection();
        $('#barcodeInput').focus();
    }
</script>

<style>
    #partyId + .select2-container .select2-selection__arrow { display: none !important; }
    #partyId + .select2-container .select2-selection--single { padding-right: 8px; }

    #qrCodeSection {
        display: none;
        margin-top: 10px;
        padding: 10px 0;
    }
    #qrCodeSection label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
    }
    #qrCodeSection img {
        border: 1px solid #ddd;
        padding: 5px;
        display: block;
    }

    @media print {
        body * { visibility: hidden; }
        #billContent, #billContent * { visibility: visible; }
        #billContent { position: absolute; left: 0; top: 0; width: 105mm; }
        @page { size: A6 portrait; margin: 5mm; }
        .modal-header, .modal-footer { display: none !important; }
    }
</style>
@endpush
