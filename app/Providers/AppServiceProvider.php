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
        View::composer('Dashboard.Layouts.sidebar', function ($view) {
            if (!session()->has('user_id')) {
                return;
            }

            $org_schema = session('org_schema');
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
            DB::purge('coops');

            $menuData = DB::connection('coops')->select("CALL USP_GET_SIDEBAR_MENUE(?)", [session('user_group_id')]);
            $menuIcons = [
                1 => 'isax isax-setting-2',
                2 => 'isax isax-box-add',
                3 => 'isax isax-shop',
                4 => 'isax isax-layer',
                5 => 'isax isax-wallet-3',
                6 => 'isax isax-scan',
                7 => 'isax isax-profile-2user',
                8 => 'isax isax-chart-2',
                9 => 'isax isax-document-text',
            ];
            $menu = [];
            foreach ($menuData as $item) {
                $isParent = $item->Child_Id === null || $item->Child_Id === '';
                if ($isParent) {
                    $menu[$item->Parraint_Id] = (object)[
                        'name' => $item->Parraint_Name,
                        'icon' => data_get($item, 'Parraint_Icon') ?: ($menuIcons[$item->Parraint_Id] ?? 'isax isax-box'),
                        'children' => [],
                    ];
                }
            }

            foreach ($menuData as $item) {
                $isParent = $item->Child_Id === null || $item->Child_Id === '';
                if (!$isParent && isset($menu[$item->Parraint_Id])) {
                    $menu[$item->Parraint_Id]->children[] = (object)[
                        'id' => $item->Child_Id,
                        'name' => $item->Child_Name,
                        'route' => $item->Child_Route ?? null
                    ];
                }
            }

            $view->with('menu', $menu);
        });

        //agent sidebar
    View::composer('AgentDashboard.Layouts.sidebar', function ($view) {
    if (!session()->has('agent_id')) return;

    Config::set('database.connections.coops.database', session('org_schema'));
    DB::purge('coops');

    $menuData = DB::connection('coops')->select("CALL USP_GET_AGENT_MENUE()");

    $menu = [];
    foreach ($menuData as $item) {
        if (!isset($menu[$item->Menu_Id])) {
            $menu[$item->Menu_Id] = (object)[
                'name'     => $item->Menu_Name,
                'icon'     => $item->Icon,
                'route'    => $item->Route,
                'children' => []
            ];
        }
        if ($item->SubMenu_Id) {
            $menu[$item->Menu_Id]->children[] = (object)[
                'name'  => $item->SubMenu_Name,
                'route' => $item->Route
            ];
        }
    }

    $view->with('agentMenu', $menu);
});

    }
}
