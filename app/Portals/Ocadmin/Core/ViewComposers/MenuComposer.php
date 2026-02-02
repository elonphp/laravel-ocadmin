<?php

namespace App\Portals\Ocadmin\Core\ViewComposers;

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
            'href'     => route('lang.ocadmin.dashboard'),
            'children' => []
        ];

        // 系統管理（預留）
        $menus[] = [
            'id'       => 'menu-system',
            'icon'     => 'fa-solid fa-cog',
            'name'     => '系統管理',
            'href'     => '',
            'children' => [
                [
                    'name'     => '參數設定',
                    'icon'     => '',
                    'href'     => '#',
                    'children' => []
                ],
            ]
        ];

        return $menus;
    }
}
