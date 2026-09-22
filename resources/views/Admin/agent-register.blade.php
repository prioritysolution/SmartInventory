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
                ['key' => '_action', 'label' => 'Action', 'type' => 'indent_print'],
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
                ['key' => '_action', 'label' => 'Action', 'type' => 'indent_print'],
            ],
            'totals' => ['Req_Qty', 'Issue_Qty', 'Reject_Qty'],
        ];
    @endphp
    @include('Admin.report-table-script')
    <script>
        const generatedBy = @json(session('user_name', 'User'));

        function fmtPrintQty(n) {
            const v = parseFloat(n);
            if (isNaN(v)) return '0';
            return Number.isInteger(v) ? String(v) : v.toFixed(2);
        }

        function reprintIndentBill(indentId) {
            const rows = (printRows || []).filter(function(r) {
                return String(r.Indent_Id) === String(indentId);
            });
            if (!rows.length) {
                Swal.fire('Error', 'Indent details not found', 'error');
                return;
            }

            const first = rows[0];
            const issueDateRaw = first.Issue_Date || first.Indent_Date || '';
            const issueDate = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay(issueDateRaw)
                : issueDateRaw;
            const remarks = String(first.Remarks || '');
            const formLabel = (remarks === 'From Office' || String(first.Indent_Type_Id) === '2')
                ? 'New Indent'
                : 'Requisition Issue';
            const agentName = first.Agent_Name || '';
            const agentCode = first.Agent_Code || '';

            let rowsHtml = '';
            let totalAmount = 0;
            rows.forEach(function(item, i) {
                const issueQty = parseFloat(item.Issue_Qty) || 0;
                const unit = item.Unit_Name ? (' ' + item.Unit_Name) : '';
                const mrpVal = parseFloat(item.MRP) || 0;
                const mrp = mrpVal > 0 ? mrpVal.toFixed(2) : '-';
                const amount = mrpVal * issueQty;
                totalAmount += amount;
                rowsHtml += `<tr>
                    <td class="c">${i + 1}</td>
                    <td>${item.Prod_ShortNm || ''}</td>
                    <td class="r">${fmtPrintQty(issueQty)}${unit}</td>
                    <td class="r">${mrp}</td>
                    <td class="r">${amount.toFixed(2)}</td>
                </tr>`;
            });

            const billHtml = `
            <div class="sheet">
                <div class="org">
                    <h2>${orgName || 'Smart Inventory'}</h2>
                    ${branchName ? `<p class="branch">${branchName}</p>` : ''}
                    <h3 class="title">Agent Indent Bill</h3>
                </div>
                <table class="meta">
                    <tr><td class="lbl">Issue Date</td><td>: ${issueDate || ''}</td>
                        <td class="lbl">Form</td><td>: ${formLabel}</td></tr>
                    <tr><td class="lbl">Agent Name</td><td>: ${agentName}</td>
                        <td class="lbl">Agent Code</td><td>: ${agentCode}</td></tr>
                </table>
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:40px;">Sl</th>
                            <th>Item Name</th>
                            <th style="width:120px;">Issue Quantity</th>
                            <th style="width:90px;">MRP</th>
                            <th style="width:110px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml || '<tr><td colspan="5" class="c">No items</td></tr>'}
                    </tbody>
                </table>
                <div class="total-wrap">Total Amount : ${totalAmount.toFixed(2)}</div>
                <div class="sign-wrap">
                    <div class="sign-box"><div class="sign-line">Authorized Signature</div></div>
                    <div class="sign-box"><div class="sign-line">Agent Signature</div></div>
                </div>
                <div class="print-foot">Generated By : ${generatedBy || 'User'}</div>
            </div>`;

            let iframe = document.getElementById('indentReprintFrame');
            if (iframe) {
                if (iframe.dataset.blobUrl) URL.revokeObjectURL(iframe.dataset.blobUrl);
                iframe.remove();
            }
            iframe = document.createElement('iframe');
            iframe.id = 'indentReprintFrame';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
            document.body.appendChild(iframe);

            const fullHtml = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title></title>
            <style>
                @page { size: A4; margin: 14mm 12mm 18mm 12mm; }
                html, body { margin: 0; padding: 0; }
                body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
                .sheet { width: 100%; min-height: 100%; position: relative; padding-bottom: 28px; box-sizing: border-box; }
                .print-foot { position: fixed; left: 0; bottom: 4mm; font-size: 11px; text-align: left; }
                .org { text-align: center; width: 100%; margin: 0 0 14px 0; }
                .org h2 { margin: 0 0 4px; font-size: 20px; text-transform: uppercase; text-align: center; }
                .org .branch { margin: 0 0 10px; font-size: 12px; text-align: center; }
                .org .title {
                    display: block; width: 100%; margin: 10px 0 0; padding: 0;
                    font-size: 16px; font-weight: bold; text-align: center;
                    text-decoration: underline; text-transform: uppercase; letter-spacing: 1px;
                }
                .meta { width: 100%; margin-bottom: 14px; }
                .meta td { padding: 3px 0; vertical-align: top; }
                .meta .lbl { width: 110px; font-weight: bold; }
                table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
                table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; }
                table.items th { background: #f2f2f2; text-align: center; }
                .c { text-align: center; }
                .r { text-align: right; white-space: nowrap; }
                .total-wrap { margin-top: 12px; text-align: right; font-size: 14px; font-weight: bold; }
                .sign-wrap { display: flex; justify-content: space-between; margin-top: 70px; padding: 0 10px; }
                .sign-box { width: 40%; text-align: center; }
                .sign-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 6px; font-weight: bold; }
                @media print {
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .print-foot { position: fixed; left: 0; bottom: 4mm; }
                }
            </style></head><body>${billHtml}</body></html>`;

            const blobUrl = URL.createObjectURL(new Blob([fullHtml], { type: 'text/html' }));
            iframe.dataset.blobUrl = blobUrl;
            iframe.onload = function() {
                setTimeout(function() {
                    const prevTitle = document.title;
                    document.title = ' ';
                    const restoreTitle = function() {
                        document.title = prevTitle;
                        window.removeEventListener('focus', restoreTitle);
                    };
                    window.addEventListener('focus', restoreTitle);
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(restoreTitle, 1500);
                }, 100);
            };
            iframe.src = blobUrl;
        }

        $(document).on('click', '.btn-reprint-indent', function() {
            const indentId = $(this).data('indent-id');
            if (!indentId) {
                Swal.fire('Error', 'Indent not found', 'error');
                return;
            }
            reprintIndentBill(indentId);
        });
    </script>
@endpush
