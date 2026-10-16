@extends('Dashboard.Layouts.layout')

@push('style')
    <style>
        .barcode-picker-scroll {
            max-height: 280px;
            overflow-y: auto;
        }

        .barcode-picker-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f6f6f6;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Stock Summary Report</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">As on Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="asOnDate"
                                min="{{ $year_start }}" max="{{ $year_end }}"
                                value="{{ min($year_end, date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="cateId">
                                <option value="0">All Categories</option>
                                @foreach ($categories ?? [] as $cat)
                                    <option value="{{ $cat->Prd_CateId }}">{{ $cat->Prd_CateNm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sub Category</label>
                            <select class="form-select" id="subCateId">
                                <option value="0">All Sub Categories</option>
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
                        <table id="stockTable" class="table table-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th class="text-end">Agent Quantity</th>
                                    <th class="text-end">Counter Quantity</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="stockBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Select date and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th class="text-end" id="totalAgentQty">0</th>
                                    <th class="text-end" id="totalCounterQty">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stockDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockDetailTitle">Barcode Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive barcode-picker-scroll">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Barcode</th>
                                    <th class="text-end">MRP</th>
                                    <th class="text-end">Available Qty</th>
                                </tr>
                            </thead>
                            <tbody id="stockDetailBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="detailPrintBtn" disabled>
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const orgName = @json($org_name);
        const branchName = @json($branch_name);
        let printRows = [];
        let detailPrint = null;

        function fmtQty(n) {
            const v = parseFloat(n);
            if (isNaN(v)) return '0';
            return Number.isInteger(v) ? String(v) : v.toFixed(2);
        }

        function fmtAmt(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function resetTable(message, isError) {
            printRows = [];
            $('#printBtn').prop('disabled', true);
            $('#totalAgentQty, #totalCounterQty').text('0');
            const cls = isError ? 'text-danger' : 'text-muted';
            $('#stockBody').html(
                `<tr><td colspan="8" class="text-center ${cls}">${message}</td></tr>`
            );
        }

        function loadSubCats(catId) {
            $('#subCateId').html('<option value="0">All Sub Categories</option>');
            if (!parseInt(catId, 10)) return;
            $.get("{{ route('stock-summary.subcats') }}", { cat_id: catId }, function(subs) {
                (subs || []).forEach(function(s) {
                    $('#subCateId').append(
                        `<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`
                    );
                });
            });
        }

        function printStock() {
            if (!printRows.length) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            const asOn = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay($('#asOnDate').val())
                : $('#asOnDate').val();
            const cateText = $('#cateId option:selected').text();
            const subText = $('#subCateId option:selected').text();
            let body = '';
            let totAgent = 0;
            let totCounter = 0;
            printRows.forEach(function(row, idx) {
                const agentQty = parseFloat(row.Agent_Qty) || 0;
                const counterQty = parseFloat(row.Counter_Qty) || 0;
                totAgent += agentQty;
                totCounter += counterQty;
                body += `<tr>
                    <td>${idx + 1}</td>
                    <td>${esc(row.Prod_Code)}</td>
                    <td>${esc(row.Prod_ShortNm)}</td>
                    <td>${esc(row.Cate_Name)}</td>
                    <td>${esc(row.Unit_Name)}</td>
                    <td style="text-align:right;">${fmtQty(agentQty)}</td>
                    <td style="text-align:right;">${fmtQty(counterQty)}</td>
                </tr>`;
            });
            body += `<tr>
                <th colspan="5" style="text-align:right;">Total</th>
                <th style="text-align:right;">${fmtQty(totAgent)}</th>
                <th style="text-align:right;">${fmtQty(totCounter)}</th>
            </tr>`;
            siPrint(`<!DOCTYPE html><html><head><title>Stock Summary Report</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                    th, td { border: 1px solid #000; padding: 4px 6px; }
                    th { background: #f0f0f0; }
                </style></head><body>
                <h3>${orgName || 'Smart Inventory'}</h3>
                <h4>Stock Summary Report</h4>
                <p>${branchName ? branchName + ' &nbsp;|&nbsp; ' : ''}As on: ${asOn}</p>
                <p>Category: ${cateText} &nbsp;|&nbsp; Sub Category: ${subText}</p>
                <table>
                    <thead><tr>
                        <th>Sl</th><th>Item Code</th><th>Item Name</th><th>Category</th>
                        <th>Unit</th><th>Agent Quantity</th><th>Counter Quantity</th>
                    </tr></thead>
                    <tbody>${body}</tbody>
                </table>
                </body></html>`);
        }

        $('#cateId').on('change', function() {
            loadSubCats($(this).val());
        });

        $('#printBtn').on('click', printStock);

        $('#detailPrintBtn').on('click', function() {
            if (!detailPrint || !detailPrint.lines || !detailPrint.lines.length) {
                Swal.fire('Error', 'Barcode stock is not loaded', 'error');
                return;
            }
            const asOn = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay($('#asOnDate').val())
                : $('#asOnDate').val();
            let body = '';
            let total = 0;
            detailPrint.lines.forEach(function(line, idx) {
                const qty = parseFloat(line.Avil_Qnty) || 0;
                total += qty;
                const barcode = line.Barcode && line.Barcode != 0 ? line.Barcode : '';
                body += `<tr>
                    <td>${idx + 1}</td>
                    <td>${esc(barcode)}</td>
                    <td style="text-align:right;">${fmtAmt(line.MRP)}</td>
                    <td style="text-align:right;">${fmtQty(qty)}</td>
                </tr>`;
            });
            body += `<tr>
                <th colspan="3" style="text-align:right;">Total</th>
                <th style="text-align:right;">${fmtQty(total)}</th>
            </tr>`;
            siPrint(`<!DOCTYPE html><html><head><title>Barcode Stock</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                    th, td { border: 1px solid #000; padding: 4px 6px; }
                    th { background: #f0f0f0; }
                </style></head><body>
                <h3>${orgName || 'Smart Inventory'}</h3>
                <h4>Barcode Stock — ${esc(detailPrint.title)}</h4>
                <p>${branchName ? branchName + ' &nbsp;|&nbsp; ' : ''}${esc(detailPrint.code)} &nbsp;|&nbsp; As on: ${asOn}</p>
                <table>
                    <thead><tr>
                        <th>Sl</th><th>Barcode</th><th>MRP</th><th>Available Qty</th>
                    </tr></thead>
                    <tbody>${body}</tbody>
                </table>
                </body></html>`);
        });

        $(document).on('click', '.stock-detail-btn', function() {
            const row = printRows[$(this).data('idx')];
            if (!row) return;
            $('#stockDetailTitle').text('Barcode Stock — ' + (row.Prod_ShortNm || ''));
            $('#stockDetailBody').html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');
            $('#detailPrintBtn').prop('disabled', true);
            detailPrint = null;
            $('#stockDetailModal').modal('show');
            $.get("{{ route('stock-summary.details') }}", {
                as_on_date: $('#asOnDate').val(),
                prod_id: row.Prod_Id
            }, function(lines) {
                if (!lines.length) {
                    $('#stockDetailBody').html('<tr><td colspan="4" class="text-center text-muted">No barcode stock found</td></tr>');
                    return;
                }
                let html = '';
                lines.forEach(function(line, idx) {
                    const barcode = line.Barcode && line.Barcode != 0 ? line.Barcode : '';
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${esc(barcode)}</td>
                        <td class="text-end">${fmtAmt(line.MRP)}</td>
                        <td class="text-end">${fmtQty(line.Avil_Qnty)}</td>
                    </tr>`;
                });
                $('#stockDetailBody').html(html);
                detailPrint = {
                    title: row.Prod_ShortNm || '',
                    code: row.Prod_Code || '',
                    lines: lines
                };
                $('#detailPrintBtn').prop('disabled', false);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load barcode stock';
                $('#stockDetailBody').html(`<tr><td colspan="4" class="text-center text-danger">${esc(msg)}</td></tr>`);
            });
        });

        $('#searchBtn').on('click', function() {
            const asOnDate = $('#asOnDate').val();
            if (!asOnDate) {
                Swal.fire('Error', 'Select as on date', 'error');
                return;
            }

            resetTable('Loading...', false);
            $.get("{{ route('stock-summary.search') }}", {
                as_on_date: asOnDate,
                cat_id: $('#cateId').val() || 0,
                sub_cat_id: $('#subCateId').val() || 0
            }, function(data) {
                if (!data.length) {
                    resetTable('No stock found', false);
                    return;
                }
                printRows = data;
                let html = '';
                let totAgent = 0;
                let totCounter = 0;
                data.forEach(function(row, idx) {
                    const agentQty = parseFloat(row.Agent_Qty) || 0;
                    const counterQty = parseFloat(row.Counter_Qty) || 0;
                    totAgent += agentQty;
                    totCounter += counterQty;
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${esc(row.Prod_Code)}</td>
                        <td>${esc(row.Prod_ShortNm)}</td>
                        <td>${esc(row.Cate_Name)}</td>
                        <td>${esc(row.Unit_Name)}</td>
                        <td class="text-end">${fmtQty(agentQty)}</td>
                        <td class="text-end">${fmtQty(counterQty)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary stock-detail-btn" data-idx="${idx}">
                                Details
                            </button>
                        </td>
                    </tr>`;
                });
                $('#stockBody').html(html);
                $('#totalAgentQty').text(fmtQty(totAgent));
                $('#totalCounterQty').text(fmtQty(totCounter));
                $('#printBtn').prop('disabled', false);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load stock summary';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
