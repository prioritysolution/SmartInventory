@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Agent Register</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 filter-from-to">
                            <label class="form-label">From Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="frmDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ $year_start }}">
                        </div>
                        <div class="col-md-3 filter-from-to">
                            <label class="form-label">To Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="toDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 filter-as-on">
                            <label class="form-label">As on Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="asOnDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agent</label>
                            <select class="form-select" id="agentId">
                                <option value="0">All Agents</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->Agent_Id }}">{{ $agent->Agent_Name }} ({{ $agent->Agent_Code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select class="form-select" id="modeId">
                                <option value="1">Indent</option>
                                <option value="2">Stock</option>
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
            'slug' => 'agent-register',
            'title' => 'Agent Register',
            'searchUrl' => route('agent-register.search'),
            'indent_columns' => [
                ['key' => 'Indent_No', 'label' => 'Indent No'],
                ['key' => 'Indent_Date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'Agent_Name', 'label' => 'Agent'],
                ['key' => 'Prod_Code', 'label' => 'Item Code'],
                ['key' => 'Prod_ShortNm', 'label' => 'Item Name'],
                ['key' => 'Req_Qty', 'label' => 'Req Qty', 'type' => 'qty'],
                ['key' => 'Issue_Qty', 'label' => 'Issue Qty', 'type' => 'qty'],
                ['key' => 'Reject_Qty', 'label' => 'Reject Qty', 'type' => 'qty'],
                ['key' => 'Unit_Name', 'label' => 'Unit'],
                ['key' => 'Status_Name', 'label' => 'Status'],
            ],
            'stock_columns' => [
                ['key' => 'Agent_Name', 'label' => 'Agent'],
                ['key' => 'Prod_Code', 'label' => 'Item Code'],
                ['key' => 'Prod_ShortNm', 'label' => 'Item Name'],
                ['key' => 'Unit_Name', 'label' => 'Unit'],
                ['key' => 'Qty', 'label' => 'Qty', 'type' => 'qty'],
            ],
            'indent_totals' => ['Req_Qty', 'Issue_Qty', 'Reject_Qty'],
            'stock_totals' => ['Qty'],
            'columns' => [
                ['key' => 'Indent_No', 'label' => 'Indent No'],
                ['key' => 'Indent_Date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'Agent_Name', 'label' => 'Agent'],
                ['key' => 'Prod_Code', 'label' => 'Item Code'],
                ['key' => 'Prod_ShortNm', 'label' => 'Item Name'],
                ['key' => 'Req_Qty', 'label' => 'Req Qty', 'type' => 'qty'],
                ['key' => 'Issue_Qty', 'label' => 'Issue Qty', 'type' => 'qty'],
                ['key' => 'Reject_Qty', 'label' => 'Reject Qty', 'type' => 'qty'],
                ['key' => 'Unit_Name', 'label' => 'Unit'],
                ['key' => 'Status_Name', 'label' => 'Status'],
            ],
            'totals' => ['Req_Qty', 'Issue_Qty', 'Reject_Qty'],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
