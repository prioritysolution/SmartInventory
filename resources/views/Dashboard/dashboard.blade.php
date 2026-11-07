@extends('Dashboard.Layouts.layout')

@section('title', 'Dashboard | Smart Inventory')

@php
    $userName = session('user_name', 'User');
    $branchName = session('branch_name', 'Branch');
    $orgName = session('org_name', 'Organization');
    $yearDesc = session('year_desc', 'Accounting Year');
    $yearStart = session('year_start');
    $yearEnd = session('year_end');
    $gstHave = (int) session('gst_have', 0);
    $gstType = session('gst_type', '');
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

        .si-icon.me-2 {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .si-period-btn {
            border: 1px solid #dbe0ea;
            background: #fff;
            color: #334155;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 12px;
        }

        .si-period-btn.is-active {
            background: #3F6AD8;
            border-color: #3F6AD8;
            color: #fff;
        }

        #purchase_sales_monthly {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="mb-1">Dashboard</h5>
                    <p class="text-muted mb-0">{{ $orgName }} · {{ $branchName }}</p>
                </div>
            </div>

            <div class="si-welcome position-relative mb-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="mb-1">{{ $greeting }}, {{ $userName }}</h4>
                        <p class="mb-3 opacity-75">Track purchases, counter sales, agent indents, GST and stock in this
                            accounting year.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <span class="fs-13"><i class="fa-solid fa-building me-1"></i>{{ $branchName }}</span>
                            <span class="fs-13"><i class="fa-solid fa-calendar-days me-1"></i>{{ $yearDesc }}
                                ({{ $fmtDate($yearStart) }} – {{ $fmtDate($yearEnd) }})</span>
                            <span class="fs-13"><i class="fa-regular fa-calendar me-1"></i>{{ $now->format('d M Y') }}</span>
                            <span class="fs-13"><i class="fa-regular fa-clock me-1"></i>{{ $now->format('h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="position-absolute end-0 top-50 translate-middle-y p-3 d-none d-md-block">
                    <img src="{{ asset('template/assets/img/icons/dashboard.svg') }}" alt="">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Counter Sales</p>
                                <h5 class="mb-0">₹ {{ number_format($stats?->counter_sales_month ?? 0, 2) }}</h5>
                                <small class="text-success">This month</small>
                            </div>
                            <span class="si-icon bg-warning-subtle text-warning"><i
                                    class="fa-solid fa-cart-shopping"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Goods Received</p>
                                <h5 class="mb-0">₹ {{ number_format($stats?->grn_month ?? 0, 2) }}</h5>
                                <small class="text-success">This month</small>
                            </div>
                            <span class="si-icon bg-success-subtle text-success"><i
                                    class="fa-solid fa-truck-ramp-box"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Agent Indents</p>
                                <h5 class="mb-0">₹ {{ number_format($stats?->agent_indent_month ?? 0, 2) }}</h5>
                                <small class="text-muted">Stock issued this month</small>
                            </div>
                            <span class="si-icon bg-info-subtle text-info"><i class="fa-solid fa-clipboard-list"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Returns</p>
                                <h5 class="mb-0">₹ {{ number_format($stats?->returns_month ?? 0, 2) }}</h5>
                                <small class="text-muted">Sale + purchase + agent</small>
                            </div>
                            <span class="si-icon bg-danger-subtle text-danger"><i
                                    class="fa-solid fa-rotate-left"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body">
                            <p class="mb-1 text-muted">Today's Sales</p>
                            <h5 class="mb-0">₹ {{ number_format($stats?->today_sales ?? 0, 2) }}</h5>
                            <small class="text-success">{{ $stats?->today_sales_bills ?? 0 }} bills</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body">
                            <p class="mb-1 text-muted">Today's Purchase</p>
                            <h5 class="mb-0">₹ {{ number_format($stats?->today_grn ?? 0, 2) }}</h5>
                            <small class="text-muted">{{ $stats?->today_grn_invoices ?? 0 }} invoices</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body">
                            <p class="mb-1 text-muted">Pending Barcodes</p>
                            <h5 class="mb-0">{{ $stats?->pending_barcodes ?? 0 }}</h5>
                            <small class="text-warning">After goods received</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card si-kpi">
                        <div class="card-body">
                            <p class="mb-1 text-muted">Low Stock / Reorder</p>
                            <h5 class="mb-0">{{ $stats?->low_stock_count ?? 0 }}</h5>
                            <small class="text-danger">Below reorder qty</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body pb-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <h6 class="mb-1">Purchase &amp; Sales</h6>
                                    <p class="text-muted mb-0 fs-13" id="psChartSubtitle">Current month</p>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <button type="button" class="si-period-btn" id="btnChartFy">Financial Year</button>
                                    <select id="selChartMonth" class="form-select form-select-sm" style="min-width: 140px;">
                                        <option value="fy">All months</option>
                                    </select>
                                    <p class="fs-13 text-dark d-flex align-items-center mb-0 ms-2"><i
                                            class="fa-solid fa-circle text-primary fs-12 me-1"></i>Sales</p>
                                    <p class="fs-13 text-dark d-flex align-items-center mb-0"><i
                                            class="fa-solid fa-circle text-primary-transparent fs-12 me-1"></i>Purchase</p>
                                </div>
                            </div>
                            <div id="purchase_sales_monthly"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h6 class="mb-2">Sale Summary</h6>
                            <p class="text-muted fs-13 mb-2" id="saleSummarySubtitle">Counter vs agent · current month</p>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                                <p class="d-flex align-items-center fs-13 text-dark mb-0"><i
                                        class="fa-solid fa-circle fs-8 me-1 text-pink"></i>Counter</p>
                                <p class="d-flex align-items-center fs-13 text-dark mb-0"><i
                                        class="fa-solid fa-circle fs-8 me-1 text-secondary"></i>Agent</p>
                            </div>
                            <div id="sale_summary_chart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h6 class="mb-0">Reorder Alerts</h6>
                                    <p class="text-muted fs-13 mb-0">Items below reorder quantity</p>
                                </div>
                                @php
                                    $reorderAction = collect($menuLinks ?? [])->first(
                                        fn ($link) => in_array($link->route, ['reorder-report', 'product-master'], true)
                                    );
                                @endphp
                                @if ($reorderAction)
                                    <a href="{{ route($reorderAction->route) }}" class="btn btn-sm btn-primary">{{ $reorderAction->name }}</a>
                                @endif
                            </div>
                            <div class="table-responsive">
                                <table class="table table-nowrap border mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>On Hand</th>
                                            <th>Reorder Qty</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reorderAlerts as $item)
                                            <tr>
                                                <td>{{ $item->Prod_Name }}</td>
                                                <td>{{ $item->On_Hand }}</td>
                                                <td>{{ $item->ReOrder_Qty }}</td>
                                                <td>
                                                    @if ($item->Alert_Status === 'Critical')
                                                        <span class="badge badge-soft-danger">Critical</span>
                                                    @else
                                                        <span class="badge badge-soft-warning">Low</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">No items below
                                                    reorder quantity</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
            var barEl = document.querySelector('#purchase_sales_monthly');
            var mixEl = document.querySelector('#sale_summary_chart');
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            var chartData = @json($chartData ?? []);
            if (!Array.isArray(chartData)) {
                chartData = [];
            }

            var currentMonthKey = @json($now->format('Y-m'));
            var selectedKey = 'fy';
            chartData.forEach(function(row, idx) {
                if (String(row.Month_Key) === currentMonthKey) {
                    selectedKey = String(idx);
                }
            });
            if (selectedKey === 'fy' && chartData.length) {
                selectedKey = String(chartData.length - 1);
            }

            var barChart = null;
            var mixChart = null;
            var mixCounter = 0;
            var mixAgent = 0;
            var mixTotal = 0;

            var monthSelect = document.getElementById('selChartMonth');
            var fyBtn = document.getElementById('btnChartFy');
            var psSubtitle = document.getElementById('psChartSubtitle');
            var mixSubtitle = document.getElementById('saleSummarySubtitle');

            if (monthSelect) {
                chartData.forEach(function(row, idx) {
                    var opt = document.createElement('option');
                    opt.value = String(idx);
                    opt.textContent = row.Month_Label;
                    monthSelect.appendChild(opt);
                });
                monthSelect.value = selectedKey === 'fy' ? 'fy' : selectedKey;
            }

            function formatMoney(val) {
                return '₹ ' + Number(val).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function mixForPeriod(key) {
                if (key === 'fy') {
                    return chartData.reduce(function(acc, row) {
                        acc.counter += Number(row.Counter_Amt || 0);
                        acc.agent += Number(row.Agent_Amt || 0);
                        return acc;
                    }, { counter: 0, agent: 0 });
                }
                var row = chartData[Number(key)];
                if (!row) {
                    return { counter: 0, agent: 0 };
                }
                return {
                    counter: Number(row.Counter_Amt || 0),
                    agent: Number(row.Agent_Amt || 0)
                };
            }

            function periodLabel(key) {
                if (key === 'fy') {
                    return 'financial year';
                }
                var row = chartData[Number(key)];
                return row ? row.Month_Label : 'selected month';
            }

            function applyPeriod(key, fromSelect) {
                selectedKey = key;
                if (fyBtn) {
                    fyBtn.classList.toggle('is-active', key === 'fy');
                }
                if (monthSelect && !fromSelect) {
                    monthSelect.value = key === 'fy' ? 'fy' : String(key);
                }
                if (psSubtitle) {
                    psSubtitle.textContent = key === 'fy'
                        ? 'Financial year · click a month to filter sale summary'
                        : periodLabel(key) + ' · sale summary follows this month';
                }
                if (mixSubtitle) {
                    mixSubtitle.textContent = 'Counter vs agent · ' + periodLabel(key);
                }
                updateBarChart();
                updateSaleSummary();
            }

            function getPeriodSeries(key) {
                if (key === 'fy') {
                    return {
                        labels: chartData.map(function(r) { return r.Month_Label; }),
                        sales: chartData.map(function(r) { return Number(r.Sales_Amt || 0); }),
                        purchase: chartData.map(function(r) { return Number(r.Purchase_Amt || 0); })
                    };
                }
                var row = chartData[Number(key)];
                return {
                    labels: [row ? row.Month_Label : 'Month'],
                    sales: [row ? Number(row.Sales_Amt || 0) : 0],
                    purchase: [row ? Number(row.Purchase_Amt || 0) : 0]
                };
            }

            function updateBarChart() {
                if (!barChart) {
                    return;
                }
                var period = getPeriodSeries(selectedKey);
                barChart.updateOptions({
                    xaxis: {
                        categories: period.labels
                    },
                    series: [{
                            name: 'Sales',
                            data: period.sales
                        },
                        {
                            name: 'Purchase',
                            data: period.purchase
                        }
                    ]
                }, true, true);
            }

            function updateSaleSummary() {
                if (!mixChart) {
                    return;
                }
                var mix = mixForPeriod(selectedKey);
                mixCounter = mix.counter;
                mixAgent = mix.agent;
                mixTotal = mixCounter + mixAgent;
                mixChart.updateSeries(mixTotal > 0 ? [mixCounter, mixAgent] : [1, 1]);
            }

            if (barEl) {
                var initialBar = getPeriodSeries(selectedKey);

                barChart = new ApexCharts(barEl, {
                    chart: {
                        height: 360,
                        type: 'bar',
                        stacked: false,
                        toolbar: {
                            show: false
                        },
                        events: {
                            dataPointSelection: function(event, ctx, config) {
                                if (selectedKey !== 'fy') {
                                    return;
                                }
                                applyPeriod(String(config.dataPointIndex));
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            borderRadius: 5,
                            columnWidth: '45%',
                            endingShape: 'rounded'
                        }
                    },
                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    colors: ['#7539FF', '#C5B3FF'],
                    series: [{
                            name: 'Sales',
                            data: initialBar.sales
                        },
                        {
                            name: 'Purchase',
                            data: initialBar.purchase
                        }
                    ],
                    grid: {
                        borderColor: '#E2E4E6',
                        strokeDashArray: 5,
                        padding: {
                            right: -10,
                            left: -10
                        }
                    },
                    xaxis: {
                        categories: initialBar.labels
                    },
                    yaxis: {
                        min: 0,
                        labels: {
                            formatter: function(val) {
                                return val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val;
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return formatMoney(val);
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    }
                });
                barChart.render();
            }

            if (mixEl) {
                var initialMix = mixForPeriod(selectedKey);
                mixCounter = initialMix.counter;
                mixAgent = initialMix.agent;
                mixTotal = mixCounter + mixAgent;

                mixChart = new ApexCharts(mixEl, {
                    series: mixTotal > 0 ? [mixCounter, mixAgent] : [1, 1],
                    chart: {
                        type: 'donut',
                        height: 320
                    },
                    labels: ['Counter', 'Agent'],
                    colors: ['#F38BBB', '#5297FE'],
                    plotOptions: {
                        pie: {
                            startAngle: 0,
                            endAngle: 360,
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true
                                    },
                                    value: {
                                        show: true,
                                        formatter: function(val) {
                                            if (mixTotal <= 0) {
                                                return '₹ 0.00';
                                            }
                                            return formatMoney(val);
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function() {
                                            return formatMoney(mixTotal);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        y: {
                            formatter: function(val, opts) {
                                if (mixTotal <= 0) {
                                    return '₹ 0.00';
                                }
                                var actual = [mixCounter, mixAgent][opts.seriesIndex];
                                return formatMoney(actual);
                            }
                        }
                    }
                });
                mixChart.render();
            }

            if (fyBtn) {
                fyBtn.addEventListener('click', function() {
                    applyPeriod('fy');
                });
            }
            if (monthSelect) {
                monthSelect.addEventListener('change', function() {
                    applyPeriod(monthSelect.value, true);
                });
            }

            applyPeriod(selectedKey);
        })();
    </script>
@endpush
