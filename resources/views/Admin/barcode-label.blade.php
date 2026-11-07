@extends('Dashboard.Layouts.layout')

@push('style')
<style>
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
            <h6 class="ps-2">Auto Generate SKU Barcode</h6>
        </div>

        <div class="mb-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary btn-tab active" data-mode="2">
                    <i class="fa fa-shopping-cart me-1"></i> Purchase
                </button>
                <button type="button" class="btn btn-outline-primary btn-tab" data-mode="1">
                    <i class="fa fa-box me-1"></i> Opening Stock
                </button>
            </div>
            <button type="button" class="btn btn-success" id="btnGenerateAll" disabled>
                <i class="fa fa-magic me-1"></i> Generate All Pending Barcodes
            </button>
        </div>

        <div id="openingStockSection" style="display:none;">
            <div class="card">
                <div class="card-header bg-light py-2">
                    <strong>Pending Barcode — Opening Stock</strong>
                </div>
                <div class="card-body" id="openingStockContainer">
                    <p class="text-center text-muted py-3">Loading...</p>
                </div>
            </div>
        </div>

        <div id="purchaseSection">
            <div class="card">
                <div class="card-header bg-light py-2">
                    <strong>Pending Barcode — Purchase</strong>
                </div>
                <div class="card-body" id="purchaseContainer">
                    <p class="text-center text-muted py-3">Loading...</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Purchase Items Modal --}}
