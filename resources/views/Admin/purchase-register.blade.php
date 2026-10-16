@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Purchase Register</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="frmDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ $year_start }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="toDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Supplier</label>
                            <select class="form-select" id="partyId">
                                <option value="0">All</option>
                                @foreach ($parties as $party)
                                    <option value="{{ $party->Party_Id }}">{{ $party->Party_Name }} ({{ $party->Party_Code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">GST Filter</label>
                            <select class="form-select" id="gstFilter">
                                <option value="0">All</option>
                                <option value="1">GST Registered</option>
                                <option value="2">GST Not Registered</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="printBtn" disabled>
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @include('Admin.partials.billwise-register', [
                'searchUrl' => route('purchase-register.search'),
                'reportTitle' => 'Billwise Purchase Register',
                'extraFields' => ['party_id' => '#partyId', 'gst_filter' => '#gstFilter'],
            ])
        </div>
    </div>
@endsection
