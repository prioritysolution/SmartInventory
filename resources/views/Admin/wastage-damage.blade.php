@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        .scrollable-table {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: auto;
        }

        @@media (min-width: 1200px) {
            .modal-xl-custom {
                max-width: 1400px;
            }
        }

        .calc-label {
            background-color: #f8f9fa;
            font-weight: 500;
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
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
                <h6 class="mb-0">Wastage / Damage Entry</h6>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="searchBtn" style="width: 100px;">
                    <svg aria-hidden="true" class="me-2" width="18" height="18" viewBox="0 0 18 18">
                        <path
                            d="m18 16.5-5.14-5.18h-.35a7 7 0 1 0-1.19 1.19v.35L16.5 18zM12 7A5 5 0 1 1 2 7a5 5 0 0 1 10 0">
                        </path>
                    </svg> Search
                </button>
            </div>

            <div class="row mb-3 top-entry-row">
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <h6>Entry Info</h6>
                        <input type="hidden" id="damageId" value="0">
                        <input type="hidden" id="vouchId" value="0">
                        <div class="mb-3">
                            <label class="form-label">Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="entryDate"
                                min="{{ session('year_start') }}"
                                max="{{ min(date('Y-m-d'), session('year_end')) }}"
                                value="{{ min(date('Y-m-d'), session('year_end')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Particular<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="particulars" maxlength="200"
                                placeholder="Reason / particular" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="section-card item-entry h-100">
                        <h6>Item Section</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product Code<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="productCodeInput"
                                        placeholder="Enter product code" autocomplete="new-password" maxlength="20">
                                    <button class="btn btn-primary" type="button" id="productSearchBtn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Item Selected</label>
                                <input type="text" class="form-control calc-label" id="itemSelected" readonly
                                    placeholder="Search product to pick an item">
                                <input type="hidden" id="selectedItemId">
                                <input type="hidden" id="unitId">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control calc-label" id="unitDisplay" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Qty<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" step="1" min="1"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Purchase Rate</label>
                                <input type="number" class="form-control calc-label" id="purchaseRate" readonly
                                    step="0.01">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Amt</label>
                                <input type="text" class="form-control calc-label" id="totalAmount" readonly>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Available Qty</label>
                                <input type="text" class="form-control calc-label" id="availQty" readonly>
                            </div>
                            <div class="col-md-4 mb-3 ms-md-auto">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-success w-100 d-block" id="addItemBtn">+ Add
                                    Item</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
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
                                        <th>Purchase Rate</th>
                                        <th>Total Amt</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No items added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="section-card h-100">
                        <h6 class="ms-3">Damage Summary</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Total Amount</th>
                                <td><input type="text" class="form-control calc-label fw-bold" id="netTotal"
                                        readonly value="0.00"></td>
                            </tr>
                        </table>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

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

    <div class="modal fade" id="searchModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Damage / Wastage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="searchFrom"
                                min="{{ session('year_start') }}" max="{{ session('year_end') }}"
                                value="{{ session('year_start') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="searchTo"
                                min="{{ session('year_start') }}" max="{{ session('year_end') }}"
                                value="{{ min(date('Y-m-d'), session('year_end')) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="searchGo">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Particular</th>
                                    <th class="text-end">Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="searchBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Use filters above to search</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemsArray = [];
        let itemPickerDT = null;
        const yearStart = @json(session('year_start'));
        const yearEnd = @json(session('year_end'));

        function fmt(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function calcLine() {
            const qty = parseInt($('#quantity').val(), 10) || 0;
            const rate = parseFloat($('#purchaseRate').val()) || 0;
            $('#totalAmount').val(qty && rate ? fmt(qty * rate) : '');
        }

        function clearItem() {
            $('#productCodeInput, #itemSelected, #unitDisplay, #quantity, #purchaseRate, #availQty, #totalAmount').val('');
            $('#selectedItemId, #unitId').val('');
        }

        function renderItems() {
            if (!itemsArray.length) {
                $('#itemsBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">No items added</td></tr>');
                $('#netTotal').val('0.00');
                return;
            }
            let html = '';
            let tot = 0;
            itemsArray.forEach(function(item, i) {
                tot += parseFloat(item.total_amount) || 0;
                html += `<tr>
                    <td>${i + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${fmt(item.quantity)}</td>
                    <td>${item.unit_name || ''}</td>
                    <td>${fmt(item.rate)}</td>
                    <td>${fmt(item.total_amount)}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger removeItem" data-i="${i}">Remove</button></td>
                </tr>`;
            });
            $('#itemsBody').html(html);
            $('#netTotal').val(fmt(tot));
        }

        function applyItem(item) {
            const date = $('#entryDate').val();
            $.get("{{ route('wastage-damage.item-info') }}", {
                prod_id: item.Prod_Id,
                date: date
            }, function(info) {
                $('#productCodeInput').val(item.Prod_Code);
                $('#selectedItemId').val(item.Prod_Id);
                $('#itemSelected').val(item.Prod_ShortNm);
                $('#unitId').val(item.Unit_Id);
                $('#unitDisplay').val(item.Unit_Name || info.Unit_Name || '');
                $('#purchaseRate').val(fmt(info.Purchase_Rate));
                $('#availQty').val(info.Avil_Qnty ?? 0);
                calcLine();
                $('#quantity').focus();
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load rate', 'error');
            });
        }

        function renderItemPickerTable(data) {
            setTimeout(function() {
                $('#itemPickerLoader').hide();
                if (!data.length) {
                    $('#itemPickerTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
                    $('#itemPickerTableWrap').show();
                    return;
                }
                $.each(data, function(i, item) {
                    $('#itemPickerTable tbody').append(
                        `<tr data-id="${item.Prod_Id}"
                     data-code="${item.Prod_Code || ''}"
                     data-name="${item.Prod_ShortNm || ''}"
                     data-unit="${item.Unit_Id || ''}"
                     data-unitname="${item.Unit_Name || ''}">
                    <td>${i + 1}</td>
                    <td>${item.Prod_Code || ''}</td>
                    <td>${item.Prod_ShortNm || ''}</td>
                    <td>${item.Unit_Name || ''}</td>
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

        function resetItemPickerTable() {
            $('#itemPickerLoader').hide();
            $('#itemPickerTableWrap').hide();
            if (itemPickerDT) {
                itemPickerDT.destroy();
                itemPickerDT = null;
            }
            $('#itemPickerTable tbody').html('');
        }

        function openItemPickerModal(data, code) {
            $('#itemPickerTitle').text('Select Item');
            resetItemPickerTable();
            $('#modalCateId').val('0');
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            $('#modalSearchInput').val(code || '');
            $('#itemPickerModal').modal('show');
            if (data.length > 0) {
                $('#itemPickerLoader').show();
                renderItemPickerTable(data);
            }
        }

        function lookupProductCode(code) {
            $.get("{{ route('wastage-damage.items') }}", {
                code: code,
                cat_id: 0,
                sub_cat_id: 0
            }, function(data) {
                const exactMatch = (data || []).find(item =>
                    String(item.Prod_Code || '').toLowerCase() === String(code).toLowerCase()
                );
                const unique = exactMatch || ((data || []).length === 1 ? data[0] : null);
                if (unique) {
                    applyItem(unique);
                    return;
                }
                openItemPickerModal(data || [], code);
            }).fail(function() {
                openItemPickerModal([], code);
            });
        }

        $('#quantity').on('input', calcLine);

        $('#productCodeInput').on('keypress', function(e) {
            if (e.which !== 13) return;
            e.preventDefault();
            const code = $(this).val().trim();
            if (!code) return;
            lookupProductCode(code);
        });

        $('#productSearchBtn').on('click', function(e) {
            e.preventDefault();
            const code = $('#productCodeInput').val().trim();
            if (!code) {
                openItemPickerModal([], '');
                return;
            }
            lookupProductCode(code);
        });

        $('#modalCateId').on('change', function() {
            const catId = parseInt($(this).val()) || 0;
            $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
            if (!catId) return;
            $.get("{{ route('wastage-damage.subcats') }}", {
                cat_id: catId
            }, function(subs) {
                (subs || []).forEach(function(s) {
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
            resetItemPickerTable();
            $('#itemPickerLoader').show();
            $.get("{{ route('wastage-damage.items') }}", {
                cat_id: catId,
                sub_cat_id: subCatId,
                code: code
            }, function(data) {
                renderItemPickerTable(data || []);
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
            resetItemPickerTable();
        });

        $(document).on('click', '#itemPickerTable tbody tr[data-id]', function() {
            applyItem({
                Prod_Id: $(this).data('id'),
                Prod_Code: $(this).data('code'),
                Prod_ShortNm: $(this).data('name'),
                Unit_Id: $(this).data('unit'),
                Unit_Name: $(this).data('unitname')
            });
            $('#itemPickerModal').modal('hide');
        });

        $('#addItemBtn').on('click', function() {
            if (!$('#entryDate').val()) {
                Swal.fire('Error', 'Date is required', 'error');
                return;
            }
            if (!$('#selectedItemId').val()) {
                Swal.fire('Error', 'Select a product', 'error');
                return;
            }
            const qty = parseInt($('#quantity').val(), 10) || 0;
            const rate = parseFloat($('#purchaseRate').val()) || 0;
            const avail = parseFloat($('#availQty').val()) || 0;
            if (qty <= 0) {
                Swal.fire('Error', 'Enter quantity', 'error');
                return;
            }
            if (rate <= 0) {
                Swal.fire('Error', 'Last purchase rate not found for this product', 'error');
                return;
            }
            if (qty > avail) {
                Swal.fire('Error', 'Quantity cannot exceed available stock (' + avail + ')', 'error');
                return;
            }
            const id = $('#selectedItemId').val();
            if (itemsArray.some(i => String(i.item_id) === String(id))) {
                Swal.fire('Error', 'Item already added', 'error');
                return;
            }
            itemsArray.push({
                item_id: id,
                item_name: $('#itemSelected').val(),
                unit_id: $('#unitId').val(),
                unit_name: $('#unitDisplay').val(),
                quantity: qty,
                rate: rate,
                total_amount: +(qty * rate).toFixed(2)
            });
            renderItems();
            clearItem();
        });

        $(document).on('click', '.removeItem', function() {
            itemsArray.splice($(this).data('i'), 1);
            renderItems();
        });

        $('#saveBtn').on('click', function() {
            const date = $('#entryDate').val();
            if (!date) {
                Swal.fire('Error', 'Date is required', 'error');
                return;
            }
            if (date < yearStart || date > yearEnd) {
                Swal.fire('Error', 'Date must be within the accounting year', 'error');
                return;
            }
            if (!$('#particulars').val().trim()) {
                Swal.fire('Error', 'Particular is required', 'error');
                return;
            }
            if (!itemsArray.length) {
                Swal.fire('Error', 'Add at least one item', 'error');
                return;
            }
            const isEdit = parseInt($('#damageId').val(), 10) > 0;
            $(this).prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
            $.ajax({
                url: "{{ route('wastage-damage.store') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    entry_date: date,
                    particulars: $('#particulars').val().trim(),
                    damage_id: $('#damageId').val(),
                    vouch_id: $('#vouchId').val(),
                    items: itemsArray
                },
                success: function(res) {
                    Swal.fire('Success', res.message, 'success').then(() => resetForm());
                },
                error: function(xhr) {
                    $('#saveBtn').prop('disabled', false).text(isEdit ? 'Update' : 'Save');
                    Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save',
                        'error');
                }
            });
        });

        function resetForm() {
            $('#damageId, #vouchId').val(0);
            $('#particulars').val('');
            $('#entryDate').val('{{ min(date('Y-m-d'), session('year_end')) }}');
            itemsArray = [];
            renderItems();
            clearItem();
            $('#saveBtn').prop('disabled', false).text('Save');
        }
        $('#cancelBtn').on('click', resetForm);

        $('#searchBtn').on('click', function() {
            $('#searchModal').modal('show');
        });
        $('#searchGo').on('click', function() {
            $.get("{{ route('wastage-damage.search') }}", {
                from_date: $('#searchFrom').val(),
                to_date: $('#searchTo').val()
            }, function(rows) {
                if (!rows.length) {
                    $('#searchBody').html(
                        '<tr><td colspan="5" class="text-center">No records found</td></tr>');
                    return;
                }
                let html = '';
                rows.forEach(function(r) {
                    html += `<tr>
                        <td>${r.Invoice_No || ''}</td>
                        <td>${r.Invoice_Date || ''}</td>
                        <td>${r.Particulars || ''}</td>
                        <td class="text-end">${fmt(r.Net_Amt)}</td>
                        <td><button type="button" class="btn btn-sm btn-primary editDmg" data-id="${r.Dmg_Id}">Edit</button></td>
                    </tr>`;
                });
                $('#searchBody').html(html);
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Search failed', 'error');
            });
        });

        $(document).on('click', '.editDmg', function() {
            const id = $(this).data('id');
            $.get("{{ url('wastage-damage/details') }}/" + id, function(d) {
                $('#searchModal').modal('hide');
                $('#damageId').val(d.Dmg_Id);
                $('#vouchId').val(d.Voucher_Id || 0);
                $('#entryDate').val((d.Invoice_Date || '').substring(0, 10));
                $('#particulars').val(d.Particulars || '');
                itemsArray = (d.Item_Details || []).map(function(item) {
                    return {
                        item_id: item.Prod_Id,
                        item_name: item.Prod_Name,
                        unit_id: item.Unit_Id,
                        unit_name: item.Unit_Name || '',
                        quantity: item.qnty,
                        rate: item.Item_Rate,
                        total_amount: item.Item_Total
                    };
                });
                renderItems();
                $('#saveBtn').text('Update');
            }).fail(function() {
                Swal.fire('Error', 'Failed to load details', 'error');
            });
        });
    </script>
@endpush
