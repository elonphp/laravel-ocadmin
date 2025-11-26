<?php

namespace Portals\Ocadmin\ViewComposers;

use Illuminate\View\View;

class MenuComposer
{
    public function compose(View $view): void
    {
        $view->with('menus', $this->buildMenus());
    }

    protected function buildMenus(): array
    {
        $menus = [];

        // Dashboard
        $menus[] = [
            'id'       => 'menu-dashboard',
            'icon'     => 'fa-solid fa-home',
            'name'     => 'Dashboard',
            'href'     => route('ocadmin.dashboard'),
            'children' => []
        ];

        // Example Menu (參考舊系統)
        $menus[] = $this->buildExampleMenu();

        return $menus;
    }

    /**
     * 範例選單 - 參考舊系統 ColumnLeftComposer.php
     */
    protected function buildExampleMenu(): array
    {
        $example = [];

        // L2 example 0
        $example[] = [
            'name'     => 'L2 example 0',
            'icon'     => '',
            'href'     => '#',
            'children' => []
        ];

        // L2 example 1
        $example[] = [
            'name'     => 'L2 example 1',
            'icon'     => '',
            'href'     => '#',
            'children' => []
        ];

        // L2 example 2 with children (L3)
        $level_2 = [];

        // L3 example 0
        $level_2[] = [
            'name' => 'L3 example 0',
            'href' => '#',
            'icon' => '',
        ];

        // L3 example 1
        $level_2[] = [
            'name' => 'L3 example 1',
            'href' => '#',
            'icon' => '',
        ];

        // L3 example 2 with children (L4)
        $level_3a = [];
        $level_3a[] = [
            'name' => 'L4 example 0',
            'href' => '#',
            'icon' => '',
        ];
        $level_3a[] = [
            'name' => 'L4 example 1',
            'href' => '#',
            'icon' => '',
        ];

        $level_2[] = [
            'name'     => 'L3 example 2',
            'icon'     => '',
            'children' => $level_3a
        ];

        // L3 example 3 with children (L4)
        $level_3b = [];
        $level_3b[] = [
            'name' => 'L4 example 0',
            'href' => '#',
            'icon' => '',
        ];
        $level_3b[] = [
            'name' => 'L4 example 1',
            'href' => '#',
            'icon' => '',
        ];

        $level_2[] = [
            'name'     => 'L3 example 3',
            'icon'     => '',
            'children' => $level_3b
        ];

        $example[] = [
            'name'     => 'L2 example 2',
            'icon'     => '',
            'children' => $level_2
        ];

        return [
            'id'       => 'menu-example',
            'icon'     => 'fa-solid fa-cog',
            'name'     => 'L1 Example',
            'href'     => '',
            'children' => $example
        ];
    }
}
