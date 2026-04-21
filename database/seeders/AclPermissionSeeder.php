<?php

namespace Database\Seeders;

use App\Models\Acl\Permission;
use App\Models\Acl\PermissionTranslation;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AclPermissionSeeder extends Seeder
{
    /**
     * 權限 Seeder
     *
     * 權限命名規則：四段式 {portal}.{module}.{resource}.{action}
     * action 統一使用 access / modify / delete
     *   - access  列表 + 單筆檢視
     *   - modify  新增 + 修改
     *   - delete  刪除
     *
     * 支援 Wildcard Permission（config/permission.php → enable_wildcard_permission => true）
     * 例如角色擁有 admin.catalog.product.* 即符合 admin.catalog.product.access / .modify / .delete
     *
     * @see docs/md/0104_權限機制.md §3 權限設計
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── 後台 admin.catalog.* ─────────────────────────

            'admin.catalog.product.access'            => ['en' => 'Product Access',            'zh_Hant' => '商品 檢視'],
            'admin.catalog.product.modify'            => ['en' => 'Product Modify',            'zh_Hant' => '商品 修改'],
            'admin.catalog.product.delete'            => ['en' => 'Product Delete',            'zh_Hant' => '商品 刪除'],

            'admin.catalog.option.access'             => ['en' => 'Option Access',             'zh_Hant' => '選項 檢視'],
            'admin.catalog.option.modify'             => ['en' => 'Option Modify',             'zh_Hant' => '選項 修改'],
            'admin.catalog.option.delete'             => ['en' => 'Option Delete',             'zh_Hant' => '選項 刪除'],

            'admin.catalog.option_value_group.access' => ['en' => 'Option Value Group Access', 'zh_Hant' => '選項值群組 檢視'],
            'admin.catalog.option_value_group.modify' => ['en' => 'Option Value Group Modify', 'zh_Hant' => '選項值群組 修改'],
            'admin.catalog.option_value_group.delete' => ['en' => 'Option Value Group Delete', 'zh_Hant' => '選項值群組 刪除'],

            'admin.catalog.option_value_link.access'  => ['en' => 'Option Value Link Access',  'zh_Hant' => '選項值連結 檢視'],
            'admin.catalog.option_value_link.modify'  => ['en' => 'Option Value Link Modify',  'zh_Hant' => '選項值連結 修改'],
            'admin.catalog.option_value_link.delete'  => ['en' => 'Option Value Link Delete',  'zh_Hant' => '選項值連結 刪除'],

            // ── 後台 admin.member.* ─────────────────────────

            'admin.member.member.access'              => ['en' => 'Member Access',             'zh_Hant' => '會員 檢視'],
            'admin.member.member.modify'              => ['en' => 'Member Modify',             'zh_Hant' => '會員 修改'],
            'admin.member.member.delete'              => ['en' => 'Member Delete',             'zh_Hant' => '會員 刪除'],

            // ── 後台 admin.order.* ─────────────────────────

            'admin.order.order.access'                => ['en' => 'Order Access',              'zh_Hant' => '訂單 檢視'],
            'admin.order.order.modify'                => ['en' => 'Order Modify',              'zh_Hant' => '訂單 修改'],
            'admin.order.order.delete'                => ['en' => 'Order Delete',              'zh_Hant' => '訂單 刪除'],

            // ── 後台 admin.org.* ─────────────────────────

            'admin.org.organization.access'           => ['en' => 'Organization Access',       'zh_Hant' => '組織主檔 檢視'],
            'admin.org.organization.modify'           => ['en' => 'Organization Modify',       'zh_Hant' => '組織主檔 修改'],
            'admin.org.organization.delete'           => ['en' => 'Organization Delete',       'zh_Hant' => '組織主檔 刪除'],

            'admin.org.company.access'                => ['en' => 'Company Access',            'zh_Hant' => '公司 檢視'],
            'admin.org.company.modify'                => ['en' => 'Company Modify',            'zh_Hant' => '公司 修改'],
            'admin.org.company.delete'                => ['en' => 'Company Delete',            'zh_Hant' => '公司 刪除'],

            'admin.org.department.access'             => ['en' => 'Department Access',         'zh_Hant' => '部門 檢視'],
            'admin.org.department.modify'             => ['en' => 'Department Modify',         'zh_Hant' => '部門 修改'],
            'admin.org.department.delete'             => ['en' => 'Department Delete',         'zh_Hant' => '部門 刪除'],

            'admin.org.employee.access'               => ['en' => 'Employee Access',           'zh_Hant' => '員工 檢視'],
            'admin.org.employee.modify'               => ['en' => 'Employee Modify',           'zh_Hant' => '員工 修改'],
            'admin.org.employee.delete'               => ['en' => 'Employee Delete',           'zh_Hant' => '員工 刪除'],

            // ── 後台 admin.system.* ─────────────────────────

            'admin.system.permission.access'          => ['en' => 'Permission Access',         'zh_Hant' => '權限管理 檢視'],
            'admin.system.permission.modify'          => ['en' => 'Permission Modify',         'zh_Hant' => '權限管理 修改'],
            'admin.system.permission.delete'          => ['en' => 'Permission Delete',         'zh_Hant' => '權限管理 刪除'],

            'admin.system.role.access'                => ['en' => 'Role Access',               'zh_Hant' => '角色管理 檢視'],
            'admin.system.role.modify'                => ['en' => 'Role Modify',               'zh_Hant' => '角色管理 修改'],
            'admin.system.role.delete'                => ['en' => 'Role Delete',               'zh_Hant' => '角色管理 刪除'],

            'admin.system.user.access'                => ['en' => 'User Access',               'zh_Hant' => '使用者管理 檢視'],
            'admin.system.user.modify'                => ['en' => 'User Modify',               'zh_Hant' => '使用者管理 修改'],
            'admin.system.user.delete'                => ['en' => 'User Delete',               'zh_Hant' => '使用者管理 刪除'],

            'admin.system.access_token.access'        => ['en' => 'Access Token Access',       'zh_Hant' => '存取令牌 檢視'],
            'admin.system.access_token.modify'        => ['en' => 'Access Token Modify',       'zh_Hant' => '存取令牌 修改'],
            'admin.system.access_token.delete'        => ['en' => 'Access Token Delete',       'zh_Hant' => '存取令牌 刪除'],

            'admin.system.user_device.access'         => ['en' => 'User Device Access',        'zh_Hant' => '使用者裝置 檢視'],

            'admin.system.setting.access'             => ['en' => 'Setting Access',            'zh_Hant' => '參數設定 檢視'],
            'admin.system.setting.modify'             => ['en' => 'Setting Modify',            'zh_Hant' => '參數設定 修改'],
            'admin.system.setting.delete'             => ['en' => 'Setting Delete',            'zh_Hant' => '參數設定 刪除'],

            'admin.system.schema.access'              => ['en' => 'Schema Access',             'zh_Hant' => '資料表結構 檢視'],

            'admin.system.menu.access'                => ['en' => 'Menu Access',               'zh_Hant' => '選單設定 檢視'],
            'admin.system.menu.modify'                => ['en' => 'Menu Modify',               'zh_Hant' => '選單設定 修改'],
            'admin.system.menu.delete'                => ['en' => 'Menu Delete',               'zh_Hant' => '選單設定 刪除'],

            'admin.system.log.access'                 => ['en' => 'Log Access',                'zh_Hant' => '日誌管理 檢視'],

            // ── 後台 admin.config.* ─────────────────────────

            'admin.config.taxonomy.access'            => ['en' => 'Taxonomy Access',           'zh_Hant' => '分類管理 檢視'],
            'admin.config.taxonomy.modify'            => ['en' => 'Taxonomy Modify',           'zh_Hant' => '分類管理 修改'],
            'admin.config.taxonomy.delete'            => ['en' => 'Taxonomy Delete',           'zh_Hant' => '分類管理 刪除'],

            'admin.config.term.access'                => ['en' => 'Term Access',               'zh_Hant' => '詞彙項目 檢視'],
            'admin.config.term.modify'                => ['en' => 'Term Modify',               'zh_Hant' => '詞彙項目 修改'],
            'admin.config.term.delete'                => ['en' => 'Term Delete',               'zh_Hant' => '詞彙項目 刪除'],
        ];

        foreach ($permissions as $name => $translations) {
            $perm = Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );

            foreach ($translations as $locale => $displayName) {
                PermissionTranslation::updateOrCreate(
                    ['permission_id' => $perm->id, 'locale' => $locale],
                    ['display_name' => $displayName],
                );
            }
        }
    }
}
