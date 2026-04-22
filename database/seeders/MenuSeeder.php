<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuTranslation;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * 選單 Seeder — 將後台側欄選單寫入 menus / menu_translations 表
     *
     * 對應 MenuComposer 中的 code-driven 選單結構。
     * 兩套機制（code-driven / db-driven）並存，可隨時切換。
     *
     * @see docs/common/10011_選單機制.md
     */
    public function run(): void
    {
        $portal = 'ocadmin';
        $sort = 0;

        // ── Dashboard ──
        $this->createMenu($portal, null, [
            'icon' => 'fa-solid fa-home',
            'route_name' => 'lang.ocadmin.dashboard',
            'sort_order' => $sort += 10,
        ], [
            'en' => 'Dashboard',
            'zh_Hant' => '儀表板',
        ]);

        // ── 商品型錄 ──
        $catalog = $this->createMenu($portal, null, [
            'icon' => 'fa-solid fa-tag',
            'sort_order' => $sort += 10,
        ], [
            'en' => 'Catalog',
            'zh_Hant' => '商品型錄',
        ]);

        $this->createMenu($portal, $catalog->id, [
            'permission_name' => 'admin.catalog.product.access',
            'route_name' => 'lang.ocadmin.catalog.products.index',
            'sort_order' => 1,
        ], [
            'en' => 'Products',
            'zh_Hant' => '商品',
        ]);

        $this->createMenu($portal, $catalog->id, [
            'permission_name' => 'admin.catalog.option.access',
            'route_name' => 'lang.ocadmin.catalog.options.index',
            'sort_order' => 2,
        ], [
            'en' => 'Options',
            'zh_Hant' => '選項',
        ]);

        $this->createMenu($portal, $catalog->id, [
            'permission_name' => 'admin.catalog.option_value_group.access',
            'route_name' => 'lang.ocadmin.catalog.option-value-groups.index',
            'sort_order' => 3,
        ], [
            'en' => 'Option Value Groups',
            'zh_Hant' => '選項連動群組',
        ]);

        $this->createMenu($portal, $catalog->id, [
            'permission_name' => 'admin.catalog.option_value_link.access',
            'route_name' => 'lang.ocadmin.catalog.option-value-links.index',
            'sort_order' => 4,
        ], [
            'en' => 'Option Value Links',
            'zh_Hant' => '選項連動',
        ]);

        // ── 會員管理 ──
        $member = $this->createMenu($portal, null, [
            'icon' => 'fa-solid fa-user-group',
            'sort_order' => $sort += 10,
        ], [
            'en' => 'Members',
            'zh_Hant' => '會員管理',
        ]);

        $this->createMenu($portal, $member->id, [
            'permission_name' => 'admin.member.member.access',
            'route_name' => 'lang.ocadmin.member.members.index',
            'sort_order' => 1,
        ], [
            'en' => 'Members',
            'zh_Hant' => '會員',
        ]);

        // ── 組織管理 ──
        $org = $this->createMenu($portal, null, [
            'icon' => 'fa-solid fa-users',
            'sort_order' => $sort += 10,
        ], [
            'en' => 'Organization',
            'zh_Hant' => '組織管理',
        ]);

        $this->createMenu($portal, $org->id, [
            'permission_name' => 'admin.org.organization.access',
            'route_name' => 'lang.ocadmin.org.organizations.index',
            'sort_order' => 1,
        ], [
            'en' => 'Organizations',
            'zh_Hant' => '組織主檔',
        ]);

        $this->createMenu($portal, $org->id, [
            'permission_name' => 'admin.org.company.access',
            'route_name' => 'lang.ocadmin.org.companies.index',
            'sort_order' => 2,
        ], [
            'en' => 'Companies',
            'zh_Hant' => '公司',
        ]);

        $this->createMenu($portal, $org->id, [
            'permission_name' => 'admin.org.department.access',
            'route_name' => 'lang.ocadmin.org.departments.index',
            'sort_order' => 3,
        ], [
            'en' => 'Departments',
            'zh_Hant' => '部門',
        ]);

        $this->createMenu($portal, $org->id, [
            'permission_name' => 'admin.org.employee.access',
            'route_name' => 'lang.ocadmin.org.employees.index',
            'sort_order' => 4,
        ], [
            'en' => 'Employees',
            'zh_Hant' => '員工',
        ]);

        // ── 系統管理 ──
        $system = $this->createMenu($portal, null, [
            'icon' => 'fa-solid fa-cog',
            'sort_order' => $sort += 10,
        ], [
            'en' => 'System',
            'zh_Hant' => '系統管理',
        ]);

        // 系統管理 > 訪問控制（分組）
        $acl = $this->createMenu($portal, $system->id, [
            'sort_order' => 1,
        ], [
            'en' => 'Access Control',
            'zh_Hant' => '訪問控制',
        ]);

        $this->createMenu($portal, $acl->id, [
            'permission_name' => 'admin.system.permission.access',
            'route_name' => 'lang.ocadmin.system.permissions.index',
            'sort_order' => 1,
        ], [
            'en' => 'Permissions',
            'zh_Hant' => '權限管理',
        ]);

        $this->createMenu($portal, $acl->id, [
            'permission_name' => 'admin.system.role.access',
            'route_name' => 'lang.ocadmin.system.roles.index',
            'sort_order' => 2,
        ], [
            'en' => 'Roles',
            'zh_Hant' => '角色管理',
        ]);

        $this->createMenu($portal, $acl->id, [
            'permission_name' => 'admin.system.user.access',
            'route_name' => 'lang.ocadmin.system.users.index',
            'sort_order' => 3,
        ], [
            'en' => 'Users',
            'zh_Hant' => '使用者管理',
        ]);

        $this->createMenu($portal, $acl->id, [
            'permission_name' => 'admin.system.access_token.access',
            'route_name' => 'lang.ocadmin.system.access-tokens.index',
            'sort_order' => 4,
        ], [
            'en' => 'Access Token',
            'zh_Hant' => 'Access Token',
        ]);

        $this->createMenu($portal, $acl->id, [
            'permission_name' => 'admin.system.user_device.access',
            'route_name' => 'lang.ocadmin.system.user-devices.index',
            'sort_order' => 5,
        ], [
            'en' => 'User Devices',
            'zh_Hant' => '裝置管理',
        ]);

        // 系統管理 > 參數設定
        $this->createMenu($portal, $system->id, [
            'permission_name' => 'admin.system.setting.access',
            'route_name' => 'lang.ocadmin.system.settings.index',
            'sort_order' => 2,
        ], [
            'en' => 'Settings',
            'zh_Hant' => '參數設定',
        ]);

        // 系統管理 > 詞彙管理（分組）
        $vocab = $this->createMenu($portal, $system->id, [
            'sort_order' => 3,
        ], [
            'en' => 'Vocabulary',
            'zh_Hant' => '詞彙管理',
        ]);

        $this->createMenu($portal, $vocab->id, [
            'permission_name' => 'admin.config.taxonomy.access',
            'route_name' => 'lang.ocadmin.config.taxonomies.index',
            'sort_order' => 1,
        ], [
            'en' => 'Taxonomies',
            'zh_Hant' => '分類管理',
        ]);

        $this->createMenu($portal, $vocab->id, [
            'permission_name' => 'admin.config.term.access',
            'route_name' => 'lang.ocadmin.config.terms.index',
            'sort_order' => 2,
        ], [
            'en' => 'Terms',
            'zh_Hant' => '詞彙項目',
        ]);

        // 系統管理 > 選單設定
        $this->createMenu($portal, $system->id, [
            'permission_name' => 'admin.system.menu.access',
            'route_name' => 'lang.ocadmin.system.menus.index',
            'sort_order' => 6,
        ], [
            'en' => 'Menu Settings',
            'zh_Hant' => '選單設定',
        ]);

        // 系統管理 > 資料表結構
        $this->createMenu($portal, $system->id, [
            'permission_name' => 'admin.system.schema.access',
            'route_name' => 'lang.ocadmin.system.schemas.index',
            'sort_order' => 4,
        ], [
            'en' => 'Schema',
            'zh_Hant' => '資料表結構',
        ]);

        // 系統管理 > 日誌管理
        $this->createMenu($portal, $system->id, [
            'permission_name' => 'admin.system.log.access',
            'route_name' => 'lang.ocadmin.system.logs.index',
            'sort_order' => 5,
        ], [
            'en' => 'Logs',
            'zh_Hant' => '日誌管理',
        ]);
    }

    /**
     * 建立選單項目 + 翻譯
     */
    private function createMenu(string $portal, ?int $parentId, array $attributes, array $translations): Menu
    {
        $menu = Menu::create(array_merge([
            'portal' => $portal,
            'parent_id' => $parentId,
        ], $attributes));

        foreach ($translations as $locale => $displayName) {
            MenuTranslation::create([
                'menu_id' => $menu->id,
                'locale' => $locale,
                'display_name' => $displayName,
            ]);
        }

        return $menu;
    }
}
