{{-- Shared barcode / MRP stock picker (Counter Sale style) --}}
<div class="modal fade" id="barcodePickerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barcodePickerTitle">Select Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 align-items-end mb-3">
                    <div class="flex-grow-1">
                        <label class="form-label mb-1" for="barcodePickerSearch">Search</label>
                        <input type="text" class="form-control" id="barcodePickerSearch"
                            placeholder="Search by Sl, Barcode, Item Name, MRP or Qty" autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-primary" id="barcodePickerSearchBtn">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="barcodePickerClearBtn" title="Clear">
                        Clear
                    </button>
                </div>
                <div class="table-responsive barcode-picker-scroll">
                    <table class="table table-bordered table-hover table-sm mb-0" id="barcodePickerTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Barcode</th>
                                <th>Item Name</th>
                                <th class="text-end">MRP</th>
                                <th class="text-end">{{ $qtyColumnLabel ?? 'Available Qty' }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="barcodePickerEmpty" class="text-center text-muted py-2 d-none">No matching barcode found</div>
            </div>
        </div>
    </div>
</div>
