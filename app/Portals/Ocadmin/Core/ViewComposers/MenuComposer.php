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

        // 商品型錄
        $menus[] = [
            'id'       => 'menu-catalog',
            'icon'     => 'fa-solid fa-tag',
            'name'     => '商品型錄',
            'href'     => '',
            'children' => [
                [
                    'name'     => '商品',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.product.index'),
                    'children' => [],
                ],
                [
                    'name'     => '選項',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option.index'),
                    'children' => [],
                ],
                [
                    'name'     => '連動選單',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option.cascade'),
                    'children' => [],
                ],
            ],
        ];

        // 公司企業
        $menus[] = [
            'id'       => 'menu-corp',
            'icon'     => 'fa-solid fa-city',
            'name'     => '公司企業',
            'href'     => '',
            'children' => [
                [
                    'name'     => '外部公司',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.organization.index'),
                    'children' => [],
                ],
                [
                    'name'     => '內部公司',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.corp.company.index'),
                    'children' => [],
                ],
            ],
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
                            'name'     => '權限',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.permission.index'),
                            'children' => []
                        ],
                        [
                            'name'     => '角色',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.role.index'),
                            'children' => []
                        ],
                        [
                            'name'     => '使用者',
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.user.index'),
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
                    'name'     => '資料表結構',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.schema.index'),
                    'children' => []
                ],
                [
                    'name'     => '日誌管理',
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.log.index'),
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
