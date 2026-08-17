<?php

namespace App\Portals\Ocadmin\Core\ViewComposers;

use App\Models\Menu;
use Illuminate\Support\Facades\Gate;
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
        $driver = config('vars.menu_driver', 'code');

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
            ->where('portal', 'ocadmin')
            ->where('group', 'main')
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
                    'permission' => permName('catalog.product.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.options.index'),
                    'permission' => permName('catalog.option.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option_value_group'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.option-value-groups.index'),
                    'permission' => permName('catalog.option_value_group.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_catalog_option_value_link'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.catalog.option-value-links.index'),
                    'permission' => permName('catalog.option_value_link.menu'),
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
                    'permission' => permName('member.member.menu'),
                    'children'   => [],
                ],
            ],
        ];

        // 組織管理
        $menus[] = [
            'id'       => 'menu-org',
            'icon'     => 'fa-solid fa-users',
            'name'     => $t('text_org'),
            'href'     => '',
            'children' => [
                [
                    'name'       => $t('text_org_organization'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.org.organizations.index'),
                    'permission' => permName('org.organization.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_org_company'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.org.companies.index'),
                    'permission' => permName('org.company.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_org_department'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.org.departments.index'),
                    'permission' => permName('org.department.menu'),
                    'children'   => [],
                ],
                [
                    'name'       => $t('text_org_employee'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.org.employees.index'),
                    'permission' => permName('org.employee.menu'),
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
                            'href'       => route('lang.ocadmin.system.acl.permissions.index'),
                            'permission' => permName('system_acl.permission.menu'),
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_role'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.acl.roles.index'),
                            'permission' => permName('system_acl.role.menu'),
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_user'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.acl.users.index'),
                            'permission' => permName('system_acl.user.menu'),
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_access_token'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.acl.access-tokens.index'),
                            'permission' => permName('system_acl.access_token.menu'),
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_user_device'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.acl.user-devices.index'),
                            'permission' => permName('system_acl.user_device.menu'),
                            'children'   => []
                        ],
                    ]
                ],
                [
                    'name'       => $t('text_system_setting'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.settings.index'),
                    'permission' => permName('system.setting.menu'),
                    'children'   => []
                ],
                [
                    'name'       => $t('text_system_menu'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.menus.index'),
                    'permission' => permName('system.menu.menu'),
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
                            'href'       => route('lang.ocadmin.system.taxonomies.index'),
                            'permission' => permName('system.taxonomy.menu'),
                            'children'   => []
                        ],
                        [
                            'name'       => $t('text_system_term'),
                            'icon'       => '',
                            'href'       => route('lang.ocadmin.system.terms.index'),
                            'permission' => permName('system.term.menu'),
                            'children'   => []
                        ],
                    ]
                ],
                [
                    'name'       => $t('text_system_schema'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.schemas.index'),
                    'permission' => permName('system.schema.menu'),
                    'children'   => []
                ],
                [
                    'name'       => $t('text_system_transition'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.transition.index'),
                    'permission' => permName('system.transition.menu'),
                    'children'   => []
                ],
                [
                    'name'       => $t('text_system_log'),
                    'icon'       => '',
                    'href'       => route('lang.ocadmin.system.logs.index'),
                    'permission' => permName('system.log.menu'),
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
        // 檢查項目本身的權限（走 Gate::check 確保 Gate::before 生效）
        if (!empty($item['permission']) && !Gate::forUser($user)->check($item['permission'])) {
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
