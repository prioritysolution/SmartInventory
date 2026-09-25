@extends('Dashboard.Layouts.layout')

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
                                    <th class="text-end">MRP</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody id="stockBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Select date and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total</th>
                                    <th class="text-end" id="totalQty">0</th>
                                    <th class="text-end" id="totalValue">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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

        function fmtQty(n) {
            const v = parseFloat(n);
            if (isNaN(v)) return '0';
            return Number.isInteger(v) ? String(v) : v.toFixed(2);
        }

        function fmtAmt(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function resetTable(message, isError) {
            printRows = [];
            $('#printBtn').prop('disabled', true);
            $('#totalQty').text('0');
            $('#totalValue').text('0.00');
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
            let totQty = 0;
            let totValue = 0;
            printRows.forEach(function(row, idx) {
                const qty = parseFloat(row.Qty) || 0;
                const value = parseFloat(row.Value) || 0;
                totQty += qty;
                totValue += value;
                body += `<tr>
                    <td>${idx + 1}</td>
                    <td>${row.Prod_Code ?? ''}</td>
                    <td>${row.Prod_ShortNm ?? ''}</td>
                    <td>${row.Cate_Name ?? ''}</td>
                    <td>${row.Unit_Name ?? ''}</td>
                    <td style="text-align:right;">${fmtAmt(row.MRP)}</td>
                    <td style="text-align:right;">${fmtQty(qty)}</td>
                    <td style="text-align:right;">${fmtAmt(value)}</td>
                </tr>`;
            });
            body += `<tr>
                <th colspan="6" style="text-align:right;">Total</th>
                <th style="text-align:right;">${fmtQty(totQty)}</th>
                <th style="text-align:right;">${fmtAmt(totValue)}</th>
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
                        <th>Unit</th><th>MRP</th><th>Qty</th><th>Value</th>
                    </tr></thead>
                    <tbody>${body}</tbody>
                </table>
                </body></html>`);
        }

        $('#cateId').on('change', function() {
            loadSubCats($(this).val());
        });

        $('#printBtn').on('click', printStock);

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
                let totQty = 0;
                let totValue = 0;
                data.forEach(function(row, idx) {
                    const qty = parseFloat(row.Qty) || 0;
                    const value = parseFloat(row.Value) || 0;
                    totQty += qty;
                    totValue += value;
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${row.Prod_Code ?? ''}</td>
                        <td>${row.Prod_ShortNm ?? ''}</td>
                        <td>${row.Cate_Name ?? ''}</td>
                        <td>${row.Unit_Name ?? ''}</td>
                        <td class="text-end">${fmtAmt(row.MRP)}</td>
                        <td class="text-end">${fmtQty(qty)}</td>
                        <td class="text-end">${fmtAmt(value)}</td>
                    </tr>`;
                });
                $('#stockBody').html(html);
                $('#totalQty').text(fmtQty(totQty));
                $('#totalValue').text(fmtAmt(totValue));
                $('#printBtn').prop('disabled', false);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load stock summary';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
