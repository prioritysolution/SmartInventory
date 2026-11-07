@push('style')
    <style>
        #reportTable { font-size: 13px; }
        #reportTable th,
        #reportTable td { vertical-align: middle; }
        #reportBody td.bill-no {
            font-weight: 600;
            white-space: normal;
            min-width: 170px;
            vertical-align: top;
        }
        #reportBody tr.date-row td {
            font-weight: 700;
            background: #f4f7fb;
        }
        #reportBody tr.bill-total td { font-weight: 700; }
        #reportBody td.num,
        #reportTable th.num { text-align: right; white-space: nowrap; }
        #reportBody td.label { text-align: center; }
    </style>
@endpush

<div class="card">
    <div class="card-body">
        <div class="table-responsive report-scroll">
            <table id="reportTable" class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Bill No</th>
                        <th>Product Name</th>
                        <th>HSN Code</th>
                        <th class="num">Item Qnty</th>
                        <th class="num">Rate</th>
                        <th class="num">Item Total</th>
                        <th class="num">GST Rate(%)</th>
                        <th class="num">Gross GST Amount</th>
                        <th class="num">Round Off</th>
                        <th class="num">Net Amount</th>
                    </tr>
                </thead>
                <tbody id="reportBody">
                    <tr>
                        <td colspan="10" class="text-center text-muted">Select filters and click Search</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-center">GRAND TOTAL</th>
                        <th class="num" id="itemTotal">0.00</th>
                        <th></th>
                        <th class="num" id="gstTotal">0.00</th>
                        <th class="num" id="roundTotal">0.00</th>
                        <th class="num" id="netTotal">0.00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const orgName = @json($org_name);
        const branchName = @json($branch_name);
        const searchUrl = @json($searchUrl);
        const reportTitle = @json($reportTitle);
        const extraFields = @json($extraFields ?? []);
        let printBills = [];
        let totalRecordsCount = null;
        let lastSearchPayload = null;
        let billLazy = null;
        let rawRows = [];

        function fmtQtyUnit(qty, unit) {
            const q = (window.siFmtQty || function (v) {
                const n = parseFloat(v);
                return isNaN(n) ? '0' : String(Math.round(n));
            })(qty);
            const name = String(unit || '').trim();
            return name ? q + ' - ' + name : q;
        }

        function fmtAmt(n) {
            const v = parseFloat(n) || 0;
            return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function fmtPct(n) {
            return (parseFloat(n) || 0).toFixed(2);
        }

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function showDate(value) {
            if (!value) return '';
            const parts = String(value).substring(0, 10).split('-');
            if (parts.length !== 3) return value;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = months[parseInt(parts[1], 10) - 1] || parts[1];
            return parts[2] + '-' + month + '-' + parts[0];
        }

        function groupBills(rows) {
            const bills = [];
            const map = {};
            (rows || []).forEach(function(row) {
                const key = String(row.Trading_Id || (String(row.Invoice_No || '') + '|' + String(row.Invoice_Date || '')));
                if (!map[key]) {
                    map[key] = {
                        Invoice_No: row.Invoice_No || '',
                        Invoice_Date: row.Invoice_Date || '',
                        Pay_Mode: row.Pay_Mode || '',
                        Party_Name: row.Party_Name || '',
                        Agent_Name: row.Agent_Name || '',
                        Round_Off: parseFloat(row.Round_Off) || 0,
                        Items: []
                    };
                    bills.push(map[key]);
                }
                map[key].Items.push(row);
            });
            return bills;
        }

        function billLabel(bill) {
            const who = bill.Party_Name || bill.Agent_Name || '';
            const tail = [bill.Pay_Mode, who].filter(Boolean).join(' ');
            return tail ? (bill.Invoice_No + '/- ' + tail) : bill.Invoice_No;
        }

        function sumItems(items, key) {
            return items.reduce(function(sum, item) {
                return sum + (parseFloat(item[key]) || 0);
            }, 0);
        }

        function dateRow(date) {
            return `<tr class="date-row"><td colspan="10">${esc(showDate(date))}</td></tr>`;
        }

        function billRows(bill) {
            const span = bill.Items.length + 1;
            let html = `<tr><td class="bill-no" rowspan="${span}">${esc(billLabel(bill))}</td>`;
            bill.Items.forEach(function(item, idx) {
                if (idx > 0) html += '<tr>';
                html += `<td>${esc(item.Item_Name)}</td>
                    <td>${esc(item.HSN_No)}</td>
                    <td class="num">${esc(fmtQtyUnit(item.Item_Qty, item.Unit_Name))}</td>
                    <td class="num">${fmtAmt(item.Item_Rate)}</td>
                    <td class="num">${fmtAmt(item.Item_Total)}</td>
                    <td class="num">${fmtPct(item.GST_Prcnt)}</td>
                    <td class="num">${fmtAmt(item.GST_Amt)}</td>
                    <td></td>
                    <td class="num">${fmtAmt(item.Net_Amt)}</td></tr>`;
            });
            html += `<tr class="bill-total">
                <td class="label" colspan="4">BILL TOTAL</td>
                <td class="num">${fmtAmt(sumItems(bill.Items, 'Item_Total'))}</td>
                <td></td>
                <td class="num">${fmtAmt(sumItems(bill.Items, 'GST_Amt'))}</td>
                <td class="num">${fmtAmt(bill.Round_Off)}</td>
                <td class="num">${fmtAmt(sumItems(bill.Items, 'Net_Amt'))}</td>
            </tr>`;
            return html;
        }

        function renderRegister(bills) {
            let html = '';
            let lastDate = null;
            let itemTotal = 0;
            let gstTotal = 0;
            let roundTotal = 0;
            let netTotal = 0;
            bills.forEach(function(bill) {
                if (bill.Invoice_Date !== lastDate) {
                    lastDate = bill.Invoice_Date;
                    html += dateRow(bill.Invoice_Date);
                }
                html += billRows(bill);
                itemTotal += sumItems(bill.Items, 'Item_Total');
                gstTotal += sumItems(bill.Items, 'GST_Amt');
                roundTotal += parseFloat(bill.Round_Off) || 0;
                netTotal += sumItems(bill.Items, 'Net_Amt');
            });
            return { html: html, itemTotal: itemTotal, gstTotal: gstTotal, roundTotal: roundTotal, netTotal: netTotal };
        }

        function resetTable(message, isError) {
            printBills = [];
            rawRows = [];
            totalRecordsCount = null;
            $('#printBtn').prop('disabled', true);
            $('#downloadBtn').prop('disabled', true);
            $('#itemTotal').text(fmtAmt(0));
            $('#gstTotal').text(fmtAmt(0));
            $('#roundTotal').text(fmtAmt(0));
            $('#netTotal').text(fmtAmt(0));
            const cls = isError ? 'text-danger' : 'text-muted';
            $('#reportBody').html(`<tr><td colspan="10" class="text-center ${cls}">${esc(message)}</td></tr>`);
        }

        function buildBillwiseCsv(items) {
            const frm = showDate($('#frmDate').val());
            const to = showDate($('#toDate').val());
            const meta = [
                ['Organization', orgName || 'Smart Inventory'],
                ['Branch', branchName || ''],
                ['Report', reportTitle || 'Register'],
                ['Period', (frm && to ? `From ${frm} To ${to}` : '')],
                ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
            ];

            const headers = [
                'Bill No', 'Date', 'Party / Customer', 'Pay Mode',
                'Product Name', 'HSN Code', 'Item Qty', 'Unit',
                'Rate', 'Item Total', 'GST Rate (%)', 'Gross GST Amount',
                'Round Off', 'Net Amount'
            ];

            let sumItemTotal = 0;
            let sumGst = 0;
            let sumRound = 0;
            let sumNet = 0;

            const rows = (items || []).map(function(item) {
                const itemTot = parseFloat(item.Item_Total) || 0;
                const gstTot = parseFloat(item.GST_Amt) || 0;
                const roundOff = parseFloat(item.Round_Off) || 0;
                const netTot = parseFloat(item.Net_Amt) || 0;

                sumItemTotal += itemTot;
                sumGst += gstTot;
                sumRound += roundOff;
                sumNet += netTot;

                return [
                    item.Invoice_No || '',
                    showDate(item.Invoice_Date),
                    item.Party_Name || item.Agent_Name || '',
                    item.Pay_Mode || '',
                    item.Item_Name || '',
                    item.HSN_No || '',
                    item.Item_Qty != null ? String(item.Item_Qty) : '',
                    item.Unit_Name || '',
                    parseFloat(item.Item_Rate || 0).toFixed(2),
                    itemTot.toFixed(2),
                    parseFloat(item.GST_Prcnt || 0).toFixed(2),
                    gstTot.toFixed(2),
                    roundOff.toFixed(2),
                    netTot.toFixed(2)
                ];
            });

            const totals = [
                'GRAND TOTAL', '', '', '', '', '', '', '', '',
                sumItemTotal.toFixed(2), '', sumGst.toFixed(2), sumRound.toFixed(2), sumNet.toFixed(2)
            ];

            return siBuildCsv({
                meta: meta,
                headers: headers,
                rows: rows,
                totals: totals
            });
        }

        function buildBillwiseHtml(bills) {
            const frm = showDate($('#frmDate').val());
            const to = showDate($('#toDate').val());
            const drawn = renderRegister(bills || []);
            const totalRow = `<tr>
                <th colspan="5" style="text-align:center;">GRAND TOTAL</th>
                <th style="text-align:right;">${fmtAmt(drawn.itemTotal)}</th>
                <th></th>
                <th style="text-align:right;">${fmtAmt(drawn.gstTotal)}</th>
                <th style="text-align:right;">${fmtAmt(drawn.roundTotal)}</th>
                <th style="text-align:right;">${fmtAmt(drawn.netTotal)}</th>
            </tr>`;
            return `<!DOCTYPE html><html><head><title>${esc(reportTitle)}</title>
                <style>
                    @page { size: landscape; }
                    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
                    h3, h4, p { margin: 0 0 4px 0; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
                    th { text-align: center; background: #f0f0f0; }
                    td.num, th.num { text-align: right; white-space: nowrap; }
                    td.label { text-align: center; font-weight: 700; }
                    td.bill-no { font-weight: 700; }
                    tr.date-row td { font-weight: 700; background: #f4f7fb; }
                    tr.bill-total td { font-weight: 700; }
                </style></head><body>
                <h3>${esc(orgName || 'Smart Inventory')}</h3>
                ${branchName ? `<p>${esc(branchName)}</p>` : ''}
                <h4>${esc(reportTitle)} From ${esc(frm)} To ${esc(to)}</h4>
                <table>
                    <thead><tr>
                        <th>Bill No</th><th>Product Name</th><th>HSN Code</th>
                        <th>Item Qnty</th><th>Rate</th><th>Item Total</th>
                        <th>GST Rate(%)</th><th>Gross GST Amount</th><th>Round Off</th><th>Net Amount</th>
                    </tr></thead>
                    <tbody>${drawn.html}${totalRow}</tbody>
                </table>
                </body></html>`;
        }

        function downloadBillwiseZip(prefetchedRows) {
            function runDownload(items) {
                if (!items || !items.length) {
                    Swal.fire('Error', 'No records to download', 'error');
                    return;
                }
                if (window.Swal) {
                    Swal.fire({
                        title: 'Generating ZIP...',
                        text: 'Packaging ' + items.length + ' records',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                }
                setTimeout(function() {
                    try {
                        const bills = groupBills(items);
                        const csvContent = buildBillwiseCsv(items);
                        const htmlContent = buildBillwiseHtml(bills);
                        const safeName = siSanitizeFilename(reportTitle || 'Register') + '_' + siDateStamp();
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

            if (lastSearchPayload && searchUrl) {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Fetching Records...',
                        text: 'Please wait while all searched records are fetched for download',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                }
                $.get(searchUrl, $.extend({}, lastSearchPayload, { limit: 0 }), function(data) {
                    if (window.Swal) Swal.close();
                    const allRows = window.SiLazyScroll && SiLazyScroll.unwrap ? SiLazyScroll.unwrap(data) : (Array.isArray(data) ? data : []);
                    runDownload(allRows);
                }).fail(function(xhr) {
                    if (window.Swal) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to fetch records for download', 'error');
                    }
                });
                return;
            }

            runDownload(rawRows);
        }

        function printRegister() {
            // Guard 1: Fast check if search already revealed > 1000 items
            if (totalRecordsCount !== null && totalRecordsCount > 1000) {
                siWarnPrintLimit(totalRecordsCount, downloadBillwiseZip);
                return;
            }

            // Guard 2: If items in rawRows exceed 1000
            if (rawRows && rawRows.length > 1000) {
                siWarnPrintLimit(rawRows.length, downloadBillwiseZip);
                return;
            }

            if (!printBills.length) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }

            const html = buildBillwiseHtml(printBills);
            siPrint(html);
        }

        $('#printBtn').on('click', printRegister);
        $(document).on('click', '#downloadBtn', function() {
            downloadBillwiseZip();
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
            const payload = { frm_date: frmDate, to_date: toDate };
            Object.keys(extraFields).forEach(function(key) {
                payload[key] = $(extraFields[key]).val() || 0;
            });
            lastSearchPayload = payload;
            rawRows = [];
            printBills = [];
            totalRecordsCount = null;
            $('#printBtn').prop('disabled', true);
            $('#downloadBtn').prop('disabled', true);
            if (billLazy) billLazy.destroy();
            billLazy = SiLazyScroll.create({
                scrollEl: $('#reportTable').closest('.report-scroll'),
                tbody: '#reportBody',
                url: searchUrl,
                pageSize: 40,
                showEndRow: false,
                colSpan: 10,
                params: function() { return lastSearchPayload; },
                emptyHtml: '<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>',
                onChunk: function(rows, payload, reset) {
                    if (reset) {
                        rawRows = [];
                        totalRecordsCount = (payload && payload.recordsTotal != null) ? Number(payload.recordsTotal) : null;
                    }
                    rawRows = rawRows.concat(rows);
                    const bills = groupBills(rawRows);
                    if (!bills.length) {
                        resetTable('No records found', false);
                        return;
                    }
                    printBills = bills;
                    const drawn = renderRegister(bills);
                    $('#reportBody').html(drawn.html);
                    $('#itemTotal').text(fmtAmt(drawn.itemTotal));
                    $('#gstTotal').text(fmtAmt(drawn.gstTotal));
                    $('#roundTotal').text(fmtAmt(drawn.roundTotal));
                    $('#netTotal').text(fmtAmt(drawn.netTotal));
                    $('#printBtn').prop('disabled', false);
                    $('#downloadBtn').prop('disabled', false);
                },
                onError: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report', 'error');
                    resetTable('Failed to load', true);
                }
            });
            billLazy.resetAndLoad();
        });
    </script>
@endpush
