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
                        <div class="col-md-3 d-flex align-items-end gap-2 flex-wrap">
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
                    <div class="d-flex justify-content-end mb-2">
                        <input type="text" class="form-control" id="tableSearch" maxlength="80"
                            placeholder="Search item code or item name in table"
                            style="max-width: 320px;" autocomplete="off">
                    </div>
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
                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3 px-1">
                        <strong>Total Available Qty</strong>
                        <strong id="stockDetailTotalQty">0</strong>
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
        let lastSearchPayload = null;
        let stockLazy = null;
        let detailPrint = null;
        let footerTotals = { agent: 0, counter: 0 };

        function fmtQty(n) {
            return (window.siFmtQty || function (v) {
                const n = parseFloat(v);
                return isNaN(n) ? '0' : String(Math.round(n));
            })(n);
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

        let totalRecordsCount = null;

        function buildStockSummaryCsv(rows) {
            const asOn = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay($('#asOnDate').val())
                : $('#asOnDate').val();
            const cateText = $('#cateId option:selected').text();
            const subText = $('#subCateId option:selected').text();

            const meta = [
                ['Organization', orgName || 'Smart Inventory'],
                ['Branch', branchName || ''],
                ['Report', 'Stock Summary Report'],
                ['As on Date', asOn],
                ['Category', cateText],
                ['Sub Category', subText],
                ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
            ];

            const headers = ['Sl', 'Item Code', 'Item Name', 'Category', 'Unit', 'Agent Quantity', 'Counter Quantity'];

            let totAgent = 0;
            let totCounter = 0;

            const dataRows = (rows || []).map(function(row, idx) {
                const agentQty = parseFloat(row.Agent_Qty) || 0;
                const counterQty = parseFloat(row.Counter_Qty) || 0;
                totAgent += agentQty;
                totCounter += counterQty;
                return [
                    String(idx + 1),
                    row.Prod_Code || '',
                    row.Prod_ShortNm || '',
                    row.Cate_Name || '',
                    row.Unit_Name || '',
                    fmtQty(agentQty),
                    fmtQty(counterQty)
                ];
            });

            const totals = ['Total', '', '', '', '', fmtQty(totAgent), fmtQty(totCounter)];

            return siBuildCsv({
                meta: meta,
                headers: headers,
                rows: dataRows,
                totals: totals
            });
        }

        function buildStockSummaryHtml(rows) {
            const asOn = (window.siDate && siDate.toDisplay)
                ? siDate.toDisplay($('#asOnDate').val())
                : $('#asOnDate').val();
            const cateText = $('#cateId option:selected').text();
            const subText = $('#subCateId option:selected').text();
            let body = '';
            let totAgent = 0;
            let totCounter = 0;
            (rows || []).forEach(function(row, idx) {
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
            return `<!DOCTYPE html><html><head><title>Stock Summary Report</title>
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
                </body></html>`;
        }

        function downloadStockZip(prefetchedRows) {
            function runDownload(rows) {
                if (!rows || !rows.length) {
                    Swal.fire('Error', 'No records to download', 'error');
                    return;
                }
                if (window.Swal) {
                    Swal.fire({
                        title: 'Generating ZIP...',
                        text: 'Packaging ' + rows.length + ' records',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                }
                setTimeout(function() {
                    try {
                        const csvContent = buildStockSummaryCsv(rows);
                        const htmlContent = buildStockSummaryHtml(rows);
                        const safeName = 'Stock_Summary_' + siDateStamp();
                        const zipName = safeName + '.zip';
                        const files = [
                            { name: safeName + '.csv', content: csvContent },
                            { name: safeName + '.html', content: htmlContent }
                        ];
                        siDownloadZip(zipName, files);
                        if (window.Swal) Swal.close();
                    } catch (e) {
                        if (window.Swal) {
                            Swal.close();
                            Swal.fire('Error', 'Failed to generate ZIP: ' + (e.message || e), 'error');
                        }
                    }
                }, 50);
            }

            if (Array.isArray(prefetchedRows) && prefetchedRows.length) {
                runDownload(prefetchedRows);
                return;
            }

            if (lastSearchPayload) {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Fetching Records...',
                        text: 'Please wait while all searched records are fetched for download',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                }
                $.get("{{ route('stock-summary.search') }}", $.extend({}, lastSearchPayload, { limit: 0 }), function(data) {
                    if (window.Swal) Swal.close();
                    const allRows = SiLazyScroll.unwrap(data);
                    runDownload(allRows);
                }).fail(function(xhr) {
                    if (window.Swal) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to fetch records for download', 'error');
                    }
                });
                return;
            }

            runDownload(printRows);
        }

        function printStock() {
            // Guard 1: Fast check if total records > 1000
            if (totalRecordsCount !== null && totalRecordsCount > 1000) {
                siWarnPrintLimit(totalRecordsCount, downloadStockZip);
                return;
            }

            // Guard 2: If rows in memory exceed 1000
            if (printRows && printRows.length > 1000) {
                siWarnPrintLimit(printRows.length, downloadStockZip);
                return;
            }

            function run(rows) {
                printRows = rows || [];
                if (!printRows.length) {
                    Swal.fire('Error', 'Search the report first', 'error');
                    return;
                }
                if (printRows.length > 1000) {
                    siWarnPrintLimit(printRows.length, function() {
                        downloadStockZip(printRows);
                    });
                    return;
                }
                doPrintStock();
            }
            if (lastSearchPayload) {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Preparing Print...',
                        text: 'Loading report records',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                }
                $.get("{{ route('stock-summary.search') }}", $.extend({}, lastSearchPayload, { limit: 0 }), function(data) {
                    if (window.Swal) Swal.close();
                    const allRows = SiLazyScroll.unwrap(data);
                    totalRecordsCount = allRows.length;
                    run(allRows);
                }).fail(function(xhr) {
                    if (window.Swal) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report for print', 'error');
                    }
                });
                return;
            }
            run(printRows);
        }

        function doPrintStock() {
            if (!printRows.length) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            const html = buildStockSummaryHtml(printRows);
            siPrint(html);
        }

        $('#cateId').on('change', function() {
            loadSubCats($(this).val());
        });

        $('#printBtn').on('click', printStock);
        $(document).on('click', '#downloadBtn', function() {
            downloadStockZip();
        });

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
            $('#stockDetailTotalQty').text('0');
            $('#detailPrintBtn').prop('disabled', true);
            detailPrint = null;
            $('#stockDetailModal').modal('show');
            $.get("{{ route('stock-summary.details') }}", {
                as_on_date: $('#asOnDate').val(),
                prod_id: row.Prod_Id
            }, function(lines) {
                if (!lines.length) {
                    $('#stockDetailBody').html('<tr><td colspan="4" class="text-center text-muted">No barcode stock found</td></tr>');
                    $('#stockDetailTotalQty').text('0');
                    return;
                }
                let html = '';
                let totalQty = 0;
                lines.forEach(function(line, idx) {
                    const qty = parseFloat(line.Avil_Qnty) || 0;
                    totalQty += qty;
                    const barcode = line.Barcode && line.Barcode != 0 ? line.Barcode : '';
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${esc(barcode)}</td>
                        <td class="text-end">${fmtAmt(line.MRP)}</td>
                        <td class="text-end">${fmtQty(qty)}</td>
                    </tr>`;
                });
                $('#stockDetailBody').html(html);
                $('#stockDetailTotalQty').text(fmtQty(totalQty));
                detailPrint = {
                    title: row.Prod_ShortNm || '',
                    code: row.Prod_Code || '',
                    lines: lines
                };
                $('#detailPrintBtn').prop('disabled', false);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load barcode stock';
                $('#stockDetailBody').html(`<tr><td colspan="4" class="text-center text-danger">${esc(msg)}</td></tr>`);
                $('#stockDetailTotalQty').text('0');
            });
        });

        function runStockSearch() {
            const asOnDate = $('#asOnDate').val();
            if (!asOnDate) {
                Swal.fire('Error', 'Select as on date', 'error');
                return;
            }

            lastSearchPayload = {
                as_on_date: asOnDate,
                cat_id: $('#cateId').val() || 0,
                sub_cat_id: $('#subCateId').val() || 0,
                item_q: $('#tableSearch').val() || ''
            };
            printRows = [];
            totalRecordsCount = null;
            footerTotals = { agent: 0, counter: 0 };
            $('#printBtn').prop('disabled', true);
            $('#downloadBtn').prop('disabled', true);
            $('#totalAgentQty, #totalCounterQty').text('0');
            if (stockLazy) stockLazy.destroy();
            stockLazy = SiLazyScroll.create({
                scrollEl: $('#stockTable').closest('.report-scroll'),
                tbody: '#stockBody',
                url: "{{ route('stock-summary.search') }}",
                pageSize: 40,
                showEndRow: false,
                colSpan: 8,
                params: function() { return lastSearchPayload; },
                emptyHtml: '<tr><td colspan="8" class="text-center text-muted">No stock found</td></tr>',
                renderRow: function(row, idx) {
                    const agentQty = parseFloat(row.Agent_Qty) || 0;
                    const counterQty = parseFloat(row.Counter_Qty) || 0;
                    return `<tr>
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
                },
                onChunk: function(rows, payload, reset) {
                    if (reset) {
                        printRows = [];
                        totalRecordsCount = (payload && payload.recordsTotal != null) ? Number(payload.recordsTotal) : null;
                    }
                    printRows = printRows.concat(rows);
                    if (payload && payload.totals) {
                        footerTotals.agent = parseFloat(payload.totals.Agent_Qty) || 0;
                        footerTotals.counter = parseFloat(payload.totals.Counter_Qty) || 0;
                    } else if (reset) {
                        footerTotals = { agent: 0, counter: 0 };
                        printRows.forEach(function(row) {
                            footerTotals.agent += parseFloat(row.Agent_Qty) || 0;
                            footerTotals.counter += parseFloat(row.Counter_Qty) || 0;
                        });
                    }
                    $('#totalAgentQty').text(fmtQty(footerTotals.agent));
                    $('#totalCounterQty').text(fmtQty(footerTotals.counter));
                    $('#printBtn').prop('disabled', printRows.length === 0);
                    $('#downloadBtn').prop('disabled', printRows.length === 0);
                },
                onError: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load stock summary', 'error');
                }
            });
            stockLazy.resetAndLoad();
        }

        $('#searchBtn').on('click', runStockSearch);
        let tableSearchTimer = null;
        $('#tableSearch').on('input', function() {
            clearTimeout(tableSearchTimer);
            tableSearchTimer = setTimeout(function() {
                if (!$('#asOnDate').val()) return;
                runStockSearch();
            }, 400);
        });
        $('#tableSearch').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(tableSearchTimer);
                runStockSearch();
            }
        });
    </script>
@endpush
