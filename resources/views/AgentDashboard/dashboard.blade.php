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
                    <a href="{{ route('agent.sale') }}" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i>New Sale
                    </a>
                    <a href="{{ route('agent.requisition') }}" class="btn btn-outline-primary">
                        <i class="fa fa-clipboard me-1"></i>Requisition
                    </a>
                </div>
            </div>

            <div class="si-welcome position-relative mb-4 p-4">
                <div>
                    <h4 class="mb-1">{{ $greeting }}, {{ $agentName }}</h4>
                    <p class="mb-3" style="opacity:.85;">Track your sales, requisitions and customer returns for this year.</p>
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
                            <h5>₹ 1.86L</h5>
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
                            <h5>₹ 42,800</h5>
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
                            <h5>₹ 6,240</h5>
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
                            <h5>248</h5>
                            <h6>Stock with Agent</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h4>₹ 12,450</h4>
                            <h5>Today's Sales</h5>
                        </div>
                        <div class="dash-imgs"><i data-feather="shopping-cart"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das1">
                        <div class="dash-counts">
                            <h4>9</h4>
                            <h5>Today's Bills</h5>
                        </div>
                        <div class="dash-imgs"><i data-feather="file-text"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das2">
                        <div class="dash-counts">
                            <h4>3</h4>
                            <h5>Pending Indents</h5>
                        </div>
                        <div class="dash-imgs"><i data-feather="clipboard"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das3">
                        <div class="dash-counts">
                            <h4>7</h4>
                            <h5>Low Stock Items</h5>
                        </div>
                        <div class="dash-imgs"><i data-feather="alert-triangle"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Sales Trend</h5>
                            <div class="graph-sets">
                                <ul>
                                    <li><span>Sales</span></li>
                                    <li><span>Returns</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="sales_charts"></div>
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
                                        <tr>
                                            <td>Rice 25kg</td>
                                            <td>4</td>
                                            <td><span class="badge bg-danger">Critical</span></td>
                                        </tr>
                                        <tr>
                                            <td>Sunflower Oil 1L</td>
                                            <td>9</td>
                                            <td><span class="badge bg-warning">Low</span></td>
                                        </tr>
                                        <tr>
                                            <td>Wheat Flour 10kg</td>
                                            <td>3</td>
                                            <td><span class="badge bg-danger">Critical</span></td>
                                        </tr>
                                        <tr>
                                            <td>Toor Dal 1kg</td>
                                            <td>11</td>
                                            <td><span class="badge bg-warning">Low</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <a href="{{ route('agent.requisition') }}" class="btn btn-light w-100 mt-3">Raise Requisition</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
