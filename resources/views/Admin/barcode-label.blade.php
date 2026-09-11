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

/* KEY FIX: remove only border gap, not color */
.btn-group .btn-outline-primary {
    border-color: transparent !important; /* hide border only */
    background: transparent;
}

/* keep original Bootstrap active blue */
.btn-group .btn-primary {
    border: none;
}
    
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header mb-3">
            <h6 class="ps-2"> Auto Generate SKU Barcode</h6>
        </div>

        <div class="mb-3">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary btn-tab active" data-mode="2">
                    <i class="fa fa-shopping-cart me-1"></i> Purchase
                </button>
                <button type="button" class="btn btn-outline-primary btn-tab" data-mode="1">
                    <i class="fa fa-box me-1"></i> Opening Stock
                </button>
            </div>
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
<script>
$(document).ready(function () {

    let dtTable      = null;
    let dtPurchase   = null;
    let dtModalItems = null;
    let purchaseItemsMap      = {};
    let reloadPurchaseOnClose = false;

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

    function loadPending(mode) {
        if (mode == 1) {
            $('#openingStockSection').show();
            $('#purchaseSection').hide();

            if (dtTable) { dtTable.destroy(); dtTable = null; }
            $('#openingStockContainer').html('<p class="text-center text-muted py-3">Loading...</p>');

            $.get("{{ url('barcode-label/pending') }}/" + mode, function (data) {
                if (!data.length) {
                    $('#openingStockContainer').html('<p class="text-center text-muted py-3">No pending items</p>');
                    return;
                }
                let rows = '';
                $.each(data, function (i, row) {
                    rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${row.Prod_ShortNm}</td>
                        <td><span class="badge bg-secondary">${row.Prod_Code}</span></td>
                        <td>${siDate.toDisplay(row.InOut_Date)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-generate"
                                data-stock="${row.Stock_Id}"
                                data-code="${row.Prod_Code}"
                                data-name="${row.Prod_ShortNm}"
                                data-date="${row.InOut_Date ?? ''}">
                                <i class="fa fa-barcode me-1"></i> Generate Barcode
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
                    return;
                }
                let rows = '';
                $.each(data, function (i, row) {
                    const items = typeof row.Item_Details === 'string'
                        ? JSON.parse(row.Item_Details)
                        : (row.Item_Details ?? []);
                    purchaseItemsMap[row.Trading_Id] = { invoice: row.Invoice_No ?? '', items: items };
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
                                <tr><th>#</th><th>Invoice No</th><th>Invoice Date</th><th>Party</th><th>Action</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`);
                dtPurchase = $('#purchaseTable').DataTable({
                    ...dtOptions,
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

    $(document).on('click', '.btn-generate', function () {
        const $btn      = $(this);
        const stockId   = $btn.data('stock');
        const prodCode  = $btn.data('code');
        const prodName  = $btn.data('name');
        const stockDate = $btn.data('date');

        Swal.fire({
            title: 'Are You Sure You Want To Generate Barcode?',
            html: `<span class="text-muted">Product:</span> <strong>${prodName}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: "{{ url('barcode-label/generate') }}",
                method: 'POST',
                data: { stock_id: stockId, stock_date: stockDate, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    dtTable.row($btn.closest('tr')).remove().draw();
                    if (dtTable.data().count() === 0) {
                        dtTable.destroy(); dtTable = null;
                        $('#openingStockContainer').html('<p class="text-center text-muted py-3">No pending items</p>');
                    }
                    Swal.fire({ icon: 'success', title: 'Barcode Generated Successfully', timer: 1500, showConfirmButton: false, width: '400px' })
                        .then(() => {
                            const params = new URLSearchParams({
                                barcode:  res.barcode,
                                name:     prodName,
                                mrp:      res.mrp ?? 0,
                                packdate: stockDate ?? '',
                                qty:      1
                            });
                            window.location.href = "{{ url('print-barcode') }}?" + params.toString();
                        });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error ?? 'Failed to generate barcode', 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-barcode me-1"></i> Generate Barcode');
                }
            });
        });
    });

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
                <td>
                    <button class="btn btn-sm btn-primary btn-generate-purchase"
                        data-stock="${item.Stock_Id}"
                        data-name="${item.Prod_ShortNm}"
                        data-code="${item.Prod_Code}"
                        data-trading="${tradingId}">
                        <i class="fa fa-barcode me-1"></i> Generate Barcode
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
            columnDefs: [{ orderable: false, targets: [0, 3] }]
        });
        $('#purchaseItemsModal').modal('show');
    });

    $('#purchaseItemsModal').on('hidden.bs.modal', function () {
        if (dtModalItems) { dtModalItems.destroy(); dtModalItems = null; }
        $('#modalItemsContainer').empty();
        if (reloadPurchaseOnClose) {
            reloadPurchaseOnClose = false;
            loadPending(2);
        }
    });

    $(document).on('click', '.btn-generate-purchase', function () {
        const $btn      = $(this);
        const stockId   = $btn.data('stock');
        const prodName  = $btn.data('name');
        const prodCode  = $btn.data('code');
        const tradingId = $btn.data('trading');
        const today     = new Date().toISOString().split('T')[0];

        Swal.fire({
            title: 'Are You Sure You Want To Generate Barcode?',
            html: `<span class="text-muted">Product:</span> <strong>${prodName}</strong><br>`,
                  
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: "{{ url('barcode-label/generate') }}",
                method: 'POST',
                data: { stock_id: stockId, stock_date: today, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    dtModalItems.row($btn.closest('tr')).remove().draw();
                    if (purchaseItemsMap[tradingId]) {
                        purchaseItemsMap[tradingId].items = purchaseItemsMap[tradingId].items.filter(
                            it => it.Stock_Id != stockId
                        );
                    }
                    Swal.fire({ icon: 'success', title: 'Barcode Generated Successfully', timer: 1500, showConfirmButton: false, width: '400px' })
                        .then(() => {
                            const params = new URLSearchParams({
                                barcode:  res.barcode,
                                name:     prodName,
                                mrp:      res.mrp ?? 0,
                                packdate: today ?? '',
                                qty:      1,
                                mode:     2
                            });
                            window.location.href = "{{ url('print-barcode') }}?" + params.toString();
                        });
                    if (dtModalItems.data().count() === 0) {
                        reloadPurchaseOnClose = true;
                        $('#purchaseItemsModal').modal('hide');
                    }
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error ?? 'Failed to generate barcode', 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-barcode me-1"></i> Generate Barcode');
                }
            });
        });
    });

});
</script>
@endpush
