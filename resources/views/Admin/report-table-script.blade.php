<script>
    const orgName = @json($org_name);
    const branchName = @json($branch_name);
    const reportCfg = @json($reportCfg);
    const pageTitle = reportCfg.title || '';
    const slug = reportCfg.slug || '';
    const searchUrl = reportCfg.searchUrl || '';
    const subcatUrl = "{{ route('stock-summary.subcats') }}";
    let printRows = [];
    let totalRecordsCount = null;
    let lastSearchPayload = null;
    let reportLazy = null;
    let footerTotals = {};
    let activeColumns = reportCfg.columns || [];
    let activeTotals = reportCfg.totals || [];

    function fmtQty(n) {
        return (window.siFmtQty || function (v) {
            const n = parseFloat(v);
            return isNaN(n) ? '0' : String(Math.round(n));
        })(n);
    }

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function fmtDrCr(n) {
        const v = parseFloat(n);
        if (isNaN(v) || Math.abs(v) < 0.005) return '0.00';
        if (v < 0) return Math.abs(v).toFixed(2) + ' Cr';
        return v.toFixed(2) + ' Dr';
    }

    function isPartyLedgerMode() {
        return (slug === 'supplier-register' || slug === 'customer-register')
            && String($('input[name="reportView"]:checked').val()) === '2';
    }

    function fmtAmtBlank(n) {
        const v = parseFloat(n);
        if (!v) return '';
        return fmtAmt(v);
    }

    function fmtCell(col, row) {
        const val = row[col.key];
        if (col.type === 'indent_print') {
            const indentId = row.Indent_Id || '';
            if (!indentId) return '';
            return `<div class="text-center">
                <button type="button" class="btn btn-sm btn-outline-primary btn-reprint-indent"
                    data-indent-id="${indentId}" title="Reprint Indent Bill">
                    <i class="fas fa-print"></i>
                </button>
            </div>`;
        }
        if (col.type === 'date') {
            return (window.siDate && siDate.toDisplay) ? siDate.toDisplay(val) : (val || '');
        }
        if (col.type === 'amount') {
            if (val === null || val === undefined || val === '') return '';
            return fmtAmt(val);
        }
        if (col.type === 'drcr') {
            if (val === null || val === undefined || val === '') return '';
            return fmtDrCr(val);
        }
        if (col.type === 'amount_blank') {
            return fmtAmtBlank(val);
        }
        if (col.type === 'qty') return fmtQty(val);
        return val == null ? '' : val;
    }

    function currentColumns() {
        if (isPartyLedgerMode()) {
            return reportCfg.ledger_columns || [];
        }
        if (slug === 'agent-register' && String($('#modeId').val()) === '2') {
            return reportCfg.stock_columns || [];
        }
        if (slug === 'agent-register') {
            return reportCfg.indent_columns || reportCfg.columns || [];
        }
        return reportCfg.columns || [];
    }

    function currentTotals() {
        if (isPartyLedgerMode()) {
            return reportCfg.ledger_totals || [];
        }
        if (slug === 'agent-register' && String($('#modeId').val()) === '2') {
            return reportCfg.stock_totals || [];
        }
        if (slug === 'agent-register') {
            return reportCfg.indent_totals || reportCfg.totals || [];
        }
        return reportCfg.totals || [];
    }

    function renderHead() {
        activeColumns = currentColumns();
        activeTotals = currentTotals();
        let head = '<th>Sl</th>';
        activeColumns.forEach(function(col) {
            const cls = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty' || col.type === 'drcr') ? ' class="text-end"' : '';
            head += `<th${cls}>${col.label}</th>`;
        });
        $('#reportHead').html(head);
        let foot = '<th class="text-end">Total</th>';
        if (!activeTotals.length) {
            foot = '<th colspan="' + (activeColumns.length + 1) + '"></th>';
        } else {
            activeColumns.forEach(function(col) {
                if (activeTotals.indexOf(col.key) >= 0 || col.key === reportCfg.closing_key) {
                    foot += `<th class="text-end" data-total="${col.key}">0</th>`;
                } else {
                    foot += '<th></th>';
                }
            });
        }
        $('#reportFoot').html(foot);
    }

    function toggleLedgerPartyFilter() {
        if (!isPartyLedgerMode()) {
            $('.ledger-party-filter').addClass('d-none');
            $('#ledgerReportBanner').addClass('d-none').html('');
            $('#reportTable').removeClass('table-bordered party-ledger-table');
            return;
        }
        $('.ledger-party-filter').removeClass('d-none');
    }

    function selectedPartyMeta() {
        const $opt = $('#partyId option:selected');
        let name = $opt.data('name') || '';
        let code = $opt.data('code') || '';
        const label = String($opt.text() || '').trim();
        if ((!name || !code) && label) {
            const dash = label.lastIndexOf('-');
            if (dash > 0) {
                name = name || label.slice(0, dash);
                code = code || label.slice(dash + 1);
            } else {
                name = name || label;
            }
        }
        return {
            id: $opt.val() || '',
            code: code,
            name: name
        };
    }

    function partyLedgerTitle(row) {
        const party = row || {};
        const meta = selectedPartyMeta();
        const partyName = party.Party_Name || meta.name || '';
        const partyCode = party.Party_Code || meta.code || '';
        const frm = $('#frmDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#frmDate').val()) : $('#frmDate').val())
            : '';
        const to = $('#toDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#toDate').val()) : $('#toDate').val())
            : '';
        const range = (frm && to) ? ` From ${frm} To ${to}` : '';
        return `Party Ledger Of ${partyName}-${partyCode}${range}`;
    }

    function renderLedgerBanner(row) {
        const title = partyLedgerTitle(row);
        $('#ledgerReportBanner').removeClass('d-none').html(
            `<h5 class="mb-1 fw-bold">${orgName || 'Smart Inventory'}</h5>` +
            (branchName ? `<div class="mb-1">${branchName}</div>` : '') +
            `<div class="fw-semibold">${title}</div>`
        );
        $('#reportTable').addClass('table-bordered party-ledger-table');
    }

    function renderLedgerBody(data) {
        let html = '';
        data.forEach(function(row, idx) {
            const trCls = parseInt(row.Row_Kind, 10) === 1 ? ' class="fw-bold"' : '';
            html += `<tr${trCls}><td>${idx + 1}</td>`;
            activeColumns.forEach(function(col) {
                const cls = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') ? ' class="text-end"' : '';
                html += `<td${cls}>${fmtCell(col, row)}</td>`;
            });
            html += '</tr>';
        });
        return html;
    }

    function renderLedgerPrintBody() {
        let body = '';
        printRows.forEach(function(row, idx) {
            const weight = parseInt(row.Row_Kind, 10) === 1 ? ' font-weight:bold;' : '';
            body += `<tr><td style="text-align:center;${weight}">${idx + 1}</td>`;
            activeColumns.forEach(function(col) {
                let align = '';
                if (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') {
                    align = ' style="text-align:right;' + weight + '"';
                } else if (col.type === 'date') {
                    align = ' style="text-align:center;' + weight + '"';
                } else if (weight) {
                    align = ' style="' + weight + '"';
                }
                body += `<td${align}>${fmtCell(col, row)}</td>`;
            });
            body += '</tr>';
        });
        return body;
    }

    function printPartyLedger() {
        const frm = $('#frmDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#frmDate').val()) : $('#frmDate').val())
            : '';
        const to = $('#toDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#toDate').val()) : $('#toDate').val())
            : '';
        const title = partyLedgerTitle(printRows[0] || {});
        let head = '<th style="width:40px;">Sl</th>';
        activeColumns.forEach(function(col) {
            const align = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') ? ' style="text-align:right;"' : '';
            head += `<th${align}>${col.label}</th>`;
        });
        const body = renderLedgerPrintBody();
        siPrint(`<!DOCTYPE html><html><head><title>Party Ledger</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 24px; }
                .header { text-align: center; margin-bottom: 12px; }
                .header h3 { margin: 0 0 4px 0; font-size: 18px; }
                .header p { margin: 0 0 4px 0; }
                .header h4 { margin: 8px 0 0 0; font-size: 14px; font-weight: 600; }
                .page-no { text-align: right; font-size: 11px; margin-bottom: 4px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { font-weight: bold; }
            </style></head><body>
            <div class="header">
                <h3>${orgName || 'Smart Inventory'}</h3>
                ${branchName ? `<p>${branchName}</p>` : ''}
                <h4>${title}</h4>
            </div>
            <div class="page-no">Page 1 of 1</div>
            <table><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table>
            </body></html>`);
    }

    function resetTable(message, isError) {
        printRows = [];
        totalRecordsCount = null;
        $('#printBtn').prop('disabled', true);
        $('#downloadBtn').prop('disabled', true);
        $('#ledgerReportBanner').addClass('d-none').html('');
        $('#reportTable').removeClass('table-bordered party-ledger-table');
        renderHead();
        const cls = isError ? 'text-danger' : 'text-muted';
        $('#reportBody').html(
            `<tr><td colspan="${activeColumns.length + 1}" class="text-center ${cls}">${message}</td></tr>`
        );
    }

    function toggleAgentDates() {
        if (slug !== 'agent-register') return;
        const isStock = String($('#modeId').val()) === '2';
        $('.filter-from-to').toggle(!isStock);
        $('.filter-as-on').toggle(isStock);
    }

    function applyFooterTotals(totals, rows) {
        const sums = {};
        activeTotals.forEach(function(k) { sums[k] = 0; });
        if (totals && Object.keys(totals).length) {
            Object.keys(totals).forEach(function(k) { sums[k] = parseFloat(totals[k]) || 0; });
        } else {
            (rows || []).forEach(function(row) {
                if ((parseInt(row.Row_Kind, 10) || 0) !== 0) return;
                activeTotals.forEach(function(k) {
                    sums[k] += parseFloat(row[k]) || 0;
                });
            });
        }
        footerTotals = sums;
        activeColumns.forEach(function(col) {
            if (col.key === reportCfg.closing_key) {
                const last = (rows && rows.length) ? rows[rows.length - 1][col.key] : 0;
                $(`#reportFoot [data-total="${col.key}"]`).text(fmtDrCr(last));
                return;
            }
            if (!sums.hasOwnProperty(col.key)) return;
            const v = (col.type === 'amount') ? fmtAmt(sums[col.key]) : fmtQty(sums[col.key]);
            $(`#reportFoot [data-total="${col.key}"]`).text(v);
        });
    }

    function buildSearchPayload() {
        const payload = {
            frm_date: $('#frmDate').val() || '',
            to_date: $('#toDate').val() || '',
            as_on_date: $('#asOnDate').val() || '',
            party_id: $('#partyId').val() || 0,
            agent_id: $('#agentId').val() || 0,
            mode: $('input[name="reportView"]:checked').val() || $('#modeId').val() || 0,
            cat_id: $('#cateId').val() || 0,
            sub_cat_id: $('#subCateId').val() || 0,
            ledger_id: $('#ledgerId').val() || 0
        };
        if (reportCfg.extraParams) {
            Object.entries(reportCfg.extraParams).forEach(function([key, selector]) {
                payload[key] = $(selector).val() || 0;
            });
        }
        return payload;
    }

    function renderReportRow(row, idx) {
        const kind = parseInt(row.Row_Kind, 10) || 0;
        const trCls = kind > 0 ? ' class="fw-bold"' : '';
        let html = `<tr${trCls}><td>${idx + 1}</td>`;
        activeColumns.forEach(function(col) {
            const cls = (col.type === 'amount' || col.type === 'qty' || col.type === 'drcr' || col.type === 'amount_blank') ? ' class="text-end"' : '';
            html += `<td${cls}>${col.footer_only ? '' : fmtCell(col, row)}</td>`;
        });
        html += '</tr>';
        return html;
    }

    function getReportPeriodLabel() {
        const asOn = $('#asOnDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#asOnDate').val()) : $('#asOnDate').val())
            : '';
        const frm = $('#frmDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#frmDate').val()) : $('#frmDate').val())
            : '';
        const to = $('#toDate').length
            ? ((window.siDate && siDate.toDisplay) ? siDate.toDisplay($('#toDate').val()) : $('#toDate').val())
            : '';
        let period = '';
        if (slug === 'agent-commission' && $('#monthId').length && $('#yearId').length) {
            period = $('#monthId option:selected').text() + ' ' + $('#yearId').val();
            if ($('#agentId').length && $('#agentId').val() && $('#agentId').val() !== '0') {
                period += ' | Agent: ' + $('#agentId option:selected').text();
            } else {
                period += ' | All Agents';
            }
            period += ' | Settled sales only';
        } else if (slug === 'agent-register' && String($('#modeId').val()) === '2') {
            period = 'As on: ' + asOn;
        } else if (frm && to) {
            period = 'From ' + frm + ' To ' + to;
        } else if (asOn) {
            period = 'As on: ' + asOn;
        }
        if (slug === 'supplier-register' || slug === 'customer-register') {
            const viewLabel = String($('input[name="reportView"]:checked').val()) === '2' ? 'Party Ledger' : 'Detailed List';
            period += (period ? ' | ' : '') + viewLabel;
        }
        if ($('#ledgerId').length && $('#ledgerId').val() && $('#ledgerId').val() !== '0') {
            period += (period ? ' | ' : '') + 'Ledger: ' + $('#ledgerId option:selected').text();
        }
        return period;
    }

    function buildReportCsv(rows) {
        const meta = [
            ['Organization', orgName || 'Smart Inventory'],
            ['Branch', branchName || ''],
            ['Report', pageTitle || 'Report'],
            ['Period', getReportPeriodLabel()],
            ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
        ];

        const exportCols = activeColumns.filter(function(col) {
            return col.type !== 'indent_print';
        });

        const headers = ['Sl'].concat(exportCols.map(function(c) { return c.label; }));
        const sums = {};
        activeTotals.forEach(function(k) { sums[k] = 0; });

        const dataRows = (rows || []).map(function(row, idx) {
            const kind = parseInt(row.Row_Kind, 10) || 0;
            const r = [String(idx + 1)];
            exportCols.forEach(function(col) {
                const val = col.footer_only ? '' : fmtCell(col, row);
                r.push(val);
                if (kind === 0 && sums.hasOwnProperty(col.key)) {
                    sums[col.key] += parseFloat(row[col.key]) || 0;
                }
            });
            return r;
        });

        let totalsRow = null;
        if (activeTotals.length) {
            totalsRow = ['Total'];
            exportCols.forEach(function(col) {
                if (col.key === reportCfg.closing_key) {
                    const last = (rows && rows.length) ? rows[rows.length - 1][col.key] : 0;
                    totalsRow.push(fmtDrCr(last));
                } else if (sums.hasOwnProperty(col.key)) {
                    const v = (col.type === 'amount') ? fmtAmt(sums[col.key]) : fmtQty(sums[col.key]);
                    totalsRow.push(v);
                } else {
                    totalsRow.push('');
                }
            });
        }

        return siBuildCsv({
            meta: meta,
            headers: headers,
            rows: dataRows,
            totals: totalsRow
        });
    }

    function buildReportHtml(rows) {
        if (isPartyLedgerMode()) {
            const title = partyLedgerTitle(rows[0] || {});
            let head = '<th style="width:40px;">Sl</th>';
            activeColumns.forEach(function(col) {
                const align = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') ? ' style="text-align:right;"' : '';
                head += `<th${align}>${col.label}</th>`;
            });
            let body = '';
            rows.forEach(function(row, idx) {
                const weight = parseInt(row.Row_Kind, 10) === 1 ? ' font-weight:bold;' : '';
                body += `<tr><td style="text-align:center;${weight}">${idx + 1}</td>`;
                activeColumns.forEach(function(col) {
                    let align = '';
                    if (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') {
                        align = ' style="text-align:right;' + weight + '"';
                    } else if (col.type === 'date') {
                        align = ' style="text-align:center;' + weight + '"';
                    } else if (weight) {
                        align = ' style="' + weight + '"';
                    }
                    body += `<td${align}>${fmtCell(col, row)}</td>`;
                });
                body += '</tr>';
            });
            return `<!DOCTYPE html><html><head><title>Party Ledger</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 24px; }
                    .header { text-align: center; margin-bottom: 12px; }
                    .header h3 { margin: 0 0 4px 0; font-size: 18px; }
                    .header p { margin: 0 0 4px 0; }
                    .header h4 { margin: 8px 0 0 0; font-size: 14px; font-weight: 600; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 4px 6px; }
                    th { font-weight: bold; background: #f0f0f0; }
                </style></head><body>
                <div class="header">
                    <h3>${orgName || 'Smart Inventory'}</h3>
                    ${branchName ? `<p>${branchName}</p>` : ''}
                    <h4>${title}</h4>
                </div>
                <table><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table>
                </body></html>`;
        }

        const period = getReportPeriodLabel();
        let head = '<th>Sl</th>';
        const printCols = activeColumns.filter(function(col) {
            return col.type !== 'indent_print';
        });
        printCols.forEach(function(col) { head += `<th>${col.label}</th>`; });
        const sums = {};
        activeTotals.forEach(function(k) { sums[k] = 0; });
        let body = '';
        rows.forEach(function(row, idx) {
            const kind = parseInt(row.Row_Kind, 10) || 0;
            const weight = kind > 0 ? ' font-weight:bold;' : '';
            body += `<tr><td>${idx + 1}</td>`;
            printCols.forEach(function(col) {
                const align = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty' || col.type === 'drcr') ? ' style="text-align:right;' + weight + '"' : (weight ? ' style="' + weight + '"' : '');
                body += `<td${align}>${col.footer_only ? '' : fmtCell(col, row)}</td>`;
                if (kind === 0 && sums.hasOwnProperty(col.key)) sums[col.key] += parseFloat(row[col.key]) || 0;
            });
            body += '</tr>';
        });

        let totalRow = '';
        if (activeTotals.length) {
            totalRow = '<tr><th>Total</th>';
            printCols.forEach(function(col) {
                if (col.key === reportCfg.closing_key) {
                    const last = rows.length ? rows[rows.length - 1][col.key] : 0;
                    totalRow += `<th style="text-align:right;">${fmtDrCr(last)}</th>`;
                } else if (sums.hasOwnProperty(col.key)) {
                    const v = (col.type === 'amount') ? fmtAmt(sums[col.key]) : fmtQty(sums[col.key]);
                    totalRow += `<th style="text-align:right;">${v}</th>`;
                } else {
                    totalRow += '<th></th>';
                }
            });
            totalRow += '</tr>';
        }

        return `<!DOCTYPE html><html><head><title>${pageTitle}</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>${pageTitle}</h4>
            <p>${branchName ? branchName + ' &nbsp;|&nbsp; ' : ''}${period}</p>
            <table><thead><tr>${head}</tr></thead><tbody>${body}${totalRow}</tbody></table>
            </body></html>`;
    }

    function downloadReportZip(prefetchedRows) {
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
                    const csvContent = buildReportCsv(rows);
                    const htmlContent = buildReportHtml(rows);
                    const safeName = siSanitizeFilename(pageTitle || 'Report') + '_' + siDateStamp();
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

        runDownload(printRows);
    }

    function printReport() {
        // Guard 1: Fast check if search already revealed > 1000 rows
        if (totalRecordsCount !== null && totalRecordsCount > 1000) {
            siWarnPrintLimit(totalRecordsCount, downloadReportZip);
            return;
        }

        // Guard 2: If rows in memory exceed 1000
        if (printRows && printRows.length > 1000) {
            siWarnPrintLimit(printRows.length, downloadReportZip);
            return;
        }

        function runPrint(rows) {
            printRows = rows || [];
            if (!printRows.length) {
                Swal.fire('Error', 'Search the report first', 'error');
                return;
            }
            if (printRows.length > 1000) {
                siWarnPrintLimit(printRows.length, function() {
                    downloadReportZip(printRows);
                });
                return;
            }
            doPrintReport();
        }

        if (lastSearchPayload && searchUrl) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Preparing Print...',
                    text: 'Loading report records',
                    allowOutsideClick: false,
                    didOpen: function() { Swal.showLoading(); }
                });
            }
            $.get(searchUrl, $.extend({}, lastSearchPayload, { limit: 0 }), function(data) {
                if (window.Swal) Swal.close();
                const allRows = window.SiLazyScroll && SiLazyScroll.unwrap ? SiLazyScroll.unwrap(data) : (Array.isArray(data) ? data : []);
                totalRecordsCount = allRows.length;
                runPrint(allRows);
            }).fail(function(xhr) {
                if (window.Swal) {
                    Swal.close();
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load report for print', 'error');
                }
            });
            return;
        }
        runPrint(printRows);
    }

    function doPrintReport() {
        if (isPartyLedgerMode()) {
            printPartyLedger();
            return;
        }
        const html = buildReportHtml(printRows);
        siPrint(html);
    }

    $('#cateId').on('change', function() {
        $('#subCateId').html('<option value="0">All Sub Categories</option>');
        const catId = parseInt($(this).val(), 10) || 0;
        if (!catId) return;
        $.get(subcatUrl, { cat_id: catId }, function(subs) {
            (subs || []).forEach(function(s) {
                $('#subCateId').append(`<option value="${s.Prd_SubCateId}">${s.Prd_SubCateNm}</option>`);
            });
        });
    });

    $('#modeId').on('change', function() {
        toggleAgentDates();
        resetTable('Select filters and click Search', false);
    });

    $('input[name="reportView"]').on('change', function() {
        toggleLedgerPartyFilter();
        renderHead();
        resetTable('Select filters and click Search', false);
    });

    $('#printBtn').on('click', printReport);
    $(document).on('click', '#downloadBtn', function() {
        downloadReportZip();
    });

    $('#searchBtn').on('click', function() {
        const payload = buildSearchPayload();
        if ((slug === 'supplier-register' || slug === 'customer-register') && String(payload.mode) === '2') {
            if (!payload.party_id) {
                Swal.fire('Error', slug === 'customer-register' ? 'Select a customer' : 'Select a supplier', 'error');
                return;
            }
        }
        lastSearchPayload = payload;
        printRows = [];
        totalRecordsCount = null;
        footerTotals = {};
        $('#printBtn').prop('disabled', true);
        $('#downloadBtn').prop('disabled', true);
        renderHead();
        if (isPartyLedgerMode()) {
            $('#reportFoot').html(`<th colspan="${activeColumns.length + 1}"></th>`);
        } else {
            $('#ledgerReportBanner').addClass('d-none').html('');
            $('#reportTable').removeClass('table-bordered party-ledger-table');
        }
        if (reportLazy) {
            reportLazy.destroy();
        }
        const span = (activeColumns.length || 1) + 1;
        reportLazy = SiLazyScroll.create({
            scrollEl: $('#reportTable').closest('.report-scroll, .table-responsive'),
            tbody: '#reportBody',
            url: searchUrl,
            pageSize: 40,
            showEndRow: false,
            colSpan: function() { return (activeColumns.length || 1) + 1; },
            params: function() { return lastSearchPayload; },
            emptyHtml: `<tr><td colspan="${span}" class="text-center text-muted">No records found</td></tr>`,
            renderRow: function(row, idx) {
                return renderReportRow(row, idx);
            },
            onChunk: function(rows, payload, reset, meta) {
                if (reset) {
                    printRows = [];
                    totalRecordsCount = (payload && payload.recordsTotal != null) ? Number(payload.recordsTotal) : null;
                }
                printRows = printRows.concat(rows);
                if (isPartyLedgerMode() && printRows.length) {
                    renderLedgerBanner(printRows[0]);
                }
                if (!isPartyLedgerMode()) {
                    if (payload && payload.totals && Object.keys(payload.totals).length) {
                        applyFooterTotals(payload.totals, printRows);
                    } else if (footerTotals && Object.keys(footerTotals).length) {
                        applyFooterTotals(footerTotals, printRows);
                    } else {
                        applyFooterTotals(null, printRows);
                    }
                }
                $('#printBtn').prop('disabled', printRows.length === 0);
                $('#downloadBtn').prop('disabled', printRows.length === 0);
            },
            onError: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load report';
                Swal.fire('Error', msg, 'error');
            }
        });
        reportLazy.resetAndLoad();
    });

    $('#reportTable').closest('.table-responsive').addClass('report-scroll');
    renderHead();
    toggleLedgerPartyFilter();
    toggleAgentDates();
    if (slug === 'agent-register') {
        $('.filter-as-on').hide();
    }

    if (typeof siInitAllSelect2 === 'function') {
        siInitAllSelect2(document);
    }
</script>
