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
     * 權限命名規則：三段式 {module}.{resource}.{action}
     * - catalog.*  — 商品型錄
     * - order.*    — 訂單管理
     * - member.*   — 會員管理
     *
     * Action 類型：
     * - access  — 檢視（列表 + 單筆檢視）
     * - modify  — 修改（新增 + 修改）
     * - delete  — 刪除
     *
     * 支援 Wildcard Permission（config/permission.php → enable_wildcard_permission => true）
     * 例如角色擁有 catalog.product.* 即符合 catalog.product.access / .modify / .delete
     *
     * @see docs/md/0104_權限機制.md §3 權限設計
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── 商品型錄 ────────────────────────────────

            'catalog.product.access'      => ['en' => 'Access Product',       'zh_Hant' => '商品檢視'],
            'catalog.product.modify'      => ['en' => 'Modify Product',       'zh_Hant' => '商品修改'],
            'catalog.product.delete'      => ['en' => 'Delete Product',       'zh_Hant' => '商品刪除'],

            'catalog.option.access'       => ['en' => 'Access Option',        'zh_Hant' => '選項檢視'],
            'catalog.option.modify'       => ['en' => 'Modify Option',        'zh_Hant' => '選項修改'],
            'catalog.option.delete'       => ['en' => 'Delete Option',        'zh_Hant' => '選項刪除'],

            // ── 會員管理 ────────────────────────────────

            'member.member.access'        => ['en' => 'Access Member',        'zh_Hant' => '會員檢視'],
            'member.member.modify'        => ['en' => 'Modify Member',        'zh_Hant' => '會員修改'],
            'member.member.delete'        => ['en' => 'Delete Member',        'zh_Hant' => '會員刪除'],

            // ── 訂單管理 ────────────────────────────────

            'order.order.access'          => ['en' => 'Access Order',         'zh_Hant' => '訂單檢視'],
            'order.order.modify'          => ['en' => 'Modify Order',         'zh_Hant' => '訂單修改'],
            'order.order.delete'          => ['en' => 'Delete Order',         'zh_Hant' => '訂單刪除'],
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
