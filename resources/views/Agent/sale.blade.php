@extends('AgentDashboard.Layouts.layout')

@push('style')
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/plugins/select2/css/select2.min.css') }}">
<style>
    #partyId + .select2-container .select2-selection__arrow { display: none !important; }
    #partyId + .select2-container .select2-selection--single { padding-right: 8px; }
    #qrCodeSection { display:none; margin-top:10px; padding:10px 0; }
    #qrCodeSection label { display:block; margin-bottom:6px; font-weight:500; }
    #qrCodeSection img { border:1px solid #ddd; padding:5px; display:block; }

    @media (min-width: 1200px) {
        .modal-xl-custom { max-width: 1400px; }
    }
    #itemPickerTable tbody tr { cursor: pointer; }
    #itemPickerTable tbody tr:hover { background-color: #e8f4ff; }

    .bill-container { font-family:Arial,sans-serif; font-size:12px; line-height:1.4; max-width:350px; margin:0 auto; }
    .bill-header { text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:10px; }
    .bill-header h3 { margin:0; font-size:16px; font-weight:bold; }
    .bill-header p { margin:5px 0 0 0; font-size:12px; }
    .bill-details { margin-bottom:10px; }
    .bill-details p { margin:3px 0; display:flex; justify-content:space-between; }
    .items-table { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
    .items-table th, .items-table td { border:1px solid #000; padding:4px 2px; text-align:left; }
    .items-table th { background-color:#f0f0f0; font-weight:bold; }
    .items-table .text-right { text-align:right; }
    .total-section { border-top:2px solid #000; padding-top:8px; margin-top:10px; }
    .total-section p { margin:3px 0; display:flex; justify-content:space-between; }
    .total-section .final-total { font-weight:bold; font-size:14px; border-top:1px solid #000; padding-top:5px; margin-top:5px; }
    .bill-footer { text-align:center; margin-top:15px; font-size:10px; border-top:1px dashed #000; padding-top:8px; }
</style>
@endpush

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
                    <label class="form-label">Select Item</label>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" class="form-control"
                            placeholder="Scan barcode or search item" autocomplete="off">
                        <button class="btn btn-primary" type="button" id="itemSearchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Item Selected</label>
                    <input type="text" id="itemSelected" class="form-control bg-light" readonly
                          placeholder="Select item">
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

            <div id="qrCodeSection" style="display:none;">
                <label class="form-label">Scan QR to Pay</label>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=UPI_PAYMENT_LINK" alt="QR Code">
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
<div class="modal fade" id="billModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="billContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printBill()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Item Picker Modal --}}
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
<script src="{{ asset('agenttemplate/assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const baseUrl   = "{{ url('/') }}";
    const csrfToken = "{{ csrf_token() }}";
    const today     = "{{ date('Y-m-d') }}";

    let itemsArray    = [];
    let currentSaleId = 0;
    let itemPickerDT  = null;

    $(document).ready(function () {

        $('#barcodeInput').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = $(this).val().trim();
                if (val) lookupBarcode();
                else openSaleItemPicker([]);
            }
        });

        $('#itemSearchBtn').on('click', function (e) {
            e.preventDefault();
            const code = $('#barcodeInput').val().trim();
            if (!code) {
                openSaleItemPicker([]);
                return;
            }
            $.get(baseUrl + '/agent/sale/items', {
                code: code,
                cat_id: 0,
                sub_cat_id: 0
            }, function (data) {
                openSaleItemPicker(data, code);
            }).fail(function () {
                Swal.fire('Error', 'Failed to load items', 'error');
            });
        });

        $('#modalCateId').on('change', function () {
            const catId = parseInt($(this).val()) || 0;
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            clearSaleItemPickerTable();
            if (!catId) return;
            $.get(baseUrl + '/agent/sale/subcats', { cat_id: catId }, function (subs) {
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
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
            $.get(baseUrl + '/agent/sale/items', {
                cat_id: catId,
                sub_cat_id: subCatId,
                code: code
            }, function (data) {
                renderSaleItemPickerTable(data);
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
            $('#itemPickerLoader').hide();
            $('#itemPickerTableWrap').hide();
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
        });

        $(document).on('click', '#itemPickerTable tbody tr', function () {
            const prodId = $(this).data('id');
            const saleDate = $('#saleDate').val();
            if (!prodId) return;
            if (!saleDate) {
                Swal.fire('Error', 'Please select sale date first', 'error');
                return;
            }
            $.get(baseUrl + '/agent/sale/item-info', {
                prod_id: prodId,
                sale_date: saleDate
            }).done(function (item) {
                $('#itemPickerModal').modal('hide');
                populateItemFields(item);
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
            });
        });

        $('#quantity').on('input', calculateAmounts);

        $('input[name="transMode"]').on('change', function () {
            $('#qrCodeSection').toggle($(this).val() === '2');
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
                discount_percent: $('#discountPercent').val() ,
                discount_amount:  $('#discountAmount').val() ,
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
            if (!$('#saleDate').val())  { Swal.fire('Error', 'Sale Date is required', 'error'); return; }
            if (!$('#partyId').val())   { Swal.fire('Error', 'Please select a Customer', 'error'); return; }
            if (!itemsArray.length)     { Swal.fire('Error', 'Please add at least one item', 'error'); return; }

            $(this).prop('disabled', true).text('Saving...');

            const totAmt     = itemsArray.reduce((s, i) => s + parseFloat(i.total_amount), 0);
            const discAmt    = itemsArray.reduce((s, i) => s + parseFloat(i.discount_amount || 0), 0);
            const taxableAmt = itemsArray.reduce((s, i) => s + parseFloat(i.taxable_amount), 0);
            const totGst     = itemsArray.reduce((s, i) => s + parseFloat(i.total_gst), 0);
            const netBeforeRound = parseFloat((taxableAmt + totGst).toFixed(2));
            const rounded    = Math.round(netBeforeRound);
            const roundOff   = parseFloat((rounded - netBeforeRound).toFixed(2));

            $.ajax({
                url:  baseUrl + '/agent/sale/save',
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
                        if (res.show_bill && res.bill_data) {
                            showBillModal(res.bill_data);
                            $('#billModal').one('hidden.bs.modal', function () {
                                resetForm();
                            });
                        } else {
                            resetForm();
                        }
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

        $.get(baseUrl + '/agent/sale/barcode', { barcode, sale_date: saleDate })
            .done(function (item) { populateItemFields(item); })
            .fail(function () {
                $.get(baseUrl + '/agent/sale/items', {
                    code: barcode,
                    cat_id: 0,
                    sub_cat_id: 0
                }, function (data) {
                    if (data && data.length) {
                        openSaleItemPicker(data, barcode);
                    } else {
                        Swal.fire('Not Found', 'No item found for: ' + barcode, 'warning');
                        $('#barcodeInput').select();
                    }
                }).fail(function () {
                    Swal.fire('Not Found', 'No item found for barcode: ' + barcode, 'warning');
                    $('#barcodeInput').select();
                });
            });
    }

    function openSaleItemPicker(data, code) {
        if (!$('#saleDate').val()) {
            Swal.fire('Error', 'Please select sale date first', 'error');
            return;
        }
        $('#itemPickerLoader').hide();
        clearSaleItemPickerTable();
        $('#modalCateId').val('0');
        $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
        $('#modalSearchInput').val(code || '');
        $('#itemPickerModal').modal('show');
        if (data && data.length > 0) {
            $('#itemPickerLoader').show();
            renderSaleItemPickerTable(data);
        }
    }

    function clearSaleItemPickerTable() {
        $('#itemPickerLoader').hide();
        $('#itemPickerTableWrap').hide();
        if (itemPickerDT) {
            itemPickerDT.destroy();
            itemPickerDT = null;
        }
        $('#itemPickerTable tbody').html('');
    }

    function renderSaleItemPickerTable(data) {
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

        const availableQty = parseFloat(item.Avil_Qnty);
        $('#barcodeInput').data('available-qty', availableQty);
        if (!isFinite(availableQty) || availableQty <= 0) {
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
        if (!$('#selectedItemId').val()) { Swal.fire('Error', 'Please select an item', 'error'); return false; }
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
        document.getElementById('billContent').innerHTML = generateBillHTML(billData);
        new bootstrap.Modal(document.getElementById('billModal')).show();
    }

    function generateBillHTML(billData) {
        let itemsHtml = '';
        if (billData.items && Array.isArray(billData.items)) {
            billData.items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.item_name || 'N/A'}</td>
                        <td class="text-right">${item.qty}</td>
                        <td class="text-right">${parseFloat(item.rate).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(item.amount).toFixed(2)}</td>
                    </tr>`;
            });
        }
        const discount    = parseFloat(billData.Discount || 0);
        const totalAmount = parseFloat(billData.Tot_Amount || 0);

        return `
            <div class="bill-container">
                <div class="bill-header">
                    <h3>${billData.Cust_Name || ''}</h3>
                    <p>Sale Invoice</p>
                </div>
                <div class="bill-details">
                    <p><strong>Invoice No:</strong> <span>${billData.Invoice_No || 'N/A'}</span></p>
                    <p><strong>Date:</strong> <span>${siDate.toDisplay(billData.Invoice_Date) || 'N/A'}</span></p>
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
                    <tbody>${itemsHtml}</tbody>
                </table>
                <div class="total-section">
                    ${discount > 0 ? `<p><strong>Discount:</strong> <span>${discount.toFixed(2)}</span></p>` : ''}
                    <p class="final-total"><strong>Net Amount:</strong> <span>${totalAmount.toFixed(2)}</span></p>
                </div>
                <div class="bill-footer">
                    <p>Thank you for your business!</p>
                    <p>Visit Again!</p>
                </div>
            </div>`;
    }

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
        doc.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            * { margin:0; padding:0; box-sizing:border-box; }
            @page { size:90mm auto; margin:0; }
             html,body { margin:0; padding:0; width:100%; display:flex; justify-content:center; }
            .bill-container { width:76mm; margin:0 auto; }
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
        </style></head><body>${billHtml}</body></html>`);
        doc.close();
        iframe.contentWindow.onload = function () { iframe.contentWindow.print(); };
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
@endpush
