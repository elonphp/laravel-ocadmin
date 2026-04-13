<?php

namespace App\Portals\Ocadmin\Core\ViewComposers;

use App\Models\Menu;
use Illuminate\View\View;

class MenuComposer
{
    public function compose(View $view): void
    {
        $view->with('menus', $this->buildMenus());
    }

    protected function buildMenus(): array
    {
        $user = auth()->user();
        $driver = config('vars.menu_driver', 'database');

        $menus = ($driver === 'database')
            ? $this->buildMenusFromDb()
            : $this->buildMenusFromCode();

        return collect($menus)
            ->map(fn ($menu) => $this->filterByPermission($menu, $user))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * DB Driven：從 sys_menus 表讀取選單
     */
    protected function buildMenusFromDb(): array
    {
        return Menu::with('children.children.children')
            ->where('portal', 'admin')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($menu) => $menu->toMenuItem())
            ->toArray();
    }

    /**
     * Code Driven：hardcoded 選單（備用）
     */
    protected function buildMenusFromCode(): array
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
                    'name'       => $t('text_catalog_product'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.products.index'),
                    'permission' => 'admin.catalog.product.access',
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.options.index'),
                    'permission' => 'admin.catalog.option.access',
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option_value_group'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.option-value-groups.index'),
                    'permission' => 'admin.catalog.option_value_group.access',
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option_value_link'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.option-value-links.index'),
                    'permission' => 'admin.catalog.option_value_link.access',
                    'children'   => [],
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
                    'name'       => $t('text_party_organization'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.organizations.index'),
                    'permission' => 'admin.party.organization.access',
                    'children'   => [],
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
                    'name'       => $t('text_member_member'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.member.members.index'),
                    'permission' => 'admin.member.member.access',
                    'children'   => [],
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
                    'name'       => $t('text_hrm_company'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.hrm.companies.index'),
                    'permission' => 'admin.hrm.company.access',
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_hrm_department'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.hrm.departments.index'),
                    'permission' => 'admin.hrm.department.access',
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_hrm_employee'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.hrm.employees.index'),
                    'permission' => 'admin.hrm.employee.access',
                    'children'   => [],
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
                            'name'       => $t('text_system_permission'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.permissions.index'),
                            'permission' => 'admin.system.permission.access',
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_role'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.roles.index'),
                            'permission' => 'admin.system.role.access',
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_user'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.users.index'),
                            'permission' => 'admin.system.user.access',
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_access_token'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.access-tokens.index'),
                            'permission' => 'admin.system.access_token.access',
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_user_device'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.user-devices.index'),
                            'permission' => 'admin.system.user_device.access',
                            'children'   => []
                        ],
                    ]
                ],
                [
                    'name'       => $t('text_system_setting'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.settings.index'),
                    'permission' => 'admin.system.setting.access',
                    'children'   => []
                ],
                [
                    'name'       => $t('text_system_menu'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.menus.index'),
                    'permission' => 'admin.system.menu.access',
                    'children'   => []
                ],
                [
                    'name'     => $t('text_system_vocabulary'),
                    'icon'     => '',
                    'href'     => '',
                    'children' => [
                        [
                            'name'       => $t('text_system_taxonomy'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.config.taxonomies.index'),
                            'permission' => 'admin.config.taxonomy.access',
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_term'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.config.terms.index'),
                            'permission' => 'admin.config.term.access',
                            'children'   => []
                        ],
                    ]
                ],
                [
                    'name'       => $t('text_system_schema'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.schemas.index'),
                    'permission' => 'admin.system.schema.access',
                    'children'   => []
                ],
                [
                    'name'       => $t('text_system_log'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.logs.index'),
                    'permission' => 'admin.system.log.access',
                    'children'   => []
                ],
            ]
        ];

        return $menus;
    }

    /**
     * 遞迴過濾：移除無權限的項目，子項全空的父層也移除
     */
    protected function filterByPermission(array $item, $user): ?array
    {
        // 檢查項目本身的權限
        if (!empty($item['permission']) && !$user->can($item['permission'])) {
            return null;
        }

        // 遞迴過濾子項目
        if (!empty($item['children'])) {
            $item['children'] = collect($item['children'])
                ->map(fn ($child) => $this->filterByPermission($child, $user))
                ->filter()
                ->values()
                ->toArray();

            // 分組節點（href 為空）且子項全被過濾 → 隱藏父層
            if (empty($item['children']) && empty($item['href'])) {
                return null;
            }
        }

        return $item;
    }
}
