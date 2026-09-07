@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Share Register</h6>

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
            'slug' => 'share-register',
            'title' => 'Share Register',
            'searchUrl' => route('share-register.search'),
            'columns' => [
                ['key' => 'Share_No', 'label' => 'Share No'],
                ['key' => 'Admission_Date', 'label' => 'Admission Date', 'type' => 'date'],
                ['key' => 'Member_Name', 'label' => 'Member'],
                ['key' => 'Guardian_Name', 'label' => 'Guardian'],
                ['key' => 'Contact_No', 'label' => 'Contact'],
                ['key' => 'Adm_Fees', 'label' => 'Adm Fees', 'type' => 'amount'],
                ['key' => 'Rate_Per_Share', 'label' => 'Rate/Share', 'type' => 'amount'],
                ['key' => 'Share_Amount', 'label' => 'Share Amt', 'type' => 'amount'],
                ['key' => 'Total_Amount', 'label' => 'Total', 'type' => 'amount'],
                ['key' => 'Status_Name', 'label' => 'Status'],
            ],
            'totals' => ['Adm_Fees', 'Share_Amount', 'Total_Amount'],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
