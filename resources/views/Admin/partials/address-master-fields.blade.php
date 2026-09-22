{{-- Shared Address Master fields. Prefix: $prefix, $colClass --}}
@php
    $p = $prefix ?? '';
    $col = $colClass ?? 'col-md-3 mb-3';
    $req = $required ?? true;
    $star = $req ? ' <span class="text-danger">*</span>' : '';
@endphp
<div class="{{ $col }}">
    <label class="form-label">Village{!! $star !!}</label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}village_id" data-addr-type="village">
        <option value="">Select Village</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">Police Station{!! $star !!}</label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}ps_id" data-addr-type="ps">
        <option value="">Select Police Station</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">Post Office{!! $star !!}</label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}post_id" data-addr-type="post">
        <option value="">Select Post Office</option>
    </select>
</div>
<div class="{{ $col }}">
    <label class="form-label">PIN Code{!! $star !!}</label>
    <input type="text" class="form-control form-control-lg" id="{{ $p }}pin_code" readonly
        placeholder="Select Post Office">
</div>
<div class="{{ $col }}">
    <label class="form-label">District{!! $star !!}</label>
    <select class="form-select form-control-lg addr-master-select" id="{{ $p }}dist_id" data-addr-type="dist">
        <option value="">Select District</option>
    </select>
</div>
