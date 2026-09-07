@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Ledger Book</h6>

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
                            <label class="form-label">Ledger<span class="text-danger">*</span></label>
                            <select class="form-select" id="ledgerId">
                                <option value="0">Select Ledger</option>
                                @foreach ($ledgers as $ledger)
                                    <option value="{{ $ledger->Account_Id }}">{{ $ledger->Account_Code }} - {{ $ledger->Account_Desc }}</option>
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
            'slug' => 'ledger-book',
            'title' => 'Ledger Book',
            'searchUrl' => route('ledger-book.search'),
            'keep_order' => true,
            'columns' => [
                ['key' => 'Vou_Date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'Vou_No', 'label' => 'Voucher No'],
                ['key' => 'Particulars', 'label' => 'Particulars'],
                ['key' => 'Debit', 'label' => 'Debit', 'type' => 'amount'],
                ['key' => 'Credit', 'label' => 'Credit', 'type' => 'amount'],
                ['key' => 'Balance', 'label' => 'Balance', 'type' => 'amount'],
            ],
            'totals' => ['Debit', 'Credit'],
        ];
    @endphp
    @include('Admin.report-table-script')
@endpush
