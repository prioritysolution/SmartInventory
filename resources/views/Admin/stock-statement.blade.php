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

        .stock-register {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
            color: #000;
            background: #fff;
            font-size: 12px;
        }

        .stock-register th,
        .stock-register td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .stock-register thead th {
            background: #fff;
            font-weight: 700;
            text-align: center;
            box-shadow: none;
        }

        .stock-register td.item-name {
            text-align: left;
            white-space: normal;
            min-width: 140px;
        }

        .stock-register .num {
            text-align: right;
        }

        .stock-register .detail-link {
            color: #0d6efd;
            background: none;
            border: 0;
            padding: 0;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .stock-register tfoot th {
            font-weight: 700;
            background: #fff;
        }

        .report-scroll .stock-register thead th {
            position: sticky;
            background: #fff;
            box-shadow: none;
        }

        .report-scroll .stock-register thead tr:nth-child(2) th {
            top: 26px;
        }

        .register-heading {
            text-align: center;
            color: #000;
            margin-bottom: 8px;
        }

        .register-heading h5,
        .register-heading p {
            margin: 0;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Stock Statement &amp; Register</h6>

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
                            <label class="form-label">Category</label>
                            <select class="form-select" id="cateId">
                                <option value="0">All Categories</option>
                                @foreach ($categories as $cat)
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
                    <div class="register-heading" id="registerHeading" style="display:none;">
                        <h5>ITEM STOCK REGISTER</h5>
                        <p id="registerPeriod"></p>
                    </div>
                    <div class="table-responsive report-scroll">
                        <table id="reportTable" class="stock-register">
                            <thead>
                                <tr>
                                    <th rowspan="2"></th>
                                    <th rowspan="2">Item Name</th>
                                    <th rowspan="2">Opening</th>
                                    <th colspan="2">Purchase</th>
                                    <th colspan="2">Purchase Return</th>
                                    <th colspan="2">Sold</th>
                                    <th colspan="2">Sold Return</th>
                                    <th colspan="2">Damage</th>
                                    <th colspan="2">In Hand Agent</th>
                                    <th colspan="2">Closing</th>
                                </tr>
                                <tr>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Qty</th>
                                    <th>Value</th>
                                    <th>Available</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td colspan="17" class="text-center text-muted">Select filters and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total :</th>
                                    <th class="num" id="totalOpening">0</th>
                                    <th class="num" id="totalPurchase">0</th>
                                    <th class="num" id="totalPurchaseValue">0.00</th>
                                    <th class="num" id="totalPurchaseReturn">0</th>
                                    <th class="num" id="totalPurchaseReturnValue">0.00</th>
                                    <th class="num" id="totalSold">0</th>
                                    <th class="num" id="totalSoldValue">0.00</th>
                                    <th class="num" id="totalSoldReturn">0</th>
                                    <th class="num" id="totalSoldReturnValue">0.00</th>
                                    <th class="num" id="totalDamage">0</th>
                                    <th class="num" id="totalDamageValue">0.00</th>
                                    <th class="num" id="totalAgent">0</th>
                                    <th class="num" id="totalAgentValue">0.00</th>
                                    <th class="num" id="totalAvailable">0</th>
                                    <th class="num" id="totalValue">0.00</th>
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
                                    <th>Item Name</th>
                                    <th class="text-end">MRP</th>
                                    <th class="text-end">Available Qty</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody id="stockDetailBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
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

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function resetTotals() {
            $('#totalOpening, #totalPurchase, #totalPurchaseReturn, #totalSold, #totalSoldReturn, #totalDamage, #totalAgent, #totalAvailable').text('0');
            $('#totalPurchaseValue, #totalPurchaseReturnValue, #totalSoldValue, #totalSoldReturnValue, #totalDamageValue, #totalAgentValue, #totalValue').text('0.00');
        }

        function resetTable(message, isError) {
            printRows = [];
            $('#printBtn').prop('disabled', true);
            $('#registerHeading').hide();
            resetTotals();
            const cls = isError ? 'text-danger' : 'text-muted';
            $('#reportBody').html(
                `<tr><td colspan="17" class="text-center ${cls}">${message}</td></tr>`
            );
        }

        function statementCells(row) {
            return {
                opening: parseFloat(row.Opening_Qty) || 0,
                purchase: parseFloat(row.Purchase_Qty) || 0,
                purchaseValue: parseFloat(row.Purchase_Value) || 0,
                purchaseReturn: parseFloat(row.Purchase_Return_Qty) || 0,
                purchaseReturnValue: parseFloat(row.Purchase_Return_Value) || 0,
                sold: parseFloat(row.Sold_Qty) || 0,
                soldValue: parseFloat(row.Sold_Value) || 0,
                soldReturn: parseFloat(row.Sold_Return_Qty) || 0,
                soldReturnValue: parseFloat(row.Sold_Return_Value) || 0,
                damage: parseFloat(row.Damage_Qty) || 0,
                damageValue: parseFloat(row.Damage_Value) || 0,
                agent: parseFloat(row.Agent_Qty) || 0,
                agentValue: parseFloat(row.Agent_Value) || 0,
                available: parseFloat(row.Available) || 0,
                value: parseFloat(row.Value) || 0
            };
        }

        function addTotals(totals, cells) {
            totals.opening += cells.opening;
            totals.purchase += cells.purchase;
            totals.purchaseValue += cells.purchaseValue;
            totals.purchaseReturn += cells.purchaseReturn;
            totals.purchaseReturnValue += cells.purchaseReturnValue;
            totals.sold += cells.sold;
            totals.soldValue += cells.soldValue;
            totals.soldReturn += cells.soldReturn;
            totals.soldReturnValue += cells.soldReturnValue;
            totals.damage += cells.damage;
            totals.damageValue += cells.damageValue;
            totals.agent += cells.agent;
            totals.agentValue += cells.agentValue;
            totals.available += cells.available;
            totals.value += cells.value;
        }

        function blankTotals() {
            return {
                opening: 0, purchase: 0, purchaseValue: 0,
                purchaseReturn: 0, purchaseReturnValue: 0,
                sold: 0, soldValue: 0, soldReturn: 0, soldReturnValue: 0,
                damage: 0, damageValue: 0, agent: 0, agentValue: 0,
                available: 0, value: 0
            };
        }

        function statementRowHtml(cells) {
            return `
                    <td style="text-align:right;">${fmtQty(cells.opening)}</td>
                    <td style="text-align:right;">${fmtQty(cells.purchase)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.purchaseValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.purchaseReturn)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.purchaseReturnValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.sold)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.soldValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.soldReturn)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.soldReturnValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.damage)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.damageValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.agent)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.agentValue)}</td>
                    <td style="text-align:right;">${fmtQty(cells.available)}</td>
                    <td style="text-align:right;">${fmtAmt(cells.value)}</td>`;
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
            const frm = (window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#frmDate').val()) : $('#frmDate').val();
            const to = (window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#toDate').val()) : $('#toDate').val();
            let body = '';
            const totals = blankTotals();
            printRows.forEach(function(row) {
                const cells = statementCells(row);
                addTotals(totals, cells);
                body += `<tr>
                    <td>${esc(row.Prod_ShortNm)}</td>
                    ${statementRowHtml(cells)}
                </tr>`;
            });
            body += `<tr>
                <th style="text-align:right;">Total :</th>
                <th style="text-align:right;">${fmtQty(totals.opening)}</th>
                <th style="text-align:right;">${fmtQty(totals.purchase)}</th>
                <th style="text-align:right;">${fmtAmt(totals.purchaseValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.purchaseReturn)}</th>
                <th style="text-align:right;">${fmtAmt(totals.purchaseReturnValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.sold)}</th>
                <th style="text-align:right;">${fmtAmt(totals.soldValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.soldReturn)}</th>
                <th style="text-align:right;">${fmtAmt(totals.soldReturnValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.damage)}</th>
                <th style="text-align:right;">${fmtAmt(totals.damageValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.agent)}</th>
                <th style="text-align:right;">${fmtAmt(totals.agentValue)}</th>
                <th style="text-align:right;">${fmtQty(totals.available)}</th>
                <th style="text-align:right;">${fmtAmt(totals.value)}</th>
            </tr>`;
            siPrint(`<!DOCTYPE html><html><head><title>Item Stock Register</title>
                <style>
                    @page { size: landscape; }
                    body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
                    h3, h4, p { margin: 0 0 4px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 3px 4px; }
                    th { font-weight: 700; text-align: center; }
                </style></head><body>
                <h3>${orgName || 'Smart Inventory'}</h3>
                ${branchName ? `<p>${branchName}</p>` : ''}
                <h4>ITEM STOCK REGISTER FROM ${frm} TO ${to}</h4>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">Item Name</th>
                            <th rowspan="2">Opening</th>
                            <th colspan="2">Purchase</th>
                            <th colspan="2">Purchase Return</th>
                            <th colspan="2">Sold</th>
                            <th colspan="2">Sold Return</th>
                            <th colspan="2">Damage</th>
                            <th colspan="2">In Hand Agent</th>
                            <th colspan="2">Closing</th>
                        </tr>
                        <tr>
                            <th>Qty</th><th>Value</th>
                            <th>Qty</th><th>Value</th>
                            <th>Qty</th><th>Value</th>
                            <th>Qty</th><th>Value</th>
                            <th>Qty</th><th>Value</th>
                            <th>Qty</th><th>Value</th>
                            <th>Available</th><th>Value</th>
                        </tr>
                    </thead>
                    <tbody>${body}</tbody>
                </table>
                </body></html>`);
        }

        $('#cateId').on('change', function() {
            loadSubCats($(this).val());
        });

        $('#printBtn').on('click', printStock);

        $(document).on('click', '.stock-detail-btn', function() {
            const row = printRows[$(this).data('idx')];
            if (!row) return;
            $('#stockDetailTitle').text('Barcode Stock — ' + (row.Prod_ShortNm || ''));
            $('#stockDetailBody').html('<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>');
            $('#stockDetailModal').modal('show');
            $.get("{{ route('stock-statement.details') }}", {
                to_date: $('#toDate').val(),
                prod_id: row.Prod_Id
            }, function(lines) {
                if (!lines.length) {
                    $('#stockDetailBody').html('<tr><td colspan="6" class="text-center text-muted">No barcode stock found</td></tr>');
                    return;
                }
                let html = '';
                lines.forEach(function(line, idx) {
                    const barcode = line.Barcode && line.Barcode != 0 ? line.Barcode : '';
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${esc(barcode)}</td>
                        <td>${esc(line.Prod_ShortNm)}</td>
                        <td class="text-end">${fmtAmt(line.MRP)}</td>
                        <td class="text-end">${fmtQty(line.Avil_Qnty)}</td>
                        <td class="text-end">${fmtAmt(line.Value)}</td>
                    </tr>`;
                });
                $('#stockDetailBody').html(html);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load barcode stock';
                $('#stockDetailBody').html(`<tr><td colspan="6" class="text-center text-danger">${esc(msg)}</td></tr>`);
            });
        });

        $('#searchBtn').on('click', function() {
            const frmDate = $('#frmDate').val();
            const toDate = $('#toDate').val();
            if (!frmDate || !toDate) {
                Swal.fire('Error', 'Select from date and to date', 'error');
                return;
            }
            if (frmDate > toDate) {
                Swal.fire('Error', 'From date cannot be after to date', 'error');
                return;
            }

            resetTable('Loading...', false);
            $.get("{{ route('stock-statement.search') }}", {
                frm_date: frmDate,
                to_date: toDate,
                cat_id: $('#cateId').val() || 0,
                sub_cat_id: $('#subCateId').val() || 0
            }, function(data) {
                if (!data.length) {
                    resetTable('No stock found', false);
                    return;
                }
                printRows = data;
                let html = '';
                const totals = blankTotals();
                data.forEach(function(row, idx) {
                    const cells = statementCells(row);
                    addTotals(totals, cells);
                    html += `<tr>
                        <td><button type="button" class="detail-link stock-detail-btn" data-idx="${idx}">Detail</button></td>
                        <td class="item-name">${esc(row.Prod_ShortNm)}</td>
                        <td class="num">${fmtQty(cells.opening)}</td>
                        <td class="num">${fmtQty(cells.purchase)}</td>
                        <td class="num">${fmtAmt(cells.purchaseValue)}</td>
                        <td class="num">${fmtQty(cells.purchaseReturn)}</td>
                        <td class="num">${fmtAmt(cells.purchaseReturnValue)}</td>
                        <td class="num">${fmtQty(cells.sold)}</td>
                        <td class="num">${fmtAmt(cells.soldValue)}</td>
                        <td class="num">${fmtQty(cells.soldReturn)}</td>
                        <td class="num">${fmtAmt(cells.soldReturnValue)}</td>
                        <td class="num">${fmtQty(cells.damage)}</td>
                        <td class="num">${fmtAmt(cells.damageValue)}</td>
                        <td class="num">${fmtQty(cells.agent)}</td>
                        <td class="num">${fmtAmt(cells.agentValue)}</td>
                        <td class="num">${fmtQty(cells.available)}</td>
                        <td class="num">${fmtAmt(cells.value)}</td>
                    </tr>`;
                });
                $('#reportBody').html(html);
                $('#totalOpening').text(fmtQty(totals.opening));
                $('#totalPurchase').text(fmtQty(totals.purchase));
                $('#totalPurchaseValue').text(fmtAmt(totals.purchaseValue));
                $('#totalPurchaseReturn').text(fmtQty(totals.purchaseReturn));
                $('#totalPurchaseReturnValue').text(fmtAmt(totals.purchaseReturnValue));
                $('#totalSold').text(fmtQty(totals.sold));
                $('#totalSoldValue').text(fmtAmt(totals.soldValue));
                $('#totalSoldReturn').text(fmtQty(totals.soldReturn));
                $('#totalSoldReturnValue').text(fmtAmt(totals.soldReturnValue));
                $('#totalDamage').text(fmtQty(totals.damage));
                $('#totalDamageValue').text(fmtAmt(totals.damageValue));
                $('#totalAgent').text(fmtQty(totals.agent));
                $('#totalAgentValue').text(fmtAmt(totals.agentValue));
                $('#totalAvailable').text(fmtQty(totals.available));
                $('#totalValue').text(fmtAmt(totals.value));
                const frmText = (window.siDate && siDate.toDisplay) ? siDate.toDisplay(frmDate) : frmDate;
                const toText = (window.siDate && siDate.toDisplay) ? siDate.toDisplay(toDate) : toDate;
                $('#registerPeriod').text('FROM ' + frmText + ' TO ' + toText);
                $('#registerHeading').show();
                $('#printBtn').prop('disabled', false);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load stock statement';
                Swal.fire('Error', msg, 'error');
                resetTable('Failed to load', true);
            });
        });
    </script>
@endpush
