<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    public function boot(): void
    {
        View::composer([
            'Dashboard.Layouts.sidebar',
            'Dashboard.Layouts.topbar',
            'Dashboard.Layouts.quick-access',
            'Dashboard.dashboard',
        ], function ($view) {
            if (!session()->has('user_id')) {
                $view->with(['menu' => [], 'menuLinks' => []]);
                return;
            }

            $org_schema = session('org_schema');
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
            DB::purge('coops');

            $menuData = DB::connection('coops')->select('CALL USP_GET_SIDEBAR_MENUE(?)', [session('user_group_id')]);
            $view->with(buildAdminSidebarMenu($menuData));
        });

        View::composer('Dashboard.Layouts.topbar', function ($view) {
            $notifications = [];
            if (!session()->has('user_id') || !session()->has('org_schema')) {
                $view->with(['adminNotifications' => [], 'adminNotificationCount' => 0]);
                return;
            }

            try {
                Config::set('database.connections.coops.database', session('org_schema'));
                DB::purge('coops');

                $branchId = (int) (session('branch_id') ?: 0);
                $yearId = (int) (session('year_id') ?: 0);
                $menuLinks = $view->getData()['menuLinks'] ?? [];
                if (empty($menuLinks) && session()->has('user_group_id')) {
                    $menuData = DB::connection('coops')->select('CALL USP_GET_SIDEBAR_MENUE(?)', [session('user_group_id')]);
                    $menuLinks = buildAdminSidebarMenu($menuData)['menuLinks'] ?? [];
                }

                $statsRows = DB::connection('coops')->select('CALL USP_GET_DASHBOARD_STATS(?, ?)', [
                    $branchId,
                    $yearId,
                ]);
                $stats = $statsRows[0] ?? null;

                $pendingBarcodes = (int) ($stats->pending_barcodes ?? 0);
                $lowStock = (int) ($stats->low_stock_count ?? 0);

                if ($pendingBarcodes > 0) {
                    $notifications[] = (object) [
                        'title'   => 'Pending Barcodes',
                        'message' => $pendingBarcodes . ' item(s) waiting for barcode generation',
                        'when'    => 'Today',
                        'url'     => menuLinkRoute($menuLinks, 'barcode-label') ?? route('user-dashboard'),
                        'icon'    => 'fa fa-barcode',
                        'bg'      => '#fff6e5',
                        'color'   => '#d39e00',
                    ];
                }

                if ($lowStock > 0) {
                    $notifications[] = (object) [
                        'title'   => 'Low Stock',
                        'message' => $lowStock . ' product(s) below reorder level',
                        'when'    => 'Today',
                        'url'     => menuLinkRoute($menuLinks, 'reorder-report')
                            ?? menuLinkRoute($menuLinks, 'product-master')
                            ?? route('user-dashboard'),
                        'icon'    => 'fa fa-exclamation-triangle',
                        'bg'      => '#fdecec',
                        'color'   => '#dc3545',
                    ];
                }

                $reorderAlerts = DB::connection('coops')->select('CALL USP_GET_REORDER_ALERTS(?, ?)', [
                    $branchId,
                    $yearId,
                ]);
                foreach (array_slice($reorderAlerts, 0, 3) as $row) {
                    $status = strtoupper((string) ($row->Alert_Status ?? 'Low'));
                    $notifications[] = (object) [
                        'title'   => ($status === 'CRITICAL' ? 'Critical Stock' : 'Reorder Alert'),
                        'message' => trim(($row->Prod_Code ?? '') . ' ' . ($row->Prod_Name ?? '')) .
                            ' — On hand: ' . (int) ($row->On_Hand ?? 0) .
                            ', Reorder: ' . (int) ($row->ReOrder_Qty ?? 0),
                        'when'    => 'Today',
                        'url'     => menuLinkRoute($menuLinks, 'reorder-report')
                            ?? menuLinkRoute($menuLinks, 'product-master')
                            ?? route('user-dashboard'),
                        'icon'    => $status === 'CRITICAL' ? 'fa fa-times-circle' : 'fa fa-box',
                        'bg'      => $status === 'CRITICAL' ? '#fdecec' : '#e7f6fb',
                        'color'   => $status === 'CRITICAL' ? '#dc3545' : '#0aa2c0',
                    ];
                }
            } catch (\Exception $e) {
                $notifications = [];
            }

            $view->with([
                'adminNotifications' => $notifications,
                'adminNotificationCount' => count($notifications),
            ]);
        });

        View::composer([
            'AgentDashboard.Layouts.sidebar',
            'AgentDashboard.dashboard',
            'AgentDashboard.Layouts.topbar',
        ], function ($view) {
            if (!session()->has('agent_id')) {
                $view->with(['agentMenu' => [], 'agentMenuLinks' => []]);
                return;
            }

            Config::set('database.connections.coops.database', session('org_schema'));
            DB::purge('coops');

            $menuData = DB::connection('coops')->select('CALL USP_GET_AGENT_MENUE()');
            $view->with(buildAgentSidebarMenu($menuData));
        });

        View::composer('AgentDashboard.Layouts.topbar', function ($view) {
            $notifications = [];
            if (!session()->has('agent_id') || !session()->has('org_schema')) {
                $view->with(['agentNotifications' => [], 'agentNotificationCount' => 0]);
                return;
            }

            try {
                Config::set('database.connections.coops.database', session('org_schema'));
                DB::purge('coops');
                $today = \Carbon\Carbon::now('Asia/Kolkata')->toDateString();
                $rows = DB::connection('coops')->select(
                    'CALL USP_GET_AGENT_NOTIFICATIONS(?, ?)',
                    [session('agent_id'), $today]
                );

                $agentMenuLinks = buildAgentSidebarMenu(
                    DB::connection('coops')->select('CALL USP_GET_AGENT_MENUE()')
                )['agentMenuLinks'];

                $styles = [
                    'ISSUED' => ['icon' => 'fa fa-box', 'bg' => '#e8f8ef', 'color' => '#198754', 'route' => 'agent.report.issue', 'params' => ['today' => 1]],
                    'PENDING' => ['icon' => 'fa fa-clipboard', 'bg' => '#fff6e5', 'color' => '#d39e00', 'route' => 'agent.requisition'],
                    'RETURN' => ['icon' => 'fa fa-undo', 'bg' => '#e7f6fb', 'color' => '#0aa2c0', 'route' => 'agent.customer'],
                    'LOW_STOCK' => ['icon' => 'fa fa-exclamation-triangle', 'bg' => '#fdecec', 'color' => '#dc3545', 'route' => 'agent.report.stock', 'params' => ['today' => 1]],
                ];

                foreach ($rows as $row) {
                    $type = strtoupper((string) ($row->NType ?? ''));
                    $style = $styles[$type] ?? $styles['LOW_STOCK'];
                    $url = menuLinkRoute(
                        $agentMenuLinks,
                        $style['route'],
                        $style['params'] ?? []
                    ) ?? route('agent.dashboard');
                    $when = 'Today';
                    if (!empty($row->NDate)) {
                        try {
                            $date = \Carbon\Carbon::parse($row->NDate, 'Asia/Kolkata')->startOfDay();
                            $when = $date->isToday() ? 'Today' : $date->format('d M Y');
                        } catch (\Exception $e) {
                            $when = 'Today';
                        }
                    }
                    $notifications[] = (object) [
                        'message' => $row->Message ?? '',
                        'when'    => $when,
                        'url'     => $url,
                        'icon'    => $style['icon'],
                        'bg'      => $style['bg'],
                        'color'   => $style['color'],
                    ];
                }
            } catch (\Exception $e) {
                $notifications = [];
            }

            $view->with([
                'agentNotifications' => $notifications,
                'agentNotificationCount' => count($notifications),
            ]);
        });

    }
}
