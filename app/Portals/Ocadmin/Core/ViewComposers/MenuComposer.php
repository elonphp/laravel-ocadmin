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
        $t = fn (string $key) => __("admin/common/menu.{$key}");

        $menus = [];

        // Dashboard
        $menus[] = [
            'id'       => 'menu-dashboard',
            'icon'     => 'fa-solid fa-home',
            'name'     => $t('text_dashboard'),
            'href'     => route('lang.ocadmin.dashboard'),
            'children' => []
        ];

        // 商品型錄
        $menus[] = [
            'id'       => 'menu-catalog',
            'icon'     => 'fa-solid fa-tag',
            'name'     => $t('text_catalog'),
            'href'     => '',
            'children' => [
                [
                    'name'     => $t('text_catalog_product'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.products.index'),
                    'children' => [],
                ],
                [
                    'name'     => $t('text_catalog_option'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.options.index'),
                    'children' => [],
                ],
                [
                    'name'     => $t('text_catalog_option_value_group'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option-value-groups.index'),
                    'children' => [],
                ],
                [
                    'name'     => $t('text_catalog_option_value_link'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.catalog.option-value-links.index'),
                    'children' => [],
                ],
            ],
        ];

        // 往來對象
        $menus[] = [
            'id'       => 'menu-party',
            'icon'     => 'fa-solid fa-city',
            'name'     => $t('text_party'),
            'href'     => '',
            'children' => [
                [
                    'name'     => $t('text_party_organization'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.organizations.index'),
                    'children' => [],
                ],
            ],
        ];

        // 會員管理
        $menus[] = [
            'id'       => 'menu-member',
            'icon'     => 'fa-solid fa-user-group',
            'name'     => $t('text_member'),
            'href'     => '',
            'children' => [
                [
                    'name'     => $t('text_member_member'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.member.members.index'),
                    'children' => [],
                ],
            ],
        ];

        // 人資管理
        $menus[] = [
            'id'       => 'menu-hrm',
            'icon'     => 'fa-solid fa-users',
            'name'     => $t('text_hrm'),
            'href'     => '',
            'children' => [
                [
                    'name'     => $t('text_hrm_company'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.companies.index'),
                    'children' => [],
                ],
                [
                    'name'     => $t('text_hrm_department'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.departments.index'),
                    'children' => [],
                ],
                [
                    'name'     => $t('text_hrm_employee'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.hrm.employees.index'),
                    'children' => [],
                ],
            ],
        ];

        // 系統管理
        $menus[] = [
            'id'       => 'menu-system',
            'icon'     => 'fa-solid fa-cog',
            'name'     => $t('text_system'),
            'href'     => '',
            'children' => [
                [
                    'name'     => $t('text_system_acl'),
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => $t('text_system_permission'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.permissions.index'),
                            'children' => []
                        ],
                        [
                            'name'     => $t('text_system_role'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.roles.index'),
                            'children' => []
                        ],
                        [
                            'name'     => $t('text_system_user'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.system.users.index'),
                            'children' => []
                        ],
                    ]
                ],
                [
                    'name'     => $t('text_system_setting'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.settings.index'),
                    'children' => []
                ],
                [
                    'name'     => $t('text_system_vocabulary'),
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'     => $t('text_system_taxonomy'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.taxonomies.index'),
                            'children' => []
                        ],
                        [
                            'name'     => $t('text_system_term'),
                            'icon'     => '',
                            'href'     => route('lang.ocadmin.config.terms.index'),
                            'children' => []
                        ],
                    ]
                ],
                [
                    'name'     => $t('text_system_schema'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.schemas.index'),
                    'children' => []
                ],
                [
                    'name'     => $t('text_system_log'),
                    'icon'     => '',
                    'href'     => route('lang.ocadmin.system.logs.index'),
                    'children' => []
                ],
            ]
        ];

        return $menus;
    }
}
