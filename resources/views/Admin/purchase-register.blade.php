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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive report-scroll">
                        <table id="reportTable" class="table table-nowrap">
                            <thead class="thead-light">
                                <tr id="reportHead"></tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td class="text-center text-muted">Select filters and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr id="reportFoot"></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $reportCfg = [
            'slug' => 'purchase-register',
            'title' => 'Purchase Register',
            'searchUrl' => route('purchase-register.search'),
            'extraParams' => ['gst_filter' => '#gstFilter'],
            'columns' => [
                ['key' => 'Invoice_No', 'label' => 'Invoice No'],
                ['key' => 'Invoice_Date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'Party_Name', 'label' => 'Supplier'],
                ['key' => 'GstIn', 'label' => 'GSTIN'],
                ['key' => 'Invoice_Amt', 'label' => 'Invoice Amt', 'type' => 'amount'],
                ['key' => 'Disc_Amount', 'label' => 'Discount', 'type' => 'amount'],
                ['key' => 'Taxable_Amt', 'label' => 'Taxable', 'type' => 'amount'],
                ['key' => 'GST_Amt', 'label' => 'GST', 'type' => 'amount'],
                ['key' => 'Net_Amt', 'label' => 'Net Amt', 'type' => 'amount'],
            ],
            'totals' => ['Invoice_Amt', 'Disc_Amount', 'Taxable_Amt', 'GST_Amt', 'Net_Amt'],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
