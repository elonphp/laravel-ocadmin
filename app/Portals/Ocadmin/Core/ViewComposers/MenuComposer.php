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

        // 系統管理
        $menus[] = [
            'id'       => 'menu-system',
            'icon'     => 'fa-solid fa-cog',
            'name'     => '系統管理',
            'href'     => '',
            'children' => [
                [
                    'name'     => '訪問控制',
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => '權限管理',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.permission.index'),
                            'children' => []
                        ],
                        [
                            'name'     => '角色管理',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.role.index'),
                            'children' => []
                        ],
                    ]
                ],
                [
                    'name'     => '參數設定',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.setting.index'),
                    'children' => []
                ],
                [
                    'name'     => '詞彙管理',
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => '分類管理',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.taxonomy.index'),
                            'children' => []
                        ],
                        [
                            'name'     => '詞彙項目',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.term.index'),
                            'children' => []
                        ],
                    ]
                ],
            ]
        ];

        return $menus;
    }
}
