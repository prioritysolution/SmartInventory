@extends('Dashboard.Layouts.layout')
@section('content')
<div class="page-wrapper">
<div class="content container-fluid">
    <div class="mb-3">
        <h6 class="ps-2">Product Sub Category</h6>
    </div>
    <div class="row">
        <!-- Left: Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3" id="formTitle">Add Sub Category</h6>
                    <input type="hidden" id="subCateId">
                    <div class="mb-3">
                        <label class="form-label">Category<span class="text-danger">*</span></label>
                        <select class="form-select" id="cateId">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->Prd_CateId }}">{{ $cat->Prd_CateNm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sub Category Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subCateNm" maxlength="50" autocomplete="off">
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="saveSubCategory">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Table -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                  <h6 class="mb-3">Product Sub Category List</h6>  
                    <div class="table-responsive">
                        <table id="subCategoryTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th><th>Category</th><th>Sub Category Name</th><th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subCategories as $key => $sub)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $sub->Prd_CateNm ?? '' }}</td>
                                    <td>{{ $sub->Prd_SubCateNm }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm editRow"
                                            data-id="{{ $sub->Prd_SubCateId }}"
                                            data-name="{{ $sub->Prd_SubCateNm }}"
                                            data-cateid="{{ $sub->Prd_CateId }}">Edit</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#subCategoryTable').DataTable();

    $(document).on('click', '.editRow', function () {
        $('#subCateId').val($(this).data('id'));
        $('#subCateNm').val($(this).data('name'));
        $('#cateId').val($(this).data('cateid'));
        $('#formTitle').text('Edit Sub Category');
        $('#saveSubCategory').text('Update');
    });

    $('#cancelBtn').on('click', clearForm);

    $('#saveSubCategory').click(function () {
        if (!$('#cateId').val()) { Swal.fire('Validation Error', 'Category required', 'error'); return; }
        if (!$('#subCateNm').val().trim()) { Swal.fire('Validation Error', 'Sub Category Name required', 'error'); return; }
        $(this).prop('disabled', true).text('Saving...');
        let id = $('#subCateId').val();
        $.ajax({
            url: "{{ route('prod-subcategory.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                sub_cate_id: id,
                sub_cate_nm: $('#subCateNm').val().trim(),
                cate_id: $('#cateId').val()
            },
            success: res => {
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            },
            error: xhr => {
                $('#saveSubCategory').prop('disabled', false).text(id ? 'Update' : 'Save');
                let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : (xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    function clearForm() {
        $('#subCateId').val('');
        $('#subCateNm').val('');
        $('#cateId').val('');
        $('#formTitle').text('Add Sub Category');
        $('#saveSubCategory').prop('disabled', false).text('Save');
    }
});
</script>
@endpush
