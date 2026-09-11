@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
    <style>
        .modal-xl-custom {
            max-width: 1100px;
        }

        #itemPickerTable tbody tr {
            cursor: pointer;
        }

        #itemPickerTable tbody tr:hover {
            background-color: #eef5ff;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h5 class=" ps-2">Product Master</h5>
                <div style="width: 350px; position: relative;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchItem" placeholder="Search Product"
                            autocomplete="off">
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="product_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_code" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" maxlength="50" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Print Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="print_name" maxlength="50" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit<span class="text-danger">*</span></label>
                            <select class="form-select" id="unit">
                                <option value="">Select Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->Unit_Id }}">{{ $unit->Unit_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select class="form-select" id="category">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->Prd_CateId }}" data-fmcg="{{ $category->Is_Fmcg }}">
                                        {{ $category->Prd_CateNm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sub Category<span class="text-danger">*</span></label>
                            <select class="form-select" id="sub_category">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">HSN</label>
                            <select class="form-select" id="hsn">
                                <option value="">Select HSN</option>
                                @foreach ($hsn as $h)
                                    <option value="{{ $h->Id }}">{{ $h->HSN }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Margin (%)</label>
                            <input type="number" step="0.01" class="form-control" id="sale_margin" min="0" max="100"
                                autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reorder Quantity</label>
                            <input type="number" class="form-control" id="reorder_qnty" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3" id="prodLifeDiv" style="display:none;">
                            <label class="form-label">Max Life</label>
                            <input type="number" class="form-control" id="prod_life" min="0" max="200"
                                autocomplete="off">
                        </div>


                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Picker Modal (category-wise) --}}
    <div class="modal fade" id="itemPickerModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemPickerTitle">Select Product</h5>
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let itemPickerDT = null;

            $('#unit').select2({
                placeholder: 'Select Unit',
                allowClear: true
            });
            $('#category').select2({
                placeholder: 'Select Category',
                allowClear: true
            });
            $('#sub_category').select2({
                placeholder: 'Select Sub Category',
                allowClear: true
            });
            $('#hsn').select2({
                placeholder: 'Select HSN',
                allowClear: true
            });

            function openItemPickerModal(code) {
                $('#itemPickerTitle').text('Select Product');
                $('#itemPickerLoader').hide();
                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');
                $('#modalCateId').val('0');
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                $('#modalSearchInput').val(code || '');
                $('#itemPickerModal').modal('show');
                if (code) {
                    $('#modalSearchBtn').trigger('click');
                }
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
                            `<tr data-id="${item.Prod_Id}">
                                <td>${i + 1}</td>
                                <td>${item.Prod_Code || ''}</td>
                                <td>${item.Prod_ShortNm || item.Item_Name || ''}</td>
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

            $('#searchBtn').on('click', function() {
                openItemPickerModal($('#searchItem').val().trim());
            });

            $('#searchItem').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#searchBtn').trigger('click');
                }
            });

            $('#modalCateId').on('change', function() {
                const catId = parseInt($(this).val()) || 0;
                $('#modalSubCateId').html('<option value="0">-- All Sub Categories --</option>');
                if (!catId) return;
                $.get(`/product-master/subcategories/${catId}`, function(subs) {
                    (subs || []).forEach(s => {
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
                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');
                $.get("{{ route('product-master.search') }}", {
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
                $('#itemPickerLoader').hide();
                $('#itemPickerTableWrap').hide();
                if (itemPickerDT) {
                    itemPickerDT.destroy();
                    itemPickerDT = null;
                }
                $('#itemPickerTable tbody').html('');
            });

            $(document).on('click', '#itemPickerTable tbody tr', function() {
                const prodId = $(this).data('id');
                if (!prodId) return;
                $('#itemPickerModal').modal('hide');
                loadItemDetails(prodId);
            });

            function loadItemDetails(itemId) {
                $.ajax({
                    url: `/product-master/details/${itemId}`,
                    type: 'GET',
                    success: function(data) {
                        if (data) {
                            $('#product_id').val(data.Prod_Id);
                            $('#product_code').val(data.Prod_Code);
                            $('#product_name').val(data.Prod_ShortNm);
                            $('#print_name').val(data.Prod_PrintNm);
                            $('#unit').val(data.Unit_Id).trigger('change');
                            $('#hsn').val(data.Gst_Id).trigger('change');
                            $('#sale_margin').val(data.Sale_Margin);
                            $('#reorder_qnty').val(data.ReOrder_Qty);
                            $('#prod_life').val(data.Prod_Life);
                            $('#category').val(data.Cate_Id).trigger('change');
                            $.get(`/product-master/subcategories/${data.Cate_Id}`, function(subs) {
                                $('#sub_category').html(
                                    '<option value="">Select Sub Category</option>');
                                (subs || []).forEach(s => {
                                    $('#sub_category').append(
                                        `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                                    );
                                });
                                $('#sub_category').val(data.SubCate_Id).trigger('change');
                            });

                            $('#saveBtn').text('Update');
                            $('#searchItem').val('');
                        } else {
                            Swal.fire('Error', 'Product not found', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load product details', 'error');
                    }
                });
            }

            $('#saveBtn').on('click', function() {

                const isUpdate = !!$('#product_id').val();
                if (!IS_ADMIN && isUpdate) {
                    Swal.fire('Access Denied', 'You do not have permission to edit a product.', 'warning');
                    return;
                }

                if (!validateForm()) return;
                $(this).prop('disabled', true).text(isUpdate ? 'Updating...' : 'Saving...');

                $.ajax({
                    url: '/product-master/save',
                    type: 'POST',
                    data: {
                        product_id: $('#product_id').val(),
                        product_code: $('#product_code').val().trim(),
                        product_name: $('#product_name').val().trim(),
                        print_name: $('#print_name').val().trim(),
                        unit: $('#unit').val(),
                        category: $('#category').val(),
                        sub_category: $('#sub_category').val(),
                        hsn: $('#hsn').val(),
                        sale_margin: $('#sale_margin').val(),
                        reorder_qnty: $('#reorder_qnty').val(),
                        prod_life: $('#prod_life').val(),
                        mode: isUpdate ? 2 : 1,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#saveBtn').prop('disabled', false).text(isUpdate ? 'Update' :
                            'Save');
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            clearForm();
                        }
                    },
                    error: function(xhr) {
                        $('#saveBtn').prop('disabled', false).text(isUpdate ? 'Update' :
                            'Save');
                        Swal.fire('Error', xhr.responseJSON?.message || (isUpdate ?
                                'Failed to update product' : 'Failed to save product'),
                            'error');
                    }
                });
            });

            $('#cancelBtn').on('click', clearForm);

            $('#category').on('change', function() {
                const categoryId = $(this).val();
                const isFmcg = $(this).find('option:selected').data('fmcg');
                if (isFmcg == 1) {
                    $('#prodLifeDiv').show();
                } else {
                    $('#prodLifeDiv').hide();
                    $('#prod_life').val('');
                }
                $('#sub_category').html('<option value="">Select Sub Category</option>').trigger(
                    'change.select2');
                if (!categoryId) return;
                $.get(`/product-master/subcategories/${categoryId}`, function(data) {
                    $('#sub_category').html('<option value="">Select Sub Category</option>');
                    (data || []).forEach(item => {
                        $('#sub_category').append(
                            `<option value="${item.Prd_SubCateId}">${item.Prd_SubCateNm}</option>`
                        );
                    });
                    $('#sub_category').trigger('change.select2');
                });
            });

        });

        function validateForm() {
            if (!$('#product_code').val().trim()) {
                Swal.fire('Validation Error', 'Product Code is required', 'error');
                return false;
            }
            if (!$('#product_name').val().trim()) {
                Swal.fire('Validation Error', 'Product Name is required', 'error');
                return false;
            }
            if (!$('#print_name').val().trim()) {
                Swal.fire('Validation Error', 'Print Name is required', 'error');
                return false;
            }
            if (!$('#unit').val()) {
                Swal.fire('Validation Error', 'Please select Unit', 'error');
                return false;
            }
            if (!$('#category').val()) {
                Swal.fire('Validation Error', 'Please select Category', 'error');
                return false;
            }
            if (!$('#sub_category').val()) {
                Swal.fire('Validation Error', 'Please select Sub Category', 'error');
                return false;
            }
            if ($('#prodLifeDiv').is(':visible') && $('#prod_life').val() !== '') {
                const life = parseInt($('#prod_life').val());
                if (life < 0 || life > 200) {
                    Swal.fire('Validation Error', 'Max Life must be between 0 and 200', 'error');
                    return false;
                }
            }
            return true;
        }

        function clearForm() {
            $('#product_id').val('');
            $('#searchItem').val('');
            $('#product_code, #product_name, #print_name, #sale_margin, #reorder_qnty, #prod_life').val('');
            $('#unit, #category, #hsn').val('').trigger('change');
            $('#sub_category').html('<option value="">Select Sub Category</option>');
            $('#saveBtn').text('Save');
        }
    </script>
@endpush
