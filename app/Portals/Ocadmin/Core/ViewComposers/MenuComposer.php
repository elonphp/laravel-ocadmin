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
            'icon'     => 'bi bi-speedometer2',
            'name'     => __('menu.dashboard'),
            'href'     => route('lang.ocadmin.dashboard'),
            'children' => []
        ];

        // 商品型錄
        $menus[] = [
            'id'       => 'menu-catalog',
            'icon'     => 'bi bi-tag',
            'name'     => __('menu.catalog'),
            'href'     => '',
            'children' => [
                [
                    'name'     => __('menu.catalog_product'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.product.index'),
                    'children' => [],
                ],
                [
                    'name'     => __('menu.catalog_option'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option.index'),
                    'children' => [],
                ],
                [
                    'name'     => __('menu.catalog_ovg'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option-value-group.index'),
                    'children' => [],
                ],
                [
                    'name'     => __('menu.catalog_ovl'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option-value-link.index'),
                    'children' => [],
                ],
            ],
        ];

        // 往來對象
        $menus[] = [
            'id'       => 'menu-party',
            'icon'     => 'bi bi-building',
            'name'     => __('menu.party'),
            'href'     => '',
            'children' => [
                [
                    'name'     => __('menu.party_organization'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.organization.index'),
                    'children' => [],
                ],
            ],
        ];

        // 會員管理
        $menus[] = [
            'id'       => 'menu-member',
            'icon'     => 'bi bi-people',
            'name'     => __('menu.member'),
            'href'     => '',
            'children' => [
                [
                    'name'     => __('menu.member_member'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.member.member.index'),
                    'children' => [],
                ],
            ],
        ];

        // 人資管理
        $menus[] = [
            'id'       => 'menu-hrm',
            'icon'     => 'bi bi-person-badge',
            'name'     => __('menu.hrm'),
            'href'     => '',
            'children' => [
                [
                    'name'     => __('menu.hrm_company'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.company.index'),
                    'children' => [],
                ],
                [
                    'name'     => __('menu.hrm_department'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.department.index'),
                    'children' => [],
                ],
                [
                    'name'     => __('menu.hrm_employee'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.employee.index'),
                    'children' => [],
                ],
            ],
        ];

        // 系統管理
        $menus[] = [
            'id'       => 'menu-system',
            'icon'     => 'bi bi-gear',
            'name'     => __('menu.system'),
            'href'     => '',
            'children' => [
                [
                    'name'     => __('menu.system_acl'),
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => __('menu.system_permission'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.permission.index'),
                            'children' => []
                        ],
                        [
                            'name'     => __('menu.system_role'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.role.index'),
                            'children' => []
                        ],
                        [
                            'name'     => __('menu.system_user'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.user.index'),
                            'children' => []
                        ],
                    ]
                ],
                [
                    'name'     => __('menu.system_setting'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.setting.index'),
                    'children' => []
                ],
                [
                    'name'     => __('menu.system_vocabulary'),
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => __('menu.system_taxonomy'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.taxonomy.index'),
                            'children' => []
                        ],
                        [
                            'name'     => __('menu.system_term'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.term.index'),
                            'children' => []
                        ],
                    ]
                ],
                [
                    'name'     => __('menu.system_schema'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.schema.index'),
                    'children' => []
                ],
                [
                    'name'     => __('menu.system_log'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.log.index'),
                    'children' => []
                ],
            ]
        ];

        return $menus;
    }
}
