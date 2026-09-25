<script>
    const orgName = @json($org_name);
    const branchName = @json($branch_name);
    const reportCfg = @json($reportCfg);
    const pageTitle = reportCfg.title || '';
    const slug = reportCfg.slug || '';
    const searchUrl = reportCfg.searchUrl || '';
    const subcatUrl = "{{ route('stock-summary.subcats') }}";
    let printRows = [];
    let activeColumns = reportCfg.columns || [];
    let activeTotals = reportCfg.totals || [];

    function fmtQty(n) {
        const v = parseFloat(n);
        if (isNaN(v)) return '0';
        return Number.isInteger(v) ? String(v) : v.toFixed(2);
    }

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
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
            const cls = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') ? ' class="text-end"' : '';
            head += `<th${cls}>${col.label}</th>`;
        });
        $('#reportHead').html(head);
        let foot = '<th class="text-end">Total</th>';
        if (!activeTotals.length) {
            foot = '<th colspan="' + (activeColumns.length + 1) + '"></th>';
        } else {
            activeColumns.forEach(function(col) {
                if (activeTotals.indexOf(col.key) >= 0) {
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
        return {
            id: $opt.val() || '',
            code: $opt.data('code') || '',
            name: $opt.data('name') || $opt.text() || ''
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
        $('#printBtn').prop('disabled', true);
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

    function printReport() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (isPartyLedgerMode()) {
            printPartyLedger();
            return;
        }
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
        if (slug === 'agent-register' && String($('#modeId').val()) === '2') {
            period = 'As on: ' + asOn;
        } else if (frm && to) {
            period = 'From ' + frm + ' to ' + to;
        } else if (asOn) {
            period = 'As on: ' + asOn;
        }
        if (slug === 'supplier-register' || slug === 'customer-register') {
            const viewLabel = String($('input[name="reportView"]:checked').val()) === '2' ? 'Party Ledger' : 'Detailed List';
            period += (period ? ' &nbsp;|&nbsp; ' : '') + viewLabel;
        }
        if ($('#ledgerId').length && $('#ledgerId').val() && $('#ledgerId').val() !== '0') {
            period += (period ? ' &nbsp;|&nbsp; ' : '') + 'Ledger: ' + $('#ledgerId option:selected').text();
        }
        let head = '<th>Sl</th>';
        const printCols = activeColumns.filter(function(col) {
            return col.type !== 'indent_print';
        });
        printCols.forEach(function(col) { head += `<th>${col.label}</th>`; });
        const sums = {};
        activeTotals.forEach(function(k) { sums[k] = 0; });
        let body = isPartyLedgerMode() ? renderLedgerPrintBody() : '';
        if (!isPartyLedgerMode()) {
        printRows.forEach(function(row, idx) {
            const kind = parseInt(row.Row_Kind, 10) || 0;
            const weight = kind > 0 ? ' font-weight:bold;' : '';
            body += `<tr><td>${idx + 1}</td>`;
            printCols.forEach(function(col) {
                const align = (col.type === 'amount' || col.type === 'amount_blank' || col.type === 'qty') ? ' style="text-align:right;' + weight + '"' : (weight ? ' style="' + weight + '"' : '');
                body += `<td${align}>${fmtCell(col, row)}</td>`;
                if (kind === 0 && sums.hasOwnProperty(col.key)) sums[col.key] += parseFloat(row[col.key]) || 0;
            });
            body += '</tr>';
        });
        }
        let totalRow = '';
        if (activeTotals.length) {
            totalRow = '<tr><th>Total</th>';
            printCols.forEach(function(col) {
                if (sums.hasOwnProperty(col.key)) {
                    const v = (col.type === 'amount') ? fmtAmt(sums[col.key]) : fmtQty(sums[col.key]);
                    totalRow += `<th style="text-align:right;">${v}</th>`;
                } else {
                    totalRow += '<th></th>';
                }
            });
            totalRow += '</tr>';
        }
        siPrint(`<!DOCTYPE html><html><head><title>${pageTitle}</title>
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
            </body></html>`);
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

    $('#searchBtn').on('click', function() {
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
        // merge any extra params defined in reportCfg.extraParams
        if (reportCfg.extraParams) {
            Object.entries(reportCfg.extraParams).forEach(function([key, selector]) {
                payload[key] = $(selector).val() || 0;
            });
        }
        if ((slug === 'supplier-register' || slug === 'customer-register') && String(payload.mode) === '2') {
            if (!payload.party_id) {
                Swal.fire('Error', slug === 'customer-register' ? 'Select a customer' : 'Select a supplier', 'error');
                return;
            }
        }
        resetTable('Loading...', false);
        $.get(searchUrl, payload, function(data) {
            if (!data.length) {
                resetTable('No records found', false);
                return;
            }
            printRows = data;
            if (isPartyLedgerMode()) {
                renderLedgerBanner(data[0] || {});
                $('#reportBody').html(renderLedgerBody(data));
                $('#reportFoot').html(`<th colspan="${activeColumns.length + 1}"></th>`);
                $('#printBtn').prop('disabled', false);
                return;
            }
            $('#ledgerReportBanner').addClass('d-none').html('');
            $('#reportTable').removeClass('table-bordered party-ledger-table');
            const sums = {};
            activeTotals.forEach(function(k) { sums[k] = 0; });
            let html = '';
            data.forEach(function(row, idx) {
                const kind = parseInt(row.Row_Kind, 10) || 0;
                const trCls = kind > 0 ? ' class="fw-bold"' : '';
                html += `<tr${trCls}><td>${idx + 1}</td>`;
                activeColumns.forEach(function(col) {
                    const cls = (col.type === 'amount' || col.type === 'qty') ? ' class="text-end"' : '';
                    html += `<td${cls}>${fmtCell(col, row)}</td>`;
                    if (kind === 0 && sums.hasOwnProperty(col.key)) sums[col.key] += parseFloat(row[col.key]) || 0;
                });
                html += '</tr>';
            });
            $('#reportBody').html(html);
            activeColumns.forEach(function(col) {
                if (!sums.hasOwnProperty(col.key)) return;
                const v = (col.type === 'amount') ? fmtAmt(sums[col.key]) : fmtQty(sums[col.key]);
                $(`#reportFoot [data-total="${col.key}"]`).text(v);
            });
            $('#printBtn').prop('disabled', false);
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.message || 'Failed to load report';
            Swal.fire('Error', msg, 'error');
            resetTable('Failed to load', true);
        });
    });

    $('#reportTable').closest('.table-responsive').addClass('report-scroll');
    renderHead();
    toggleLedgerPartyFilter();
    toggleAgentDates();
    if (slug === 'agent-register') {
        $('.filter-as-on').hide();
    }
</script>
