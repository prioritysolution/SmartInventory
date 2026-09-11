<?php

use App\Support\DateFormat;

if (!function_exists('dmy')) {
    function dmy($value): string
    {
        return DateFormat::toDisplay($value);
    }
}

if (!function_exists('flattenMenuLinks')) {
    function flattenMenuLinks(array $menu): array
    {
        $links = [];

        foreach ($menu as $parent) {
            if (count($parent->children) > 0) {
                foreach ($parent->children as $child) {
                    if (!empty($child->route)) {
                        $links[] = (object) [
                            'name' => $child->name,
                            'route' => $child->route,
                            'icon' => $parent->icon ?? 'fa fa-link',
                            'group' => $parent->name ?? '',
                        ];
                    }
                }
            } elseif (!empty($parent->route)) {
                $links[] = (object) [
                    'name' => $parent->name,
                    'route' => $parent->route,
                    'icon' => $parent->icon ?? 'fa fa-link',
                    'group' => $parent->name ?? '',
                ];
            }
        }

        return $links;
    }
}

if (!function_exists('menuLinkRoute')) {
    function menuLinkRoute(array $links, string $route, array $params = []): ?string
    {
        foreach ($links as $link) {
            if (($link->route ?? '') === $route) {
                return route($route, $params);
            }
        }

        return null;
    }
}

if (!function_exists('sidebarMenuIcon')) {
    function sidebarMenuIcon(?string $icon, ?string $route = null, ?string $name = null): string
    {
        $icon = trim((string) $icon);
        $route = trim((string) $route);
        $name = strtolower(trim((string) $name));

        if ($route === 'user-dashboard' || $name === 'dashboard') {
            return 'fa-solid fa-house';
        }

        $mapped = [
            'isax isax-home-2' => 'fa-solid fa-house',
            'isax isax-setting-2' => 'fa-solid fa-gear',
            'isax isax-box-add' => 'fa-solid fa-box',
            'isax isax-shop' => 'fa-solid fa-cart-shopping',
            'isax isax-layer' => 'fa-solid fa-layer-group',
            'isax isax-wallet-3' => 'fa-solid fa-wallet',
            'isax isax-scan' => 'fa-solid fa-barcode',
            'isax isax-profile-2user' => 'fa-solid fa-user-gear',
            'isax isax-chart-2' => 'fa-solid fa-chart-column',
            'isax isax-document-text' => 'fa-solid fa-file-lines',
        ];

        if (isset($mapped[$icon])) {
            return $mapped[$icon];
        }

        if ($icon !== '' && !str_starts_with($icon, 'isax')) {
            return $icon;
        }

        return 'fa-solid fa-circle';
    }
}

if (!function_exists('buildAdminSidebarMenu')) {
    function buildAdminSidebarMenu(array $menuData): array
    {
        $menu = [];

        foreach ($menuData as $item) {
            $isParent = $item->Child_Id === null || $item->Child_Id === '';
            if ($isParent && $item->Parraint_Name) {
                $route = data_get($item, 'Parraint_Route') ?: null;
                $menu[(string) $item->Parraint_Id] = (object) [
                    'name' => $item->Parraint_Name,
                    'icon' => sidebarMenuIcon(data_get($item, 'Parraint_Icon'), $route, $item->Parraint_Name),
                    'route' => $route,
                    'children' => [],
                ];
            }
        }

        foreach ($menuData as $item) {
            $isParent = $item->Child_Id === null || $item->Child_Id === '';
            if (!$isParent && isset($menu[(string) $item->Parraint_Id]) && $item->Child_Name) {
                $menu[(string) $item->Parraint_Id]->children[] = (object) [
                    'id' => $item->Child_Id,
                    'name' => $item->Child_Name,
                    'route' => $item->Child_Route ?: null,
                ];
            }
        }

        return [
            'menu' => $menu,
            'menuLinks' => flattenMenuLinks($menu),
        ];
    }
}

if (!function_exists('buildAgentSidebarMenu')) {
    function buildAgentSidebarMenu(array $menuData): array
    {
        $menu = [];

        foreach ($menuData as $item) {
            $menuId = $item->Menu_Id;

            if (!isset($menu[$menuId])) {
                $menu[$menuId] = (object) [
                    'name' => $item->Menu_Name ?? '',
                    'icon' => $item->Icon ?: 'fas fa-circle',
                    'route' => null,
                    'children' => [],
                ];
            }

            if ($item->Menu_Name) {
                $menu[$menuId]->name = $item->Menu_Name;
            }
            if ($item->Icon) {
                $menu[$menuId]->icon = $item->Icon;
            }

            if ($item->SubMenu_Id) {
                if ($item->SubMenu_Name) {
                    $menu[$menuId]->children[] = (object) [
                        'name' => $item->SubMenu_Name,
                        'route' => $item->Route,
                    ];
                }
            } elseif ($item->Route) {
                $menu[$menuId]->route = $item->Route;
            }
        }

        return [
            'agentMenu' => $menu,
            'agentMenuLinks' => flattenMenuLinks($menu),
        ];
    }
}
