@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
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
                    <div id="searchDropdown" class="list-group position-absolute"
                        style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

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

            // Search
            $('#searchBtn').on('click', function() {
                const searchTerm = $('#searchItem').val().trim();
                if (searchTerm.length >= 1) {
                    $.ajax({
                        url: '/product-master/search',
                        type: 'GET',
                        data: {
                            search: searchTerm
                        },
                        success: function(data) {
                            if (data.length > 0) {
                                let html = '';
                                data.forEach(item => {
                                    html +=
                                        `<a href="#" class="list-group-item list-group-item-action search-item" data-id="${item.Prod_Id}">${item.ItemDisplay}</a>`;
                                });
                                $('#searchDropdown').html(html).show();
                            } else {
                                $('#searchDropdown').html(
                                        '<div class="list-group-item">No items found</div>')
                                    .show();
                            }
                        }
                    });
                }
            });

            $('#searchItem').on('input', function() {
                if ($(this).val().trim() === '') $('#searchDropdown').hide();
            });

            $(document).on('click', '.search-item', function(e) {
                e.preventDefault();
                $('#searchItem').val($(this).text());
                $('#searchDropdown').hide();
                loadItemDetails($(this).data('id'));
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#searchItem, #searchDropdown, #searchBtn').length) {
                    $('#searchDropdown').hide();
                }
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
                            // Load subcategories then set value
                            $('#category').val(data.Cate_Id).trigger('change');
                            $.get(`/product-master/subcategories/${data.Cate_Id}`, function(subs) {
                                $('#sub_category').html(
                                    '<option value="">Select Sub Category</option>');
                                subs.forEach(s => {
                                    $('#sub_category').append(
                                        `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                                    );
                                });
                                $('#sub_category').val(data.SubCate_Id).trigger('change');
                            });

                            $('#saveBtn').text('Update');
                            $('#searchItem').val('');
                            $('#searchDropdown').hide();
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

                if (!validateForm()) return;

                const isUpdate = !!$('#product_id').val();
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
                    data.forEach(item => {
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
