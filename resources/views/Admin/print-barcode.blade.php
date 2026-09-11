@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        .product-row {
            cursor: pointer;
            transition: background 0.15s;
        }

        .product-row:hover {
            background: #f0f4ff;
        }

        .product-row.selected {
            background: #dbeafe;
            font-weight: 600;
        }

        #labelPreview {
            border: 2px dashed #6c757d;
            border-radius: 8px;
            padding: 16px;
            background: #fff;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-card {
            padding: 4px 8px;
            width: 220px;
            text-align: center;
            font-family: Arial, sans-serif;
            background: #fff;
        }

        .label-card .prod-code {
            font-size: 10px;
            color: #555;
            line-height: 1.2;
        }

        .btn-group {
            padding: 6px;
            background: #f8f9fa;
            border-radius: 10px;
            display: inline-flex;
        }

        .btn-group .btn {
            padding: 6px 16px;
            border-radius: 8px !important;
            margin: 0;
        }


        .btn-group .btn-outline-primary {
            border-color: transparent !important;
            background: transparent;
        }

        .btn-group .btn-primary {
            border: none;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header mb-3">
                <h6 class="ps-2">Print Barcode Labels</h6>
            </div>

            <div class="mb-3 px-3">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-tab active" data-mode="2">
                        <i class="fa fa-shopping-cart me-1"></i> Purchase
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-tab" data-mode="1">
                        <i class="fa fa-box me-1"></i> Opening Stock
                    </button>
                </div>
            </div>

            <div class="row g-3">

                {{-- LEFT: Table --}}
                <div class="col-lg-7">

                    <div id="openingStockSection" style="display:none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong>Print Barcode — Opening Stock</strong>
                            </div>
                            <div class="card-body" id="openingStockContainer">
                                <p class="text-center text-muted py-3">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <div id="purchaseSection">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong>Print Barcode — Purchase</strong>
                            </div>
                            <div class="card-body" id="purchaseContainer">
                                <p class="text-center text-muted py-3">Loading...</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT: Barcode Preview & Print --}}
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header bg-light py-2">
                            <strong>Barcode Preview & Print</strong>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">

                            <div>
                                <label class="form-label form-label-sm mb-1">No. of Labels</label>
                                <input type="number" class="form-control form-control-sm" id="labelQty" value="1"
                                    min="1">
                            </div>

                            <div>
                                <label class="form-label form-label-sm mb-1">Label Preview</label>
                                <div id="labelPreview">
                                    <span class="text-muted small">Click Print Barcode on any row to preview</span>
                                </div>
                            </div>

                            <div>
                                <label class="form-label form-label-sm mb-1">Barcode Format</label>
                                <select class="form-select form-select-sm" id="barcodeFormat">
                                    <option value="CODE128" selected>CODE128 (Recommended)</option>
                                </select>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <button class="btn btn-success w-100" id="printBtn" disabled>
                                    <i class="fa fa-print me-1"></i> Print Labels
                                </button>
                                <button class="btn btn-outline-secondary" id="clearBtn">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Purchase Items Modal --}}
    <div class="modal fade" id="purchaseItemsModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseItemsModalLabel">Purchase Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalItemsContainer"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        $(document).ready(function() {

            let dtTable = null;
            let dtPurchase = null;
            let dtModalItems = null;
            let purchaseItemsMap = {};
            let selectedProduct = null;

            const dtOptions = {
                pageLength: 10,
                ordering: true,
                sDom: 'fBtlpi',
                language: {
                    search: '',
                    searchPlaceholder: 'Search',
                    sLengthMenu: 'Row Per Page _MENU_ Entries',
                    info: '_START_ - _END_ of _TOTAL_ items',
                    paginate: {
                        next: '<i class="isax isax-arrow-right-1"></i>',
                        previous: '<i class="isax isax-arrow-left"></i>'
                    }
                },
                scrollX: false,
                scrollCollapse: false,
                responsive: false,
                autoWidth: false
            };

            function loadList(mode) {
                if (mode == 1) {
                    $('#openingStockSection').show();
                    $('#purchaseSection').hide();

                    if (dtTable) {
                        dtTable.destroy();
                        dtTable = null;
                    }
                    $('#openingStockContainer').html('<p class="text-center text-muted py-3">Loading...</p>');

                    $.get("{{ url('print-barcode/list') }}/" + mode, function(data) {
                        if (!data.length) {
                            $('#openingStockContainer').html(
                                '<p class="text-center text-muted py-3">No records found</p>');
                            return;
                        }
                        let rows = '';
                        $.each(data, function(i, row) {
                            rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${row.Prod_ShortNm}</td>
                        <td><span class="badge bg-secondary">${row.Prod_Code}</span></td>
                        <td>${siDate.toDisplay(row.InOut_Date)}</td>
                        <td>
                            <button class="btn btn-sm btn-success btn-print"
                                data-code="${row.Barcode}"
                                data-packdate="${row.Pack_Date ?? ''}"
                                data-name="${row.Prod_ShortNm}"
                                data-mrp="${row.MRP ?? 0}"
                                data-qty="${row.Quantity}">
                                <i class="fa fa-print me-1"></i> Print Barcode
                            </button>
                        </td>
                    </tr>`;
                        });
                        $('#openingStockContainer').html(`
                    <div class="table-responsive">
                        <table id="openingStockTable" class="table table-bordered table-hover table-sm w-100">
                            <thead class="thead-light">
                                <tr><th>#</th><th>Product Name</th><th>Product Code</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`);
                        dtTable = $('#openingStockTable').DataTable({
                            ...dtOptions,
                            columnDefs: [{
                                orderable: false,
                                targets: [0, 4]
                            }]
                        });
                    }).fail(function(xhr) {
                        $('#openingStockContainer').html('<p class="text-center text-danger py-3">' + (xhr
                            .responseJSON?.error ?? 'Failed to load') + '</p>');
                    });

                } else {
                    $('#openingStockSection').hide();
                    $('#purchaseSection').show();

                    if (dtPurchase) {
                        dtPurchase.destroy();
                        dtPurchase = null;
                    }
                    $('#purchaseContainer').html('<p class="text-center text-muted py-3">Loading...</p>');
                    purchaseItemsMap = {};

                    $.get("{{ url('print-barcode/list') }}/" + mode, function(data) {
                        if (!data.length) {
                            $('#purchaseContainer').html(
                                '<p class="text-center text-muted py-3">No records found</p>');
                            return;
                        }
                        let rows = '';
                        $.each(data, function(i, row) {
                            const items = typeof row.Item_Details === 'string' ?
                                JSON.parse(row.Item_Details) :
                                (row.Item_Details ?? []);
                            purchaseItemsMap[row.Trading_Id] = {
                                invoice: row.Invoice_No ?? '',
                                items: items
                            };
                            rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${row.Invoice_No ?? ''}</td>
                        <td>${siDate.toDisplay(row.Invoice_Date)}</td>
                        <td>${row.Party_Name ?? ''}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-view-items" data-trading="${row.Trading_Id}">
                                <i class="fa fa-eye me-1"></i> View
                            </button>
                        </td>
                    </tr>`;
                        });
                        $('#purchaseContainer').html(`
                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered table-hover table-sm w-100">
                            <thead class="thead-light">
                                <tr><th>Sl</th><th>Invoice No</th><th>Invoice Date</th><th>Party</th><th>Action</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`);
                        dtPurchase = $('#purchaseTable').DataTable({
                            ...dtOptions,
                            columnDefs: [{
                                orderable: false,
                                targets: [0, 4]
                            }]
                        });
                    }).fail(function(xhr) {
                        $('#purchaseContainer').html('<p class="text-center text-danger py-3">' + (xhr
                            .responseJSON?.error ?? 'Failed to load') + '</p>');
                    });
                }
            }

            loadList(2);

            // Auto-preview from query params (redirect from barcode-label)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('barcode')) {
                const mode = parseInt(urlParams.get('mode') ?? 2);
                if (mode === 1) {
                    $('.btn-tab').removeClass('btn-primary active').addClass('btn-outline-primary');
                    $('[data-mode="1"]').removeClass('btn-outline-primary').addClass('btn-primary active');
                    loadList(1);
                }
                selectedProduct = {
                    code:     urlParams.get('barcode'),
                    name:     urlParams.get('name') ?? '',
                    mrp:      urlParams.get('mrp') ?? 0,
                    packdate: urlParams.get('packdate') ?? ''
                };
                $('#labelQty').val(urlParams.get('qty') ?? 1);
                $('#printBtn').prop('disabled', false);
                renderPreview(selectedProduct);
            }

            $(document).on('click', '.btn-tab', function() {
                $('.btn-tab').removeClass('btn-primary active').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
                loadList($(this).data('mode'));
            });

            // Opening Stock — Print Barcode
            $(document).on('click', '.btn-print', function() {
                const productName = $(this).data('name');

                // Check if product name is longer than 30 characters
                if (productName && productName.length > 30) {
                    Swal.fire('Warning',
                        'Product name is too big (more than 30 characters). This may affect barcode design.',
                        'warning');
                    return;
                }

                selectedProduct = {
                    code: $(this).data('code'),
                    packdate: $(this).data('packdate') || '',
                    name: productName,
                    mrp: $(this).data('mrp') ?? 0
                };
                $('#labelQty').val($(this).data('qty') ?? 1);
                $('#printBtn').prop('disabled', false);
                renderPreview(selectedProduct);
            });

            // Purchase — View Items Modal
            $(document).on('click', '.btn-view-items', function() {
                const tradingId = $(this).data('trading');
                const entry = purchaseItemsMap[tradingId];
                if (!entry) return;

                if (dtModalItems) {
                    dtModalItems.destroy();
                    dtModalItems = null;
                }

                $('#purchaseItemsModalLabel').text('Invoice: ' + entry.invoice);
                let rows = '';
                $.each(entry.items, function(i, item) {
                    rows += `<tr>
                <td>${i + 1}</td>
                <td>${item.Prod_ShortNm}</td>
                <td><span class="badge bg-secondary">${item.Prod_Code}</span></td>
                <td>
                    <button class="btn btn-sm btn-success btn-print-purchase"
                        data-code="${item.Barcode}"
                        data-packdate="${item.Pack_Date ?? ''}"
                        data-name="${item.Prod_ShortNm}"
                        data-mrp="${item.MRP ?? 0}"
                        data-qty="${item.Quantity}">
                        <i class="fa fa-print me-1"></i> Print Barcode
                    </button>
                </td>
            </tr>`;
                });
                $('#modalItemsContainer').html(`
            <div class="table-responsive">
                <table id="modalItemsTable" class="table table-bordered table-hover table-sm w-100">
                    <thead class="thead-light">
                        <tr><th>Sl</th><th>Product Name</th><th>Product Code</th><th>Action</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`);
                dtModalItems = $('#modalItemsTable').DataTable({
                    ...dtOptions,
                    columnDefs: [{
                        orderable: false,
                        targets: [0, 3]
                    }]
                });
                $('#purchaseItemsModal').modal('show');
            });

            $('#purchaseItemsModal').on('hidden.bs.modal', function() {
                if (dtModalItems) {
                    dtModalItems.destroy();
                    dtModalItems = null;
                }
                $('#modalItemsContainer').empty();
            });

            // Purchase Modal — Print Barcode
            $(document).on('click', '.btn-print-purchase', function() {
                const productName = $(this).data('name');

                // Check if product name is longer than 30 characters
                if (productName && productName.length > 30) {
                    Swal.fire('Warning',
                        'Product name is too big For generating Barcode.(more than 30 characters).Take some small name',
                        'warning');
                    return;
                }

                selectedProduct = {
                    code: $(this).data('code'),
                    packdate: $(this).data('packdate') || '',
                    name: productName,
                    mrp: $(this).data('mrp') ?? 0
                };
                $('#labelQty').val($(this).data('qty') ?? 1);
                $('#printBtn').prop('disabled', false);
                renderPreview(selectedProduct);
                $('#purchaseItemsModal').modal('hide');
            });

            $('#barcodeFormat').on('change', function() {
                if (selectedProduct) renderPreview(selectedProduct);
            });

            function renderPreview(product) {
                const format = $('#barcodeFormat').val();
                const packDateLine = product.packdate && product.packdate.trim() !== '' ?
                    `<div class="prod-code">pack.date:${siDate.toDisplay(product.packdate)}</div>` :
                    '';

                $('#labelPreview').html(`
            <div class="label-card">
                <div style="font-size:10px; font-weight:600; line-height:1.2;">${product.name}</div>
                <div style="font-size:10px; line-height:1.2;">MRP: ₹${parseFloat(product.mrp).toFixed(2)}</div>
                <svg id="previewSvg"></svg>
                <div class="prod-code">${product.code}</div>
                ${packDateLine}
            </div>`);
                try {
                    JsBarcode('#previewSvg', product.code, {
                        format: format,
                        width: 1.5,
                        height: 45,
                        displayValue: false,
                        margin: 2
                    });
                } catch (e) {
                    $('#labelPreview').html(
                        '<div class="text-danger small text-center p-2"><i class="fa fa-exclamation-triangle"></i> Cannot generate barcode. Try CODE128.</div>'
                    );
                }
            }

            $('#printBtn').on('click', function() {
                if (!selectedProduct) return;

                const qty = parseInt($('#labelQty').val()) || 1;
                const format = $('#barcodeFormat').val();

                if (qty < 1) {
                    Swal.fire('Error', 'Label quantity must be at least 1', 'error');
                    return;
                }

                let svgString = '';
                try {
                    const ns = 'http://www.w3.org/2000/svg';
                    const tmpSvg = document.createElementNS(ns, 'svg');
                    tmpSvg.style.cssText = 'position:absolute;left:-9999px;top:-9999px;';
                    document.body.appendChild(tmpSvg);
                    JsBarcode(tmpSvg, selectedProduct.code, {
                        format: format,
                        width: 1.2,
                        height: 50,
                        displayValue: false,
                        margin: 1
                    });
                    svgString = tmpSvg.outerHTML;
                    document.body.removeChild(tmpSvg);
                } catch (e) {
                    Swal.fire('Error', 'Barcode generation failed. Try CODE128 format.', 'error');
                    return;
                }


                // Step 2: build labels array, pad start with empty if odd
                const packDateLine = selectedProduct.packdate && selectedProduct.packdate.trim() !== '' ?
                    `<div class="code">pack.date:${siDate.toDisplay(selectedProduct.packdate)}</div>` :
                    '';

                const singleLabel = `
            <div class="lbl">
                <div class="pname">${selectedProduct.name}</div>
                <div class="mrp">MRP: &#8377;${parseFloat(selectedProduct.mrp).toFixed(2)}</div>
                ${svgString}
                <div class="code">${selectedProduct.code}</div>
                ${packDateLine}
            </div>`;

                const emptyLabel = `<div class="lbl"></div>`;

                let labels = Array(qty).fill(singleLabel);
                if (labels.length % 2 !== 0) labels.unshift(emptyLabel);

                // Group into rows of 2, reverse so last row is always full
                let rows = [];
                for (let i = 0; i < labels.length; i += 2) {
                    rows.push(`<div class="row">${labels[i]}${labels[i + 1]}</div>`);
                }
                rows.reverse();

                // Step 3: full print document
                const printDoc = `<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: 103mm 25mm; margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #fff; }
    .row { width: 103mm; height: 25mm; display: flex; flex-direction: row; page-break-after: always; overflow: visible; }
    .row:last-child { page-break-after: avoid; }
    .lbl { width: 50mm; height: 25mm; text-align: center; padding-top: 6mm; padding-left: 1mm; padding-right: 1mm; margin-top: 0; }
    .lbl:last-child { border-right: none; }
    .lbl:nth-child(1) { padding-right: 2mm; }
    .lbl:nth-child(2) { padding-left: 2mm; }
    .pname { font-size: 6pt; font-weight: bold; font-family: Arial, sans-serif; line-height: 1.1; }
    .mrp   { font-size: 5.5pt; font-family: Arial, sans-serif; line-height: 1.1; }
    .lbl svg { width: 100%; height: 10mm; display: block; margin: 0 auto 0.3mm auto; }
    .code  { font-size: 5pt; font-family: Arial, sans-serif; line-height: 1.1; }
</style>
</head>
<body>${rows.join('')}</body>
</html>`;

                // Step 4: write into hidden iframe and print
                $('#printFrame').remove();
                const $iframe = $(
                    '<iframe id="printFrame" sandbox="allow-same-origin allow-scripts allow-modals"></iframe>'
                ).css({
                    position: 'fixed',
                    top: '-9999px',
                    left: '-9999px',
                    width: '103mm',
                    height: '25mm',
                    border: 'none'
                }).appendTo('body');

                const iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                iframeDoc.open();
                iframeDoc.write(printDoc);
                iframeDoc.close();

                // Step 5: wait for iframe to fully render then print
                setTimeout(function() {
                    $iframe[0].contentWindow.focus();
                    $iframe[0].contentWindow.print();
                    setTimeout(() => $('#printFrame').remove(), 2000);
                }, 500);
            });


            $('#clearBtn').on('click', function() {
                selectedProduct = null;
                $('#labelQty').val(1);
                $('#labelPreview').html(
                    '<span class="text-muted small">Click Print Barcode on any row to preview</span>');
                $('#printBtn').prop('disabled', true);
            });

        });
    </script>
@endpush
