@extends('Dashboard.Layouts.layout')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h6 class="ps-2">Agent Commission Report</h6>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Agent</label>
                            <select class="form-select" id="agentId" data-si-ajax="agents">
                                <option value="0">All Agents</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Month<span class="text-danger">*</span></label>
                            <select class="form-select" id="monthId">
                                @foreach ([
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                ] as $num => $label)
                                    <option value="{{ $num }}" @selected($num === $cur_month)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Year<span class="text-danger">*</span></label>
                            <select class="form-select" id="yearId">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" @selected((int) $y === (int) $cur_year)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 d-flex align-items-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-success" id="generateBtn" disabled>
                                <i class="fas fa-save me-1"></i> Generate Commission
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="printBtn" disabled>
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <button type="button" class="btn btn-outline-success" id="downloadBtn" disabled>
                                <i class="fas fa-download me-1"></i> Download
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        Commission is calculated only on settled agent sales (settle date within selected month).
                        Use View for category-wise breakup. Generate Commission saves the details for the period.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive report-scroll">
                        <table id="reportTable" class="table table-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Agent Code</th>
                                    <th>Agent Name</th>
                                    <th class="text-end">Total Sale</th>
                                    <th class="text-end">Total Commission</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Select filters and click Search</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr id="reportFoot">
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end" id="footSale">0.00</th>
                                    <th class="text-end" id="footComm">0.00</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Category-wise Commission</h5>
                        <div class="text-muted small" id="commDetailSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-nowrap mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Category</th>
                                    <th class="text-end">Sale Amt</th>
                                    <th class="text-end">Comm %</th>
                                    <th class="text-end">Commission</th>
                                </tr>
                            </thead>
                            <tbody id="commDetailBody"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total</th>
                                    <th class="text-end" id="commDetailSale">0.00</th>
                                    <th></th>
                                    <th class="text-end" id="commDetailComm">0.00</th>
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
    const searchUrl = @json(route('agent-commission.search'));
    const detailsUrl = @json(route('agent-commission.details'));
    const generateUrl = @json(route('agent-commission.generate'));
    const csrfToken = @json(csrf_token());
    let printRows = [];

    function fmtAmt(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function filterPayload() {
        return {
            agent_id: $('#agentId').val() || 0,
            month: $('#monthId').val() || 0,
            year: $('#yearId').val() || 0
        };
    }

    function periodLabel() {
        return $('#monthId option:selected').text() + ' ' + $('#yearId').val();
    }

    function resetTable(message, isError) {
        printRows = [];
        $('#printBtn').prop('disabled', true);
        $('#downloadBtn').prop('disabled', true);
        $('#generateBtn').prop('disabled', true);
        $('#footSale').text('0.00');
        $('#footComm').text('0.00');
        const cls = isError ? 'text-danger' : 'text-muted';
        $('#reportBody').html(
            `<tr><td colspan="7" class="text-center ${cls}">${message}</td></tr>`
        );
    }

    function renderRows(data) {
        printRows = data || [];
        if (!printRows.length) {
            resetTable('No records found', false);
            return;
        }

        let saleSum = 0;
        let commSum = 0;
        let html = '';
        printRows.forEach(function(row, idx) {
            saleSum += parseFloat(row.Total_Sale) || 0;
            commSum += parseFloat(row.Total_Comm) || 0;
            const generated = parseInt(row.Is_Generated, 10) === 1;
            const status = generated
                ? '<span class="badge bg-success">Generated</span>'
                : '<span class="badge bg-secondary">Pending</span>';
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Agent_Code || ''}</td>
                <td>${row.Agent_Name || ''}</td>
                <td class="text-end">${fmtAmt(row.Total_Sale)}</td>
                <td class="text-end">${fmtAmt(row.Total_Comm)}</td>
                <td>${status}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-view-comm"
                        data-agent-id="${row.Agent_Id}"
                        data-agent-name="${(row.Agent_Name || '').replace(/"/g, '&quot;')}">
                        View
                    </button>
                </td>
            </tr>`;
        });
        $('#reportBody').html(html);
        $('#footSale').text(fmtAmt(saleSum));
        $('#footComm').text(fmtAmt(commSum));
        $('#printBtn').prop('disabled', false);
        $('#downloadBtn').prop('disabled', false);
        const pendingCount = printRows.filter(function(r) {
            return parseInt(r.Is_Generated, 10) !== 1;
        }).length;
        $('#generateBtn').prop('disabled', pendingCount === 0);
        if (pendingCount === 0) {
            $('#generateBtn').attr('title', 'Commission already generated for this month');
        } else {
            $('#generateBtn').removeAttr('title');
        }
    }

    function openCategoryModal(agentId, agentName) {
        const payload = filterPayload();
        payload.agent_id = agentId;
        $('#commDetailBody').html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
        $('#commDetailSub').text((agentName || '') + ' | ' + periodLabel());
        $('#commDetailSale').text('0.00');
        $('#commDetailComm').text('0.00');
        const modal = new bootstrap.Modal(document.getElementById('commDetailModal'));
        modal.show();

        $.get(detailsUrl, payload, function(res) {
            const rows = res.rows || [];
            let html = '';
            if (!rows.length) {
                html = '<tr><td colspan="5" class="text-center text-muted">No category details found</td></tr>';
            } else {
                rows.forEach(function(row, idx) {
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${row.Cate_Name || ''}</td>
                        <td class="text-end">${fmtAmt(row.Total_Sale)}</td>
                        <td class="text-end">${fmtAmt(row.Comm_Percent)}</td>
                        <td class="text-end">${fmtAmt(row.Total_Comm)}</td>
                    </tr>`;
                });
            }
            $('#commDetailBody').html(html);
            $('#commDetailSale').text(fmtAmt(res.total_sale));
            $('#commDetailComm').text(fmtAmt(res.total_comm));
            const statusTxt = parseInt(res.is_generated, 10) === 1
                ? 'Saved commission details'
                : 'Calculated from settled sales (not generated yet)';
            $('#commDetailSub').text(
                ((res.agent && res.agent.Agent_Name) ? res.agent.Agent_Name : agentName) +
                ' (' + ((res.agent && res.agent.Agent_Code) ? res.agent.Agent_Code : '') + ') | ' +
                periodLabel() + ' | ' + statusTxt
            );
        }).fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load details';
            $('#commDetailBody').html(`<tr><td colspan="5" class="text-center text-danger">${msg}</td></tr>`);
        });
    }

    function buildCommissionCsv(rows) {
        const agentTxt = ($('#agentId').val() && $('#agentId').val() !== '0')
            ? $('#agentId option:selected').text()
            : 'All Agents';

        const meta = [
            ['Organization', orgName || 'Smart Inventory'],
            ['Branch', branchName || ''],
            ['Report', 'Agent Commission Report'],
            ['Period', periodLabel()],
            ['Agent', agentTxt],
            ['Generated At', (window.siDate && siDate.toDisplay ? siDate.toDisplay(new Date()) : new Date().toLocaleString())]
        ];

        const headers = ['Sl', 'Agent Code', 'Agent Name', 'Total Sale', 'Total Commission', 'Status'];

        let saleSum = 0;
        let commSum = 0;
        const dataRows = (rows || []).map(function(row, idx) {
            saleSum += parseFloat(row.Total_Sale) || 0;
            commSum += parseFloat(row.Total_Comm) || 0;
            const status = parseInt(row.Is_Generated, 10) === 1 ? 'Generated' : 'Pending';
            return [
                String(idx + 1),
                row.Agent_Code || '',
                row.Agent_Name || '',
                fmtAmt(row.Total_Sale),
                fmtAmt(row.Total_Comm),
                status
            ];
        });

        const totals = ['Total', '', '', fmtAmt(saleSum), fmtAmt(commSum), ''];

        return siBuildCsv({
            meta: meta,
            headers: headers,
            rows: dataRows,
            totals: totals
        });
    }

    function buildCommissionHtml(rows) {
        let body = '';
        let saleSum = 0;
        let commSum = 0;
        (rows || []).forEach(function(row, idx) {
            saleSum += parseFloat(row.Total_Sale) || 0;
            commSum += parseFloat(row.Total_Comm) || 0;
            const status = parseInt(row.Is_Generated, 10) === 1 ? 'Generated' : 'Pending';
            body += `<tr>
                <td>${idx + 1}</td>
                <td>${row.Agent_Code || ''}</td>
                <td>${row.Agent_Name || ''}</td>
                <td style="text-align:right;">${fmtAmt(row.Total_Sale)}</td>
                <td style="text-align:right;">${fmtAmt(row.Total_Comm)}</td>
                <td>${status}</td>
            </tr>`;
        });
        body += `<tr>
            <th colspan="3" style="text-align:right;">Total</th>
            <th style="text-align:right;">${fmtAmt(saleSum)}</th>
            <th style="text-align:right;">${fmtAmt(commSum)}</th>
            <th></th>
        </tr>`;

        const agentTxt = ($('#agentId').val() && $('#agentId').val() !== '0')
            ? $('#agentId option:selected').text()
            : 'All Agents';

        return `<!DOCTYPE html><html><head><title>Agent Commission Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                h3, h4, p { margin: 0 0 6px 0; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 12px; }
                th, td { border: 1px solid #000; padding: 4px 6px; }
                th { background: #f0f0f0; }
            </style></head><body>
            <h3>${orgName || 'Smart Inventory'}</h3>
            <h4>Agent Commission Report</h4>
            <p>${branchName ? branchName + ' &nbsp;|&nbsp; ' : ''}${periodLabel()} &nbsp;|&nbsp; ${agentTxt} &nbsp;|&nbsp; Settled sales only</p>
            <table>
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Agent Code</th>
                        <th>Agent Name</th>
                        <th>Total Sale</th>
                        <th>Total Commission</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${body}</tbody>
            </table>
            </body></html>`;
    }

    function downloadCommissionZip() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (window.Swal) {
            Swal.fire({
                title: 'Generating ZIP...',
                text: 'Packaging records',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });
        }
        setTimeout(function() {
            try {
                const csvContent = buildCommissionCsv(printRows);
                const htmlContent = buildCommissionHtml(printRows);
                const safeName = 'Agent_Commission_' + siDateStamp();
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

    function printReport() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        if (printRows.length > 1000) {
            siWarnPrintLimit(printRows.length, downloadCommissionZip);
            return;
        }
        const html = buildCommissionHtml(printRows);
        siPrint(html);
    }

    $('#searchBtn').on('click', function() {
        resetTable('Loading...', false);
        $.get(searchUrl, filterPayload(), function(data) {
            renderRows(window.SiLazyScroll && SiLazyScroll.unwrap ? SiLazyScroll.unwrap(data) : data);
        }).fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load report';
            Swal.fire('Error', msg, 'error');
            resetTable('Failed to load', true);
        });
    });

    $('#printBtn').on('click', printReport);
    $(document).on('click', '#downloadBtn', function() {
        downloadCommissionZip();
    });

    $(document).on('click', '.btn-view-comm', function() {
        openCategoryModal($(this).data('agent-id'), $(this).data('agent-name'));
    });

    $('#generateBtn').on('click', function() {
        if (!printRows.length) {
            Swal.fire('Error', 'Search the report first', 'error');
            return;
        }
        const pendingCount = printRows.filter(function(r) {
            return parseInt(r.Is_Generated, 10) !== 1;
        }).length;
        if (pendingCount === 0) {
            Swal.fire('Not Allowed', 'Commission already generated for this month. Regeneration is not allowed.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Generate Commission',
            text: 'Generate and save commission for pending agent(s) in ' + periodLabel() + '? This cannot be regenerated later.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Generate',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $('#generateBtn').prop('disabled', true);
            $.post(generateUrl, Object.assign({ _token: csrfToken }, filterPayload()), function(res) {
                Swal.fire('Success', res.message || 'Commission generated', 'success');
                $('#searchBtn').trigger('click');
            }).fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to generate commission';
                Swal.fire('Error', msg, 'error');
                const stillPending = printRows.filter(function(r) {
                    return parseInt(r.Is_Generated, 10) !== 1;
                }).length;
                $('#generateBtn').prop('disabled', stillPending === 0);
            });
        });
    });
</script>
@endpush
