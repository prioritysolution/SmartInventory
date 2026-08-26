@extends('Dashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="ps-2">GST Master</h5>
            <button class="btn btn-primary" onclick="openGstModal()">+ Add data</button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap datatable" id="gstTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>GST Code</th>
                                <th>Category</th>
                                <th>Sub Category</th>
                                <th>Tax %</th>
                                <th>SGST</th>
                                <th>CGST</th>
                                <th>IGST</th>
                                <th>UGST</th>
                                <th>Unit</th>
                                <th>Active</th>
                                <th>FMCG</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- GST Modal --}}
<div class="modal fade" id="gstModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gstModalTitle">Add GST</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="gst_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">GST Code<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gst_code" maxlength="20" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gst_category" maxlength="250" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Category</label>
                        <input type="text" class="form-control" id="gst_subcategory" maxlength="250" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax %<span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="gst_tax_percent" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SGST</label>
                        <input type="number" step="0.01" class="form-control" id="gst_sgst" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CGST</label>
                        <input type="number" step="0.01" class="form-control" id="gst_cgst" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">IGST</label>
                        <input type="number" step="0.01" class="form-control" id="gst_igst" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">UGST</label>
                        <input type="number" step="0.01" class="form-control" id="gst_ugst" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="gst_unit_name" maxlength="100" autocomplete="off">
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-2 mt-2">
                        <input type="checkbox" class="form-check-input" id="gst_is_active">
                        <label class="form-check-label" for="gst_is_active">Is Active</label>
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-2 mt-2">
                        <input type="checkbox" class="form-check-input" id="gst_is_fmcg">
                        <label class="form-check-label" for="gst_is_fmcg">Is FMCG</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveGstBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openGstModal() {
        $('#gst_id').val('');
        $('#gst_code, #gst_category, #gst_subcategory, #gst_tax_percent, #gst_sgst, #gst_cgst, #gst_igst, #gst_ugst, #gst_unit_name').val('');
        $('#gst_is_active, #gst_is_fmcg').prop('checked', false);
        $('#gstModalTitle').text('Add GST');
        $('#saveGstBtn').text('Save');
        $('#gstModal').modal('show');
    }
</script>
@endpush
