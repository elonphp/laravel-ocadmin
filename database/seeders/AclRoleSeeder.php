<?php

namespace Database\Seeders;

use App\Models\Acl\Role;
use App\Models\Acl\RoleTranslation;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AclRoleSeeder extends Seeder
{
    /**
     * 角色 Seeder
     *
     * 角色命名規則：
     * - 全域角色：不帶 prefix（如 developer、super_admin）
     * - 後台角色：admin.{role}（如 admin.order_operator）
     *
     * 後台存取由 middleware 判斷角色名稱是否以 admin. 開頭。
     * developer 透過 Gate::before 無條件放行（開發商最高權限，後台不可見）。
     * super_admin 為客戶方最高管理員，自動指派所有啟用權限。
     *
     * @see docs/md/0104_權限機制.md §2 角色設計
     * @see docs/md/0105_Portal概述.md
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 商品型錄權限群組 ──
        $catalog = [
            'catalog.product.access', 'catalog.product.modify', 'catalog.product.delete',
            'catalog.option.access', 'catalog.option.modify', 'catalog.option.delete',
        ];

        // ── 訂單權限群組 ──
        $order = [
            'order.order.access', 'order.order.modify', 'order.order.delete',
        ];

        $roles = [
            // ── 全域角色（id 1-10 保留）──
            [
                'id' => 1,
                'name' => 'super_admin',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Super Admin'],
                    'zh_Hant' => ['display_name' => '超級管理員'],
                ],
                'permissions' => [], // 客戶方最高管理員，儲存時自動同步所有啟用權限
            ],
            [
                'id' => 2,
                'name' => 'system',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'System'],
                    'zh_Hant' => ['display_name' => '系統'],
                ],
                'permissions' => [], // 系統角色，不需指派
            ],
            [
                'id' => 3,
                'name' => 'developer',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Developer'],
                    'zh_Hant' => ['display_name' => '開發者'],
                ],
                'permissions' => [], // Gate::before 處理，不需指派
            ],

            // ── 後台管理角色（admin.*，id 從 51 開始）──
            [
                'id' => 51,
                'name' => 'admin.order_operator',
                'sort_order' => 200,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Operator'],
                    'zh_Hant' => ['display_name' => '訂單管理員'],
                ],
                'permissions' => array_merge($catalog, $order),
            ],
            [
                'id' => 52,
                'name' => 'admin.order_supervisor',
                'sort_order' => 210,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Supervisor'],
                    'zh_Hant' => ['display_name' => '訂單主管'],
                ],
                'permissions' => array_merge($catalog, $order),
            ],
        ];

        foreach ($roles as $roleData) {
            $translations = $roleData['translations'];
            $permissions = $roleData['permissions'];
            unset($roleData['translations'], $roleData['permissions']);

            $role = Role::updateOrCreate(
                ['id' => $roleData['id']],
                $roleData
            );

            foreach ($translations as $locale => $translationData) {
                RoleTranslation::updateOrCreate(
                    ['role_id' => $role->id, 'locale' => $locale],
                    $translationData
                );
            }

            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }
        }
    }
}
