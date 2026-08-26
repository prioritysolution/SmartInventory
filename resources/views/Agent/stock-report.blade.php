@extends('AgentDashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex justify-content-between align-items-center ps-2 mb-3">
        <h6 class="mb-0">Stock Report</h6>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="asOnDate"
                        min="{{ $year_start }}" max="{{ $year_end }}"
                        value="{{ min($year_end, date('Y-m-d')) }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="searchBtn">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="printBtn" disabled>
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockTable" class="table table-bordered table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th class="text-end">Qty</th>
                        </tr>
                    </thead>
                    <tbody id="stockBody">
                        <tr><td colspan="5" class="text-center text-muted">Select date and click Search</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const baseUrl = "{{ url('/') }}";
    const orgName = @json(session('org_name'));
    const agentName = @json(session('agent_name'));
    let stockDT = null;
    let printRows = [];

    function printStock() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        const asOn = siDate.toDisplay($('#asOnDate').val());
        let body = '';
        printRows.forEach((row, idx) => {
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Prod_Code ?? ''}</td>
                <td>${row.Prod_ShortNm ?? ''}</td>
                <td>${row.Unit_Name ?? ''}</td>
                <td style="text-align:right;">${row.Qty ?? 0}</td>
            </tr>`;
        });
        const w = window.open('', '_blank');
        w.document.write(`<!DOCTYPE html><html><head><title>Stock Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Stock Report</h4>
            <p>Agent: ${agentName || ''} &nbsp;|&nbsp; Date: ${asOn}</p>
            <table>
                <thead><tr>
                    <th>Sl</th><th>Item Code</th><th>Item Name</th><th>Unit</th><th>Qty</th>
                </tr></thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`);
        w.document.close();
        w.focus();
        w.print();
    }

    $('#printBtn').on('click', printStock);

    $('#searchBtn').on('click', function () {
        const asOnDate = $('#asOnDate').val();
        if (!asOnDate) {
            Swal.fire('Error', 'Select date', 'error');
            return;
        }

        if (stockDT) { stockDT.destroy(); stockDT = null; }
        printRows = [];
        $('#printBtn').prop('disabled', true);
        $('#stockBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

        $.get(baseUrl + '/agent/report/stock/search', { as_on_date: asOnDate }, function (data) {
            if (!data.length) {
                $('#stockBody').html('<tr><td colspan="5" class="text-center text-muted">No stock found</td></tr>');
                return;
            }
            printRows = data;
            let html = '';
            data.forEach((row, idx) => {
                html += `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Prod_Code ?? ''}</td>
                    <td>${row.Prod_ShortNm ?? ''}</td>
                    <td>${row.Unit_Name ?? ''}</td>
                    <td class="text-end">${row.Qty ?? 0}</td>
                </tr>`;
            });
            $('#stockBody').html(html);
            $('#printBtn').prop('disabled', false);
            stockDT = $('#stockTable').DataTable({
                pageLength: 10,
                ordering: true,
                language: {
                    search: '', searchPlaceholder: 'Search...',
                    sLengthMenu: 'Row Per Page _MENU_ Entries',
                    info: '_START_ - _END_ of _TOTAL_ items',
                    paginate: {
                        next: '<i class="fa fa-angle-right"></i>',
                        previous: '<i class="fa fa-angle-left"></i>'
                    }
                }
            });
        }).fail(function (xhr) {
            const msg = xhr.responseJSON?.message || 'Failed to load stock report. Run USP_AGENT_REPORTS.sql on the organisation database.';
            Swal.fire('Error', msg, 'error');
            $('#stockBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load</td></tr>');
        });
    });
</script>
@endpush
