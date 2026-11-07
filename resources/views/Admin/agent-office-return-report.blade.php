@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Agent Return Report</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="frmDate"
                                value="{{ $year_start }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="toDate"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agent</label>
                            <select class="form-select" id="agentId" data-si-ajax="agents">
                                <option value="0">All Agents</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="printBtn" disabled>
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <button type="button" class="btn btn-outline-success" id="downloadBtn" disabled>
                                <i class="fas fa-download me-1"></i> Download
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
            'slug' => 'agent-office-return-report',
            'title' => 'Agent Return Report',
            'searchUrl' => route('agent-office-return-report.search'),
            'columns' => [
                ['key' => 'Doc_No', 'label' => 'Return No'],
                ['key' => 'Return_Date', 'label' => 'Return Date', 'type' => 'date'],
                ['key' => 'Accepted_Date', 'label' => 'Accepted Date', 'type' => 'date'],
                ['key' => 'Agent_Code', 'label' => 'Agent Code'],
                ['key' => 'Agent_Name', 'label' => 'Agent'],
                ['key' => 'Prod_Code', 'label' => 'Item Code'],
                ['key' => 'Prod_ShortNm', 'label' => 'Item Name'],
                ['key' => 'Qty', 'label' => 'Qty', 'type' => 'qty'],
                ['key' => 'Unit_Name', 'label' => 'Unit'],
                ['key' => 'Remarks', 'label' => 'Remarks'],
                ['key' => 'Status_Name', 'label' => 'Status'],
            ],
            'totals' => ['Qty'],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
