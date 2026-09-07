@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        .party-ledger-banner h5 { font-size: 1.05rem; }
        .party-ledger-table th,
        .party-ledger-table td { border-color: #000 !important; }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Supplier Register</h6>

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
                        <div class="col-md-4 d-flex align-items-end">
                            <div>
                                <label class="form-label d-block">Report Type</label>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="reportView" id="viewDetailed" value="1" checked>
                                    <label class="form-check-label" for="viewDetailed">Detailed List</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="reportView" id="viewLedger" value="2">
                                    <label class="form-check-label" for="viewLedger">Ledger</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 ledger-party-filter d-none">
                            <label class="form-label">Supplier<span class="text-danger">*</span></label>
                            <select class="form-select" id="partyId">
                                <option value="">Select Supplier</option>
                                @foreach ($parties as $party)
                                    <option value="{{ $party->Party_Id }}" data-code="{{ $party->Party_Code }}" data-name="{{ $party->Party_Name }}">
                                        {{ $party->Party_Name }}-{{ $party->Party_Code }}
                                    </option>
                                @endforeach
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
                    <div id="ledgerReportBanner" class="party-ledger-banner d-none text-center mb-3"></div>
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
            'slug' => 'supplier-register',
            'title' => 'Supplier Register',
            'searchUrl' => route('supplier-register.search'),
            'columns' => [
                ['key' => 'Party_Code', 'label' => 'Code'],
                ['key' => 'Party_Name', 'label' => 'Supplier'],
                ['key' => 'Opening_Amt', 'label' => 'Opening', 'type' => 'amount'],
                ['key' => 'Purchase_Amt', 'label' => 'Purchase', 'type' => 'amount'],
                ['key' => 'Payment_Amt', 'label' => 'Payment', 'type' => 'amount'],
                ['key' => 'Closing_Amt', 'label' => 'Closing Balance', 'type' => 'amount'],
            ],
            'totals' => ['Opening_Amt', 'Purchase_Amt', 'Payment_Amt', 'Closing_Amt'],
            'ledger_columns' => [
                ['key' => 'Trans_Date', 'label' => 'Trans Date', 'type' => 'date'],
                ['key' => 'Particulars', 'label' => 'Particulars'],
                ['key' => 'Mode_Name', 'label' => 'Mode'],
                ['key' => 'Debit_Amt', 'label' => 'Debit', 'type' => 'amount_blank'],
                ['key' => 'Credit_Amt', 'label' => 'Credit', 'type' => 'amount_blank'],
                ['key' => 'Balance_Label', 'label' => 'Balance'],
            ],
            'ledger_totals' => [],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