<div class="modal fade" id="purchaseItemsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
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
<script>
$(document).ready(function () {

    let dtTable      = null;
    let dtPurchase   = null;
    let dtModalItems = null;
    let purchaseItemsMap      = {};
    let reloadPurchaseOnClose = false;
    let activeMode = 2;
    let pendingCount = 0;

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
        scrollX: false, scrollCollapse: false, responsive: false, autoWidth: false
    };

    function updateGenerateAllBtn(count) {
        pendingCount = count;
        if (count > 0) {
            $('#btnGenerateAll').prop('disabled', false)
                .html('<i class="fa fa-magic me-1"></i> Generate All Pending Barcodes (' + count + ')');
        } else {
            $('#btnGenerateAll').prop('disabled', true)
                .html('<i class="fa fa-magic me-1"></i> No Pending Barcodes');
        }
    }

    function loadPending(mode) {
        activeMode = mode;
        updateGenerateAllBtn(0);

        if (mode == 1) {
            $('#openingStockSection').show();
            $('#purchaseSection').hide();

            if (dtTable) { dtTable.destroy(); dtTable = null; }
            $('#openingStockContainer').html('<p class="text-center text-muted py-3">Loading...</p>');

            $.get("{{ url('barcode-label/pending') }}/" + mode, function (data) {
                if (!data.length) {
                    $('#openingStockContainer').html('<p class="text-center text-muted py-3">No pending items</p>');
                    updateGenerateAllBtn(0);
                    return;
                }
                updateGenerateAllBtn(data.length);
                let rows = '';
                $.each(data, function (i, row) {
                    rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${row.Prod_ShortNm}</td>
                        <td><span class="badge bg-secondary">${row.Prod_Code}</span></td>
                        <td>${siDate.toDisplay(row.InOut_Date)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-generate-one"
                                data-stock="${row.Stock_Id}"
                                data-name="${row.Prod_ShortNm}"
                                data-date="${row.InOut_Date ?? ''}">
                                <i class="fa fa-barcode me-1"></i> Generate
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
                    columnDefs: [{ orderable: false, targets: [0, 4] }]
                });
            }).fail(function (xhr) {
                $('#openingStockContainer').html('<p class="text-center text-danger py-3">' + (xhr.responseJSON?.error ?? 'Failed to load') + '</p>');
            });

        } else {
            $('#openingStockSection').hide();
            $('#purchaseSection').show();

            if (dtPurchase) { dtPurchase.destroy(); dtPurchase = null; }
            $('#purchaseContainer').html('<p class="text-center text-muted py-3">Loading...</p>');
            purchaseItemsMap = {};

            $.get("{{ url('barcode-label/pending') }}/" + mode, function (data) {
                if (!data.length) {
                    $('#purchaseContainer').html('<p class="text-center text-muted py-3">No pending items</p>');
                    updateGenerateAllBtn(0);
                    return;
                }
                // count total pending items across all invoices
                let totalItems = 0;
                let rows = '';
                $.each(data, function (i, row) {
                    const items = typeof row.Item_Details === 'string'
                        ? JSON.parse(row.Item_Details)
                        : (row.Item_Details ?? []);
                    totalItems += items.length;
                    purchaseItemsMap[row.Trading_Id] = { invoice: row.Invoice_No ?? '', items: items };
                    rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${row.Invoice_No ?? ''}</td>
                        <td>${siDate.toDisplay(row.Invoice_Date)}</td>
                        <td>${row.Party_Name ?? ''}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-view-items" data-trading="${row.Trading_Id}">
                                <i class="fa fa-eye me-1"></i> View Items
                            </button>
                        </td>
                    </tr>`;
                });
                updateGenerateAllBtn(totalItems);
                $('#purchaseContainer').html(`
                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered table-hover table-sm w-100">
                            <thead class="thead-light">
                                <tr><th>#</th><th>Invoice No</th><th>Invoice Date</th><th>Party</th><th>Action</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`);
                dtPurchase = $('#purchaseTable').DataTable({
                    ...dtOptions,
                    order: [],
                    columnDefs: [{ orderable: false, targets: [0, 4] }]
                });
            }).fail(function (xhr) {
                $('#purchaseContainer').html('<p class="text-center text-danger py-3">' + (xhr.responseJSON?.error ?? 'Failed to load') + '</p>');
            });
        }
    }

    loadPending(2);

    $(document).on('click', '.btn-tab', function () {
        $('.btn-tab').removeClass('btn-primary active').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
        loadPending($(this).data('mode'));
    });

    // Generate All
    $('#btnGenerateAll').on('click', function () {
        if (pendingCount === 0) return;
        Swal.fire({
            title: 'Generate All Pending Barcodes?',
            text: 'This will assign barcodes to all ' + pendingCount + ' pending entries. Same product + same MRP will share the same barcode.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate All',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const $btn = $('#btnGenerateAll');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Processing...');
            $.ajax({
                url: "{{ url('barcode-label/generate-all') }}",
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                    loadPending(activeMode);
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error ?? 'Failed to generate barcodes', 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-magic me-1"></i> Generate All Pending Barcodes');
                }
            });
        });
    });

    // Individual Generate — Opening Stock
    $(document).on('click', '.btn-generate-one', function () {
        const $btn      = $(this);
        const stockId   = $btn.data('stock');
        const prodName  = $btn.data('name');
        const stockDate = $btn.data('date');

        Swal.fire({
            title: 'Generate Barcode?',
            html: `<span class="text-muted">Product:</span> <strong>${prodName}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate',
            confirmButtonColor: '#0d6efd',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: "{{ url('barcode-label/generate-one') }}",
                method: 'POST',
                data: { stock_id: stockId, stock_date: stockDate, _token: '{{ csrf_token() }}' },
                success: function () {
                    $btn.closest('tr').fadeOut(300, function () {
                        if (dtTable) {
                            dtTable.row($(this)).remove().draw();
                            const remaining = dtTable.data().count();
                            updateGenerateAllBtn(remaining);
                            if (remaining === 0) {
                                dtTable.destroy(); dtTable = null;
                                $('#openingStockContainer').html('<p class="text-center text-muted py-3">No pending items</p>');
                            }
                        }
                    });
                    Swal.fire({ icon: 'success', title: 'Barcode Generated', timer: 1200, showConfirmButton: false, width: '350px' });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error ?? 'Failed to generate barcode', 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-barcode me-1"></i> Generate');
                }
            });
        });
    });

    // View purchase items modal
    $(document).on('click', '.btn-view-items', function () {
        const tradingId = $(this).data('trading');
        const entry     = purchaseItemsMap[tradingId];
        if (!entry) return;

        if (dtModalItems) { dtModalItems.destroy(); dtModalItems = null; }

        $('#purchaseItemsModalLabel').text('Invoice: ' + entry.invoice);
        let rows = '';
        $.each(entry.items, function (i, item) {
            rows += `<tr>
                <td>${i + 1}</td>
                <td>${item.Prod_ShortNm}</td>
                <td><span class="badge bg-secondary">${item.Prod_Code}</span></td>
                <td>${item.MRP ?? 0}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-generate-purchase"
                        data-stock="${item.Stock_Id}"
                        data-name="${item.Prod_ShortNm}"
                        data-trading="${tradingId}">
                        <i class="fa fa-barcode me-1"></i> Generate
                    </button>
                </td>
            </tr>`;
        });
        $('#modalItemsContainer').html(`
            <div class="table-responsive">
                <table id="modalItemsTable" class="table table-bordered table-hover table-sm w-100">
                    <thead class="thead-light">
                        <tr><th>Sl</th><th>Product Name</th><th>Product Code</th><th>MRP</th><th>Action</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`);
        dtModalItems = $('#modalItemsTable').DataTable({
            ...dtOptions,
            columnDefs: [{ orderable: false, targets: [0, 4] }]
        });
        $('#purchaseItemsModal').modal('show');
    });

    // Individual Generate — Purchase item inside modal
    $(document).on('click', '.btn-generate-purchase', function () {
        const $btn      = $(this);
        const stockId   = $btn.data('stock');
        const prodName  = $btn.data('name');
        const tradingId = $btn.data('trading');
        const today     = new Date().toISOString().split('T')[0];

        Swal.fire({
            title: 'Generate Barcode?',
            html: `<span class="text-muted">Product:</span> <strong>${prodName}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate',
            confirmButtonColor: '#0d6efd',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: "{{ url('barcode-label/generate-one') }}",
                method: 'POST',
                data: { stock_id: stockId, stock_date: today, _token: '{{ csrf_token() }}' },
                success: function () {
                    // remove from modal table
                    if (dtModalItems) {
                        dtModalItems.row($btn.closest('tr')).remove().draw();
                    }
                    // remove from map
                    if (purchaseItemsMap[tradingId]) {
                        purchaseItemsMap[tradingId].items = purchaseItemsMap[tradingId].items.filter(
                            it => it.Stock_Id != stockId
                        );
                        // update pending count
                        let total = 0;
                        Object.values(purchaseItemsMap).forEach(e => total += e.items.length);
                        updateGenerateAllBtn(total);
                    }
                    Swal.fire({ icon: 'success', title: 'Barcode Generated', timer: 1200, showConfirmButton: false, width: '350px' });
                    // if no more items in this invoice, close modal and reload
                    if (dtModalItems && dtModalItems.data().count() === 0) {
                        reloadPurchaseOnClose = true;
                        $('#purchaseItemsModal').modal('hide');
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error ?? 'Failed to generate barcode', 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-barcode me-1"></i> Generate');
                }
            });
        });
    });

    $('#purchaseItemsModal').on('hidden.bs.modal', function () {
        if (dtModalItems) { dtModalItems.destroy(); dtModalItems = null; }
        $('#modalItemsContainer').empty();
        if (reloadPurchaseOnClose) {
            reloadPurchaseOnClose = false;
            loadPending(2);
        }
    });

});
</script>
@endpush
