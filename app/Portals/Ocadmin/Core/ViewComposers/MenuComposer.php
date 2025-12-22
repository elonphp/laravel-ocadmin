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

        // 會員管理
        $menus[] = $this->buildMemberMenu();

        // 系統管理
        $menus[] = $this->buildSystemMenu();

        // Example Menu (參考舊系統)
        $menus[] = $this->buildExampleMenu();

        return $menus;
    }

    /**
     * 會員管理選單
     */
    protected function buildMemberMenu(): array
    {
        $children = [];

        // 會員
        $children[] = [
            'name'     => '會員',
            'icon'     => '',
            'href'     => route('lang.ocadmin.member.user.index'),
            'children' => []
        ];

        return [
            'id'       => 'menu-member',
            'icon'     => 'fa-solid fa-users',
            'name'     => '會員管理',
            'href'     => '',
            'children' => $children
        ];
    }

    /**
     * 系統管理選單
     */
    protected function buildSystemMenu(): array
    {
        $children = [];

        // 本地化設定 (Localization)
        $children[] = [
            'name'     => '本地化設定',
            'icon'     => '',
            'href'     => '',
            'children' => [
                [
                    'name'     => '國家管理',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.localization.country.index'),
                    'children' => []
                ],
                [
                    'name'     => '行政區域',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.localization.division.index'),
                    'children' => []
                ],
            ]
        ];

        // 訪問控制 (Access Control)
        $children[] = [
            'name'     => '訪問控制',
            'icon'     => '',
            'href'     => '',
            'children' => [
                [
                    'name'     => '使用者管理',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.access.user.index'),
                    'children' => []
                ],
                [
                    'name'     => '角色管理',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.access.role.index'),
                    'children' => []
                ],
                [
                    'name'     => '權限管理',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.access.permission.index'),
                    'children' => []
                ],
            ]
        ];

        // 參數設定
        $children[] = [
            'name'     => '參數設定',
            'icon'     => '',
            'href'     => route('lang.ocadmin.system.setting.index'),
            'children' => []
        ];

        // 詞彙管理（統一入口：Taxonomy 列表 → 點進去看 Terms）
        $children[] = [
            'name'     => '詞彙管理',
            'icon'     => '',
            'href'     => route('lang.ocadmin.system.taxonomy.taxonomy.index'),
            'children' => []
        ];

        // 資料庫
        $children[] = [
            'name'     => '資料庫',
            'icon'     => '',
            'href'     => '',
            'children' => [
                [
                    'name'     => '欄位定義',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.database.meta_key.index'),
                    'children' => []
                ],
            ]
        ];

        // 系統日誌
        $children[] = [
            'name'     => '系統日誌',
            'icon'     => '',
            'href'     => '',
            'children' => [
                [
                    'name'     => '資料庫',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.log.database'),
                    'children' => []
                ],
                [
                    'name'     => '歷史壓縮檔',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.log.archived'),
                    'children' => []
                ],
                [
                    'name'     => '排程的程式',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.log.scheduler'),
                    'children' => []
                ],
            ]
        ];

        return [
            'id'       => 'menu-system',
            'icon'     => 'fa-solid fa-cog',
            'name'     => '系統管理',
            'href'     => '',
            'children' => $children
        ];
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
