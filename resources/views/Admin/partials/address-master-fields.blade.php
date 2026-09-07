{{-- Shared Address Master fields. Optional: $prefix, $colClass --}}
@php
    $p = $prefix ?? '';
    $col = $colClass ?? 'col-md-3 mb-3';
@endphp
<div class="{{ $col }}">
    <label class="form-label">Village <span class="text-danger">*</span></label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}village_id" data-addr-type="village">
        <option value="">Select Village</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">Police Station <span class="text-danger">*</span></label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}ps_id" data-addr-type="ps">
        <option value="">Select Police Station</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">Post Office <span class="text-danger">*</span></label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}post_id" data-addr-type="post">
        <option value="">Select Post Office</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">PIN Code <span class="text-danger">*</span></label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}pin_id" data-addr-type="pin">
        <option value="">Select PIN Code</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">District <span class="text-danger">*</span></label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}dist_id" data-addr-type="dist">
        <option value="">Select District</option>
    </select>
</div>
