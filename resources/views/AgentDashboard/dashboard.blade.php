@extends('AgentDashboard.Layouts.layout')

@php
    $agentName = session('agent_name', 'Agent');
    $branchName = session('branch_name', 'Branch');
    $orgName = session('org_name', 'Organization');
    $yearDesc = session('year_desc', 'Accounting Year');
    $yearStart = session('year_start');
    $yearEnd = session('year_end');
    $now = \Carbon\Carbon::now('Asia/Kolkata');
    $hour = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $fmtDate = function ($value) {
        if (!$value) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Exception $e) {
            return $value;
        }
    };
@endphp

@push('style')
    <style>
        .si-welcome {
            background: linear-gradient(135deg, #3F6AD8 0%, #2b4fb3 55%, #1e3a8a 100%);
            border-radius: 16px;
            overflow: hidden;
            color: #fff;
        }

        .si-welcome h4,
        .si-welcome p,
        .si-welcome span {
            color: #fff;
        }

        .si-kpi {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(31, 45, 84, 0.06);
        }

        .si-kpi .si-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .bg-primary-subtle { background: #e8eefc; color: #3F6AD8; }
        .bg-success-subtle { background: #e8f8ef; color: #198754; }
        .bg-warning-subtle { background: #fff6e5; color: #d39e00; }
        .bg-info-subtle { background: #e7f6fb; color: #0dcaf0; }
        .bg-danger-subtle { background: #fdecec; color: #dc3545; }

        a.si-stat-link {
            display: flex;
            width: 100%;
            text-decoration: none;
            color: inherit;
        }
        a.si-stat-link:hover,
        a.si-stat-link:focus {
            text-decoration: none;
            color: inherit;
        }
        a.si-stat-link .dash-count {
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="mb-1">Dashboard</h5>
                    <p class="text-muted mb-0">{{ $orgName }} · {{ $branchName }}</p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="fa fa-calendar me-1 text-primary"></i>{{ $now->format('l, d M Y') }}
                    </span>
                    @foreach (collect($agentMenuLinks ?? [])->filter(fn ($link) => !str_starts_with($link->route, 'agent.report.'))->take(2) as $link)
                        <a href="{{ route($link->route) }}" class="btn {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="{{ $link->icon }} me-1"></i>{{ $link->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="si-welcome position-relative mb-4 p-4">
                <div>
                    <h4 class="mb-1">{{ $greeting }}, {{ $agentName }}</h4>
                    <p class="mb-3" style="opacity:.85;">Track your sales, requisitions and customer returns for this month.</p>
                    <div class="d-flex flex-wrap" style="gap:16px;">
                        <span><i class="fa fa-user me-1"></i>{{ session('agent_code') }}</span>
                        <span><i class="fa fa-building me-1"></i>{{ $branchName }}</span>
                        <span><i class="fa fa-calendar me-1"></i>{{ $yearDesc }} ({{ $fmtDate($yearStart) }} – {{ $fmtDate($yearEnd) }})</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('agenttemplate/assets/img/icons/dash3.svg') }}" alt=""></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>₹ {{ number_format($stats?->agent_sales_month ?? $stats?->agent_sales_year ?? 0, 2) }}</h5>
                            <h6>Agent Sales</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash1">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('agenttemplate/assets/img/icons/dash1.svg') }}" alt=""></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>₹ {{ number_format($stats?->requisitions_month ?? $stats?->requisitions_year ?? 0, 2) }}</h5>
                            <h6>Requisitions</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash2">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('agenttemplate/assets/img/icons/dash2.svg') }}" alt=""></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>₹ {{ number_format($stats?->customer_returns_month ?? $stats?->customer_returns_year ?? 0, 2) }}</h5>
                            <h6>Customer Returns</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash3">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('agenttemplate/assets/img/icons/dash4.svg') }}" alt=""></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>{{ number_format($stats?->stock_with_agent ?? 0, 0) }}</h5>
                            <h6>Stock with Agent</h6>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $agentStatLinks = [
                    ['route' => 'agent.report.sale', 'params' => ['today' => 1], 'amount' => $stats?->today_sales ?? 0, 'label' => "Today's Sales", 'class' => ''],
                    ['route' => 'agent.report.return', 'params' => ['today' => 1], 'amount' => $stats?->today_return ?? 0, 'label' => "Today's Return", 'class' => 'das1'],
                    ['route' => 'agent.report.indent', 'params' => ['today' => 1], 'amount' => $stats?->pending_indents ?? 0, 'label' => 'Pending Indents', 'class' => 'das2', 'format' => 'number'],
                    ['route' => 'agent.report.stock', 'params' => ['today' => 1], 'amount' => $stats?->low_stock_count ?? 0, 'label' => 'Low Stock Items', 'class' => 'das3', 'format' => 'number'],
                ];
            @endphp
            <div class="row">
                @foreach ($agentStatLinks as $stat)
                    @php
                        $statUrl = menuLinkRoute($agentMenuLinks ?? [], $stat['route'], $stat['params']);
                        $statValue = ($stat['format'] ?? '') === 'number'
                            ? number_format($stat['amount'], 0)
                            : '₹ ' . number_format($stat['amount'], 2);
                    @endphp
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        @if ($statUrl)
                            <a href="{{ $statUrl }}" class="si-stat-link">
                        @else
                            <div class="si-stat-link">
                        @endif
                                <div class="dash-count {{ $stat['class'] }}">
                                    <div class="dash-counts">
                                        <h4>{{ $statValue }}</h4>
                                        <h5>{{ $stat['label'] }}</h5>
                                    </div>
                                    <div class="dash-imgs"><i data-feather="{{ $loop->index === 0 ? 'shopping-cart' : ($loop->index === 1 ? 'rotate-ccw' : ($loop->index === 2 ? 'clipboard' : 'alert-triangle')) }}"></i></div>
                                </div>
                        @if ($statUrl)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-lg-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0">Sales Trend</h5>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <select id="selAgentChartMonth" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="all">All months</option>
                                </select>
                                <div class="graph-sets mb-0">
                                    <ul>
                                        <li><span>Sales</span></li>
                                        <li><span>Returns</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="agent_sales_charts"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Low Stock with You</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($lowStock ?? [] as $item)
                                            <tr>
                                                <td>{{ $item->Prod_Name }}</td>
                                                <td>{{ number_format((float) $item->On_Hand, 0) }}</td>
                                                <td>
                                                    @if (($item->Alert_Status ?? '') === 'Critical')
                                                        <span class="badge bg-danger">Critical</span>
                                                    @else
                                                        <span class="badge bg-warning">Low</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">No low stock items</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @php $requisitionUrl = menuLinkRoute($agentMenuLinks ?? [], 'agent.requisition'); @endphp
                            @if ($requisitionUrl)
                                <a href="{{ $requisitionUrl }}" class="btn btn-light w-100 mt-3">Raise Requisition</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var el = document.querySelector('#agent_sales_charts');
            if (!el || typeof ApexCharts === 'undefined') {
                return;
            }

            var chartData = @json($chartData ?? []);
            if (!Array.isArray(chartData)) {
                chartData = [];
            }

            var currentMonthKey = @json($now->format('Y-m'));
            var monthSelect = document.getElementById('selAgentChartMonth');
            var selectedKey = 'all';

            chartData.forEach(function(row, idx) {
                if (monthSelect) {
                    var opt = document.createElement('option');
                    opt.value = String(idx);
                    opt.textContent = row.Month_Label;
                    monthSelect.appendChild(opt);
                }
                if (String(row.Month_Key) === currentMonthKey) {
                    selectedKey = String(idx);
                }
            });

            if (selectedKey === 'all' && chartData.length) {
                selectedKey = String(chartData.length - 1);
            }

            if (monthSelect) {
                monthSelect.value = selectedKey;
            }

            function periodSeries(key) {
                if (key === 'all') {
                    return {
                        labels: chartData.map(function(r) { return r.Month_Label; }),
                        sales: chartData.map(function(r) { return Number(r.Sales_Amt || 0); }),
                        returns: chartData.map(function(r) { return Number(r.Return_Amt || 0); })
                    };
                }
                var row = chartData[Number(key)];
                return {
                    labels: [row ? row.Month_Label : 'Month'],
                    sales: [row ? Number(row.Sales_Amt || 0) : 0],
                    returns: [row ? Number(row.Return_Amt || 0) : 0]
                };
            }

            var initial = periodSeries(selectedKey);
            if (!initial.labels.length) {
                initial = { labels: ['No data'], sales: [0], returns: [0] };
            }

            var chart = new ApexCharts(el, {
                series: [
                    { name: 'Sales', data: initial.sales },
                    { name: 'Returns', data: initial.returns }
                ],
                colors: ['#28C76F', '#EA5455'],
                chart: {
                    type: 'bar',
                    height: 300,
                    stacked: false,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: selectedKey === 'all' ? '40%' : '20%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: { categories: initial.labels },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('en-IN', {
                                maximumFractionDigits: 0
                            });
                        }
                    }
                },
                legend: { position: 'right', offsetY: 40 },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '₹ ' + Number(val).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            });
            chart.render();

            if (monthSelect) {
                monthSelect.addEventListener('change', function() {
                    var key = monthSelect.value;
                    var period = periodSeries(key);
                    if (!period.labels.length) {
                        period = { labels: ['No data'], sales: [0], returns: [0] };
                    }
                    chart.updateOptions({
                        xaxis: { categories: period.labels },
                        plotOptions: {
                            bar: { columnWidth: key === 'all' ? '40%' : '20%' }
                        },
                        series: [
                            { name: 'Sales', data: period.sales },
                            { name: 'Returns', data: period.returns }
                        ]
                    });
                });
            }
        })();
    </script>
@endpush
