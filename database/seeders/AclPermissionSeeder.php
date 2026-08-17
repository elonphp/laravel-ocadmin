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
     * action 統一使用 menu / access / modify / delete
     *   - menu    側欄選單可見性（UI 入口；與資料權限解耦，可單獨給 / 收）
     *   - access  列表 + 單筆檢視
     *   - modify  新增 + 修改
     *   - delete  刪除
     *
     * 支援 Wildcard Permission（config/permission.php → enable_wildcard_permission => true）
     * 例如角色擁有 admin.catalog.product.* 即符合 admin.catalog.product.menu / .access / .modify / .delete
     *
     * Prefix 注入：array key 只寫後三段 {module}.{resource}.{action}，
     * 透過 permName() 在 runtime 拼接 config('portals.ocadmin.permission_prefix')。
     *
     * 例外：
     *   - common.image_manager 不設 .menu（非選單資源，由 TinyMCE 等編輯器內嵌呼叫）。
     *   - system.transition 另開 .run action（「執行遷移」不是改一筆 record，套不進 modify；
     *     見 10007 §6.5.3「真正的新語意才開新 action」）。
     *
     * @see docs/common/10007_權限機制.md §3 權限設計
     * @see docs/common/10011_選單機制.md §1.1 選單可見性與資料權限解耦
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── catalog ─────────────────────────

            'catalog.product.menu'              => ['en' => 'Product Menu',              'zh_Hant' => '商品 選單'],
            'catalog.product.access'            => ['en' => 'Product Access',            'zh_Hant' => '商品 檢視'],
            'catalog.product.modify'            => ['en' => 'Product Modify',            'zh_Hant' => '商品 修改'],
            'catalog.product.delete'            => ['en' => 'Product Delete',            'zh_Hant' => '商品 刪除'],

            'catalog.option.menu'               => ['en' => 'Option Menu',               'zh_Hant' => '選項 選單'],
            'catalog.option.access'             => ['en' => 'Option Access',             'zh_Hant' => '選項 檢視'],
            'catalog.option.modify'             => ['en' => 'Option Modify',             'zh_Hant' => '選項 修改'],
            'catalog.option.delete'             => ['en' => 'Option Delete',             'zh_Hant' => '選項 刪除'],

            'catalog.option_value_group.menu'   => ['en' => 'Option Value Group Menu',   'zh_Hant' => '選項值群組 選單'],
            'catalog.option_value_group.access' => ['en' => 'Option Value Group Access', 'zh_Hant' => '選項值群組 檢視'],
            'catalog.option_value_group.modify' => ['en' => 'Option Value Group Modify', 'zh_Hant' => '選項值群組 修改'],
            'catalog.option_value_group.delete' => ['en' => 'Option Value Group Delete', 'zh_Hant' => '選項值群組 刪除'],

            'catalog.option_value_link.menu'    => ['en' => 'Option Value Link Menu',    'zh_Hant' => '選項值連結 選單'],
            'catalog.option_value_link.access'  => ['en' => 'Option Value Link Access',  'zh_Hant' => '選項值連結 檢視'],
            'catalog.option_value_link.modify'  => ['en' => 'Option Value Link Modify',  'zh_Hant' => '選項值連結 修改'],
            'catalog.option_value_link.delete'  => ['en' => 'Option Value Link Delete',  'zh_Hant' => '選項值連結 刪除'],

            // ── member ─────────────────────────

            'member.member.menu'                => ['en' => 'Member Menu',               'zh_Hant' => '會員 選單'],
            'member.member.access'              => ['en' => 'Member Access',             'zh_Hant' => '會員 檢視'],
            'member.member.modify'              => ['en' => 'Member Modify',             'zh_Hant' => '會員 修改'],
            'member.member.delete'              => ['en' => 'Member Delete',             'zh_Hant' => '會員 刪除'],

            // ── order ─────────────────────────

            'order.order.menu'                  => ['en' => 'Order Menu',                'zh_Hant' => '訂單 選單'],
            'order.order.access'                => ['en' => 'Order Access',              'zh_Hant' => '訂單 檢視'],
            'order.order.modify'                => ['en' => 'Order Modify',              'zh_Hant' => '訂單 修改'],
            'order.order.delete'                => ['en' => 'Order Delete',              'zh_Hant' => '訂單 刪除'],

            // ── org ─────────────────────────

            'org.organization.menu'             => ['en' => 'Organization Menu',         'zh_Hant' => '組織主檔 選單'],
            'org.organization.access'           => ['en' => 'Organization Access',       'zh_Hant' => '組織主檔 檢視'],
            'org.organization.modify'           => ['en' => 'Organization Modify',       'zh_Hant' => '組織主檔 修改'],
            'org.organization.delete'           => ['en' => 'Organization Delete',       'zh_Hant' => '組織主檔 刪除'],

            'org.company.menu'                  => ['en' => 'Company Menu',              'zh_Hant' => '公司 選單'],
            'org.company.access'                => ['en' => 'Company Access',            'zh_Hant' => '公司 檢視'],
            'org.company.modify'                => ['en' => 'Company Modify',            'zh_Hant' => '公司 修改'],
            'org.company.delete'                => ['en' => 'Company Delete',            'zh_Hant' => '公司 刪除'],

            'org.department.menu'               => ['en' => 'Department Menu',           'zh_Hant' => '部門 選單'],
            'org.department.access'             => ['en' => 'Department Access',         'zh_Hant' => '部門 檢視'],
            'org.department.modify'             => ['en' => 'Department Modify',         'zh_Hant' => '部門 修改'],
            'org.department.delete'             => ['en' => 'Department Delete',         'zh_Hant' => '部門 刪除'],

            'org.employee.menu'                 => ['en' => 'Employee Menu',             'zh_Hant' => '員工 選單'],
            'org.employee.access'               => ['en' => 'Employee Access',           'zh_Hant' => '員工 檢視'],
            'org.employee.modify'               => ['en' => 'Employee Modify',           'zh_Hant' => '員工 修改'],
            'org.employee.delete'               => ['en' => 'Employee Delete',           'zh_Hant' => '員工 刪除'],

            // ── system_acl ─────────────────────────

            'system_acl.permission.menu'        => ['en' => 'Permission Menu',           'zh_Hant' => '權限管理 選單'],
            'system_acl.permission.access'      => ['en' => 'Permission Access',         'zh_Hant' => '權限管理 檢視'],
            'system_acl.permission.modify'      => ['en' => 'Permission Modify',         'zh_Hant' => '權限管理 修改'],
            'system_acl.permission.delete'      => ['en' => 'Permission Delete',         'zh_Hant' => '權限管理 刪除'],

            'system_acl.role.menu'              => ['en' => 'Role Menu',                 'zh_Hant' => '角色管理 選單'],
            'system_acl.role.access'            => ['en' => 'Role Access',               'zh_Hant' => '角色管理 檢視'],
            'system_acl.role.modify'            => ['en' => 'Role Modify',               'zh_Hant' => '角色管理 修改'],
            'system_acl.role.delete'            => ['en' => 'Role Delete',               'zh_Hant' => '角色管理 刪除'],

            'system_acl.user.menu'              => ['en' => 'User Menu',                 'zh_Hant' => '使用者管理 選單'],
            'system_acl.user.access'            => ['en' => 'User Access',               'zh_Hant' => '使用者管理 檢視'],
            'system_acl.user.modify'            => ['en' => 'User Modify',               'zh_Hant' => '使用者管理 修改'],
            'system_acl.user.delete'            => ['en' => 'User Delete',               'zh_Hant' => '使用者管理 刪除'],

            'system_acl.access_token.menu'      => ['en' => 'Access Token Menu',         'zh_Hant' => '存取令牌 選單'],
            'system_acl.access_token.access'    => ['en' => 'Access Token Access',       'zh_Hant' => '存取令牌 檢視'],
            'system_acl.access_token.modify'    => ['en' => 'Access Token Modify',       'zh_Hant' => '存取令牌 修改'],
            'system_acl.access_token.delete'    => ['en' => 'Access Token Delete',       'zh_Hant' => '存取令牌 刪除'],

            'system_acl.user_device.menu'       => ['en' => 'User Device Menu',          'zh_Hant' => '使用者裝置 選單'],
            'system_acl.user_device.access'     => ['en' => 'User Device Access',        'zh_Hant' => '使用者裝置 檢視'],
            'system_acl.user_device.delete'     => ['en' => 'User Device Delete',        'zh_Hant' => '使用者裝置 刪除'],

            // ── common（跨 module 工具）─────────
            //
            // Mode A（前後台分離）：鎖 permission 給編輯類角色（如商品 / 內容編輯）。
            // Mode B（ocadmin 兼任前台 / 單應用）：建議衍生專案直接不註冊 image-manager
            // 路由，避免任何登入使用者都能存取整個系統圖庫（風險：誤刪、隱私、亂上傳）。
            // 詳見 docs/common/10007 §六。
            //
            // 不設 .menu：image_manager 是編輯器內嵌呼叫，非側欄入口。

            'common.image_manager.access'       => ['en' => 'Image Manager Access',      'zh_Hant' => '圖片管理 檢視'],
            'common.image_manager.modify'       => ['en' => 'Image Manager Modify',      'zh_Hant' => '圖片管理 修改'],
            'common.image_manager.delete'       => ['en' => 'Image Manager Delete',      'zh_Hant' => '圖片管理 刪除'],

            // ── system ─────────────────────────

            'system.setting.menu'               => ['en' => 'Setting Menu',              'zh_Hant' => '參數設定 選單'],
            'system.setting.access'             => ['en' => 'Setting Access',            'zh_Hant' => '參數設定 檢視'],
            'system.setting.modify'             => ['en' => 'Setting Modify',            'zh_Hant' => '參數設定 修改'],
            'system.setting.delete'             => ['en' => 'Setting Delete',            'zh_Hant' => '參數設定 刪除'],

            'system.schema.menu'                => ['en' => 'Schema Menu',               'zh_Hant' => '資料表結構 選單'],
            'system.schema.access'              => ['en' => 'Schema Access',             'zh_Hant' => '資料表結構 檢視'],

            // 遷移功能：刻意不指派給任何角色（見 AclRoleSeeder）→ 只有 super_admin /
            // developer 靠 Gate::before 全通，一般管理員看不到也跑不了。
            'system.transition.menu'            => ['en' => 'Transition Menu',           'zh_Hant' => '遷移功能 選單'],
            'system.transition.access'          => ['en' => 'Transition Access',         'zh_Hant' => '遷移功能 檢視'],
            'system.transition.run'             => ['en' => 'Transition Run',            'zh_Hant' => '遷移功能 執行'],

            'system.menu.menu'                  => ['en' => 'Menu Menu',                 'zh_Hant' => '選單設定 選單'],
            'system.menu.access'                => ['en' => 'Menu Access',               'zh_Hant' => '選單設定 檢視'],
            'system.menu.modify'                => ['en' => 'Menu Modify',               'zh_Hant' => '選單設定 修改'],
            'system.menu.delete'                => ['en' => 'Menu Delete',               'zh_Hant' => '選單設定 刪除'],

            'system.log.menu'                   => ['en' => 'Log Menu',                  'zh_Hant' => '日誌管理 選單'],
            'system.log.access'                 => ['en' => 'Log Access',                'zh_Hant' => '日誌管理 檢視'],

            // ── 詞彙管理 system.{taxonomy,term} ────────

            'system.taxonomy.menu'              => ['en' => 'Taxonomy Menu',             'zh_Hant' => '分類管理 選單'],
            'system.taxonomy.access'            => ['en' => 'Taxonomy Access',           'zh_Hant' => '分類管理 檢視'],
            'system.taxonomy.modify'            => ['en' => 'Taxonomy Modify',           'zh_Hant' => '分類管理 修改'],
            'system.taxonomy.delete'            => ['en' => 'Taxonomy Delete',           'zh_Hant' => '分類管理 刪除'],

            'system.term.menu'                  => ['en' => 'Term Menu',                 'zh_Hant' => '詞彙項目 選單'],
            'system.term.access'                => ['en' => 'Term Access',               'zh_Hant' => '詞彙項目 檢視'],
            'system.term.modify'                => ['en' => 'Term Modify',               'zh_Hant' => '詞彙項目 修改'],
            'system.term.delete'                => ['en' => 'Term Delete',               'zh_Hant' => '詞彙項目 刪除'],
        ];

        foreach ($permissions as $suffix => $translations) {
            $perm = Permission::updateOrCreate(
                ['name' => permName($suffix), 'guard_name' => 'web'],
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
