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

if (!function_exists('buildAdminSidebarMenu')) {
    function buildAdminSidebarMenu(array $menuData): array
    {
        $menu = [];

        foreach ($menuData as $item) {
            $isParent = $item->Child_Id === null || $item->Child_Id === '';
            if ($isParent && $item->Parraint_Name) {
                $menu[$item->Parraint_Id] = (object) [
                    'name' => $item->Parraint_Name,
                    'icon' => data_get($item, 'Parraint_Icon') ?: 'isax isax-box',
                    'route' => data_get($item, 'Parraint_Route') ?: null,
                    'children' => [],
                ];
            }
        }

        foreach ($menuData as $item) {
            $isParent = $item->Child_Id === null || $item->Child_Id === '';
            if (!$isParent && isset($menu[$item->Parraint_Id]) && $item->Child_Name) {
                $menu[$item->Parraint_Id]->children[] = (object) [
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
