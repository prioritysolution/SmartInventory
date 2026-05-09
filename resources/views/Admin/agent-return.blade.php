@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Agent Indent</h6>

            <!-- Form Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <!-- Date & Agent -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="indentDate" min="{{ session('year_start') }}"
                                max="{{ session('year_end') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Agent<span class="text-danger">*</span></label>
                            <select class="form-select" id="agentId">
                                <option value="">Select Agent</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->Agent_Id }}">{{ $agent->Agent_Name }}
                                        ({{ $agent->Agent_Code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Product Barcode<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="productBarcode"
                                placeholder="Enter barcode and press Enter" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="productName" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">MRP</label>
                            <input type="number" class="form-control" id="mrp" step="0.01" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Available Qty</label>
                            <input type="number" class="form-control" id="availableQty" step="0.01" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Pack Date</label>
                            <input type="date" class="form-control" id="packDate" readonly>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Quantity<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" step="0.01" min="0.01">
                        </div>
                    </div>

                    <!-- Add Button -->
                    <div class="text-end">
                        <button class="btn btn-success" onclick="addItemRow()">+ Add Product</button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="itemsTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Barcode</th>
                                    <th>Product Name</th>
                                    <th>MRP</th>
                                    <th>Available Qty</th>
                                    <th>Pack Date</th>
                                    <th>Quantity</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-secondary" onclick="resetForm()">Cancel</button>
                        <button class="btn btn-primary" id="saveBtn">Save Indent</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let indentItems = [];
        let dataTable;

        $(document).ready(function() {

            dataTable = $('#itemsTable').DataTable();

            // Only fetch product details on Enter key press
            $('#productBarcode').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode.length >= 8) {
                        fetchProductDetails(barcode);
                    } else {
                        Swal.fire('Error', 'Please enter a valid barcode', 'error');
                    }
                }
            });

            $('#indentDate').on('change', function() {
                if ($('#productName').val()) {
                    clearProductFields();
                    Swal.fire('Info', 'Product data cleared. Please re-enter barcode for the new date.',
                        'info');
                }
            });

            // Validate date range when date is changed
            let dateValidationTimeout;


            $('#saveBtn').on('click', saveIndent);
        });

        function fetchProductDetails(barcode) {

            if (!validateIndentDate()) {
                return;
            }
            const indentDate = $('#indentDate').val();

            if (!indentDate) {
                Swal.fire('Error', 'Please select indent date first', 'error');
                $('#indentDate').focus();
                return;
            }

            // Show loading state

            $('#productName, #mrp, #availableQty, #packDate').val('');

            // Call the actual API using the stored procedure
            $.ajax({
                url: "{{ route('agent-indent.get-product-info') }}",
                method: 'POST',
                data: {
                    barcode: barcode,
                    date: indentDate,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const product = response.data;
                        $('#productName').val(product.Prod_ShortNm);
                        $('#mrp').val(product.MRP);
                        $('#availableQty').val(product.Avil_Qnty);
                        $('#packDate').val(product.Pack_Date);

                        // Store additional data for saving
                        $('#productBarcode').data('prod-id', product.Prod_Id);
                        $('#productBarcode').data('unit-id', product.Unit_Id);

                        // Set max quantity to available quantity
                        $('#quantity').attr('max', product.Avil_Qnty);

                        if (product.Avil_Qnty > 0) {
                            $('#quantity').focus();
                        } else {
                            Swal.fire('Warning', 'This product is out of stock!', 'warning');
                        }

                        // Visual feedback - success (green border only)
                        $('#productBarcode').addClass('is-valid');
                        setTimeout(() => $('#productBarcode').removeClass('is-valid'), 2000);
                    } else {
                        // Clear fields if product not found
                        $('#productName, #mrp, #availableQty, #packDate').val('');
                        $('#productBarcode').addClass('is-invalid');
                        setTimeout(() => $('#productBarcode').removeClass('is-invalid'), 2000);

                        Swal.fire({
                            title: 'Product Not Found',
                            text: response.message || 'Invalid Code Entered !!',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    // Clear fields on error
                    $('#productName, #mrp, #availableQty, #packDate').val('');
                    $('#productBarcode').addClass('is-invalid');
                    setTimeout(() => $('#productBarcode').removeClass('is-invalid'), 2000);

                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch product details. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function addItemRow() {

            if (!validateIndentDate()) {
                return;
            }

            const barcode = $('#productBarcode').val().trim();
            const productName = $('#productName').val().trim();
            const mrp = parseFloat($('#mrp').val()) || 0;
            const availableQty = parseFloat($('#availableQty').val()) || 0;
            const packDate = $('#packDate').val();
            const qty = parseFloat($('#quantity').val());
            const prodId = $('#productBarcode').data('prod-id');
            const unitId = $('#productBarcode').data('unit-id');

            // Validation
            if (!barcode) {
                Swal.fire('Error', 'Product barcode is required', 'error');
                $('#productBarcode').focus();
                return;
            }
            if (!productName || productName === 'Loading...') {
                Swal.fire('Error', 'Product name is required. Press Enter after entering barcode.', 'error');
                $('#productBarcode').focus();
                return;
            }
            if (!qty || qty <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
                $('#quantity').focus();
                return;
            }
            if (qty > availableQty) {
                Swal.fire('Error', `Requested quantity (${qty}) exceeds available quantity (${availableQty})`, 'error');
                $('#quantity').focus();
                return;
            }

            // Check for duplicate barcode
            const existingIndex = indentItems.findIndex(item => item.barcode === barcode);
            if (existingIndex !== -1) {
                Swal.fire('Error', 'This product is already added', 'error');
                return;
            }

            // Add new item
            indentItems.push({
                barcode: barcode,
                prod_id: prodId,
                unit_id: unitId,
                product_name: productName,
                mrp: mrp,
                available_qty: availableQty,
                pack_date: packDate || '',
                quantity: qty
            });
          $('#indentDate').prop('disabled', true);
            renderItemsTable();
            clearProductFields();
            $('#productBarcode').focus(); // Focus back to barcode for next entry
        }

        function removeItemRow(index) {
            Swal.fire({
                title: 'Delete Product',
                text: 'Are you sure you want to remove this product?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    indentItems.splice(index, 1);
                    renderItemsTable();
                }
            });
        }

        function renderItemsTable() {
            // Clear existing data
            dataTable.clear();

            // Add new data
            indentItems.forEach(function(item, i) {
                dataTable.row.add([
                    i + 1,
                    `<small>${item.barcode}</small>`,
                    item.product_name,
                    `₹${item.mrp.toFixed(2)}`,
                    `<span class="badge ${item.available_qty > 0 ? 'bg-success' : 'bg-danger'}">${item.available_qty}</span>`,
                    item.pack_date || '-',
                    item.quantity,
                    `<button class="btn btn-danger btn-sm" onclick="removeItemRow(${i})" title="Remove Product">
                        <i class="fas fa-trash"></i>
                    </button>`
                ]);
            });

            // Redraw the table
            dataTable.draw();
        }

        function clearProductFields() {
            $('#productBarcode, #productName, #mrp, #availableQty, #packDate, #quantity').val('');
            $('#productBarcode').removeClass('is-valid is-invalid');
            $('#productBarcode').removeData('prod-id unit-id');
            $('#quantity').removeAttr('max');
        }

        function saveIndent() {
            if (!$('#indentDate').val()) {
                Swal.fire('Error', 'Date is required', 'error');
                $('#indentDate').focus();
                return;
            }
            if (!$('#agentId').val()) {
                Swal.fire('Error', 'Please select Agent', 'error');
                $('#agentId').focus();
                return;
            }
            if (indentItems.length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            $('#saveBtn').prop('disabled', true).text('Saving...');

            // Prepare data for saving
            const saveData = {
                indent_date: $('#indentDate').val(),
                agent_id: $('#agentId').val(),
                items: indentItems.map(item => ({
                    prod_id: item.prod_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity
                })),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('agent-indent.store') }}",
                method: 'POST',
                data: saveData,
                success: function(response) {
                    Swal.fire('Success!', response.message || 'Agent indent saved successfully', 'success')
                        .then(() => {
                            resetForm();
                        });
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.error || 'Failed to save agent indent';
                    Swal.fire('Error', errorMsg, 'error');
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false).text('Save Indent');
                }
            });
        }

        function validateIndentDate() {
            const selectedDate = $('#indentDate').val();
            const yearStart = "{{ session('year_start') }}";
            const yearEnd = "{{ session('year_end') }}";

            if (!selectedDate) {
                Swal.fire('Error', 'Please select indent date first', 'error');
                $('#indentDate').focus();
                return false;
            }

            if (yearStart && yearEnd && (selectedDate < yearStart || selectedDate > yearEnd)) {
                Swal.fire({
                    title: 'Invalid Date',
                    text: `Date must be between ${yearStart} and ${yearEnd}`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('#indentDate').focus();
                return false;
            }

            return true;
        }


        function resetForm() {
            indentItems = [];
            $('#indentDate, #agentId').val('');
              $('#indentDate').prop('disabled', false);
            $('#agentId').trigger('change');
            clearProductFields();
            $('#saveBtn').prop('disabled', false).text('Save Indent');
            dataTable.clear().draw();
        }
    </script>
@endpush
