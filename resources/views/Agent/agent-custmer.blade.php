@extends('AgentDashboard.Layouts.layout')

@push('style')
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/plugins/select2/css/select2.min.css') }}">
<style>
    @media (min-width: 1200px) {
        .modal-xl-custom { max-width: 1400px; }
    }
    #itemPickerTable tbody tr { cursor: pointer; }
    #itemPickerTable tbody tr:hover { background-color: #e8f4ff; }

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

    .text-right { text-align: right; }
    .text-center { text-align: center; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Customer Return</h6>
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
                    <select class="form-control" id="rateSelect" style="display:none;"></select>
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
            <input type="hidden" id="maxReturnQty">
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
<div class="modal fade" id="billModal" tabindex="-1" role="dialog" aria-labelledby="billModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="billModalLabel">Return Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="billContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printBill()">
                    <i class="fa fa-print"></i> Print
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
    const yearStart = "{{ $year_start }}";
    const yearEnd   = "{{ $year_end }}";
    const today     = "{{ date('Y-m-d') }}";
    const ORG_NAME = @json(session('org_name', ''));
    const BRANCH_NAME = @json(session('branch_name', ''));
    const AGENT_NAME = @json(session('agent_name', ''));
    const AGENT_CODE = @json(session('agent_code', ''));

    let itemsArray    = [];
    let currentSaleId = 0;
    let itemPickerDT  = null;
    let returnableReady = false;

    function ensureCustomer() {
        if (!$('#partyId').val()) {
            Swal.fire('Error', 'Please select a Customer before choosing an item', 'error');
            return false;
        }
        return true;
    }

    function qtyOnCurrentBill(prodId) {
        return itemsArray.reduce(function (sum, row) {
            return String(row.item_id) === String(prodId)
                ? sum + (parseFloat(row.quantity) || 0)
                : sum;
        }, 0);
    }

    function applyReturnableLimit(options) {
        options = options || {};
        const partyId = $('#partyId').val();
        const prodId = $('#selectedItemId').val();
        returnableReady = false;
        if (!partyId || !prodId) return;
        $.get(baseUrl + '/agent/customer-return/returnable-qty', {
            party_id: partyId,
            prod_id: prodId,
            exclude_id: currentSaleId || 0
        }, function (row) {
            let remaining = parseFloat(row.Remaining_Qty) || 0;
            remaining -= qtyOnCurrentBill(prodId);
            if (remaining < 0) remaining = 0;
            remaining = parseFloat(remaining.toFixed(2));
            const sold = parseFloat(row.Sold_Qty) || 0;
            $('#maxReturnQty').val(remaining);
            $('#quantity').attr('max', remaining);
            if (remaining <= 0) {
                returnableReady = false;
                if (sold <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Not sold to this customer',
                        text: 'This product was not sold to the selected customer, so it cannot be returned.'
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No returnable quantity',
                        text: 'All sold quantity of this item has already been returned for this customer.'
                    });
                }
                if (!options.keepItem) {
                    clearItemSelection();
                }
                return;
            }
            returnableReady = true;
            const currentQty = parseFloat($('#quantity').val());
            if (!currentQty || currentQty <= 0 || currentQty > remaining) {
                $('#quantity').val(remaining);
            }
            calculateAmounts();
            if (options.focusQty) {
                $('#quantity').focus();
            }
        }).fail(function (xhr) {
            returnableReady = false;
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load returnable quantity', 'error');
            if (!options.keepItem) {
                clearItemSelection();
            }
        });
    }

    $(document).ready(function () {

        $('#barcodeInput').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = $(this).val().trim();
                if (val) lookupBarcode();
                else openReturnItemPicker([]);
            }
        });

        $('#itemSearchBtn').on('click', function (e) {
            e.preventDefault();
            if (!ensureCustomer()) return;
            const code = $('#barcodeInput').val().trim();
            if (!code) {
                openReturnItemPicker([]);
                return;
            }
            $.get(baseUrl + '/agent/customer-return/items', {
                code: code,
                cat_id: 0,
                sub_cat_id: 0
            }, function (data) {
                resolveReturnItemsOrOpenPicker(data, code);
            }).fail(function () {
                openReturnItemPicker([], code);
            });
        });

        $('#modalCateId').on('change', function () {
            const catId = parseInt($(this).val()) || 0;
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            clearReturnItemPickerTable();
            if (!catId) return;
            $.get(baseUrl + '/agent/customer-return/subcats', { cat_id: catId }, function (subs) {
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
            $.get(baseUrl + '/agent/customer-return/items', {
                cat_id: catId,
                sub_cat_id: subCatId,
                code: code
            }, function (data) {
                renderReturnItemPickerTable(data);
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
            if (!ensureCustomer()) return;
            if (!saleDate) {
                Swal.fire('Error', 'Please select date first', 'error');
                return;
            }
            $.get(baseUrl + '/agent/customer-return/item-info', {
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

        $('#rateSelect').on('change', function () {
            const selected = $(this).val();
            $('#rate').val(selected);
            $('#saleMrp').val(selected);
            calculateAmounts();
        });

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

    function pickUniqueItem(data, code) {
        if (!data || !data.length) return null;
        const needle = String(code).toLowerCase();
        const exact = data.filter(item => String(item.Prod_Code || '').toLowerCase() === needle);
        if (exact.length === 1) return exact[0];
        if (data.length === 1) return data[0];
        return null;
    }

    function loadReturnItemByProdId(prodId) {
        const saleDate = $('#saleDate').val();
        if (!ensureCustomer()) return;
        if (!saleDate) { Swal.fire('Error', 'Please select date first', 'error'); return; }
        $.get(baseUrl + '/agent/customer-return/item-info', {
            prod_id: prodId,
            sale_date: saleDate
        }).done(function (item) {
            populateItemFields(item);
        }).fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load item details', 'error');
        });
    }

    function resolveReturnItemsOrOpenPicker(data, code) {
        const unique = pickUniqueItem(data, code);
        if (unique) {
            loadReturnItemByProdId(unique.Prod_Id);
            return;
        }
        openReturnItemPicker(data || [], code);
    }

    function lookupBarcode() {
        const barcode  = $('#barcodeInput').val().trim();
        const saleDate = $('#saleDate').val();
        if (!barcode) return;
        if (!saleDate) { Swal.fire('Error', 'Please select date first', 'error'); return; }
        if (!ensureCustomer()) return;

        $.get(baseUrl + '/agent/customer-return/barcode', { barcode, sale_date: saleDate })
            .done(function (item) { populateItemFields(item); })
            .fail(function () {
                $.get(baseUrl + '/agent/customer-return/items', {
                    code: barcode,
                    cat_id: 0,
                    sub_cat_id: 0
                }, function (data) {
                    resolveReturnItemsOrOpenPicker(data, barcode);
                }).fail(function () {
                    openReturnItemPicker([], barcode);
                });
            });
    }

    function openReturnItemPicker(data, code) {
        if (!$('#saleDate').val()) {
            Swal.fire('Error', 'Please select date first', 'error');
            return;
        }
        if (!ensureCustomer()) return;
        $('#itemPickerLoader').hide();
        clearReturnItemPickerTable();
        $('#modalCateId').val('0');
        $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
        $('#modalSearchInput').val(code || '');
        $('#itemPickerModal').modal('show');
        if (data && data.length > 0) {
            $('#itemPickerLoader').show();
            renderReturnItemPickerTable(data);
        }
    }

    function clearReturnItemPickerTable() {
        $('#itemPickerLoader').hide();
        $('#itemPickerTableWrap').hide();
        if (itemPickerDT) {
            itemPickerDT.destroy();
            itemPickerDT = null;
        }
        $('#itemPickerTable tbody').html('');
    }

    function renderReturnItemPickerTable(data) {
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

    function parseSaleRateValue(r) {
        if (r == null) return NaN;
        if (typeof r === 'object') return parseFloat(r.MRP ?? r.mrp ?? r.Rate ?? r.rate);
        return parseFloat(r);
    }

    function applySaleRateOptions(saleRates, currentMrp) {
        const rates = [];
        (Array.isArray(saleRates) ? saleRates : []).forEach(function (r) {
            const n = parseSaleRateValue(r);
            if (n > 0 && rates.indexOf(n.toFixed(2)) === -1) {
                rates.push(n.toFixed(2));
            }
        });
        if (rates.length > 1) {
            $('#rate').hide();
            $('#rateSelect').empty().css('display', 'block');
            rates.forEach(function (r) {
                $('#rateSelect').append('<option value="' + r + '">' + r + '</option>');
            });
            const current = (parseFloat(currentMrp) > 0 ? parseFloat(currentMrp).toFixed(2) : rates[0]);
            const selected = rates.indexOf(current) >= 0 ? current : rates[0];
            $('#rateSelect').val(selected);
            $('#rate').val(selected);
            $('#saleMrp').val(selected);
        } else {
            resetRateSelect();
            $('#rate').val(currentMrp);
            $('#saleMrp').val(currentMrp);
        }
    }

    function loadSaleRatesForItem(prodId, currentMrp) {
        if (!prodId) return;
        $.get(baseUrl + '/agent/customer-return/sale-rates', { prod_id: prodId })
            .done(function (rows) {
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
        const existing = itemsArray.find(function (i) {
            return String(i.item_id) === String(item.Prod_Id)
                && parseFloat(i.rate) === parseFloat(item.MRP);
        });
        const saleRates = Array.isArray(item.Sale_Rates) ? item.Sale_Rates
            : (Array.isArray(item.sale_rates) ? item.sale_rates : []);
        if (existing && saleRates.length === 1) {
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
        applySaleRateOptions(saleRates, item.MRP);
        loadSaleRatesForItem(item.Prod_Id, item.MRP);
        $('#discountPercent').val(item.Discount || 0);
        $('#cgstRate').val(cgst);
        $('#sgstRate').val(sgst);
        $('#itemSaleDate').val(item.Pack_Date || '');
        $('#quantity').val(1);
        calculateAmounts();
        applyReturnableLimit({ focusQty: true });

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
        const rate = parseFloat($('#rate').val());
        const alreadyAdded = itemsArray.find(function (row) {
            return String(row.item_id) === String($('#selectedItemId').val())
                && parseFloat(row.rate) === rate;
        });
        if (alreadyAdded) {
            Swal.fire('Error', 'This item at this rate is already added', 'error');
            return false;
        }
        if (!returnableReady) {
            Swal.fire('Error', 'Wait until returnable quantity is loaded, or this item has no remaining qty', 'error');
            return false;
        }
        const maxReturnQty = parseFloat($('#maxReturnQty').val());
        if (!isNaN(maxReturnQty) && maxReturnQty <= 0) {
            Swal.fire('Error', 'No returnable quantity for this item against the selected customer', 'error');
            return false;
        }
        if (!isNaN(maxReturnQty) && qty > maxReturnQty) {
            Swal.fire('Error', `Return quantity cannot exceed remaining sold qty (${maxReturnQty})`, 'error');
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
        $('#cgstRate, #cgstAmount, #sgstRate, #sgstAmount, #totalGst, #saleMrp, #itemSaleDate, #maxReturnQty').val('');
        $('#quantity').removeAttr('max');
        returnableReady = false;
        resetRateSelect();
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
                <h3>${billData.Org_Name || ORG_NAME || 'Smart Inventory'}</h3>
                ${(billData.Branch_Name || BRANCH_NAME) ? `<p>${billData.Branch_Name || BRANCH_NAME}</p>` : ''}
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
@page { size: 58mm 400mm; margin: 2mm 1.5mm; }
html, body { margin: 0; padding: 0; width: 58mm; }
body { width: 58mm; }
.bill-container { width: 54mm; max-width: 54mm; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; line-height: 1.25; word-wrap: break-word; }
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

    function showBillModal(billData) {
        if (!billData) {
            Swal.fire('Error', 'No bill data available', 'error');
            return;
        }
        document.getElementById('billContent').innerHTML = generateBillHTML(billData);
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
    #partyId + .select2-container { width: 100% !important; }
    #partyId + .select2-container .select2-selection--single {
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 8px;
        display: flex;
        align-items: center;
        border: 1px solid #e9ecef;
    }
    #partyId + .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 4px;
        padding-right: 8px;
    }
    #partyId + .select2-container .select2-selection__arrow { display: none !important; }

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
</style>
@endpush
