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
     * - 全域角色：不帶 prefix（如 super_admin）
     * - 後台角色：admin.{role}（如 admin.order_operator）
     *
     * 後台存取由 middleware 判斷角色名稱是否以 admin. 開頭，
     * super_admin 透過 Gate::before 繞過所有權限檢查。
     *
     * @see docs/md/0104_權限機制.md §2 角色設計
     * @see docs/md/0105_Portal概述.md
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 商品型錄權限群組 ──
        $catalog = [
            'catalog.product.list', 'catalog.product.read', 'catalog.product.create', 'catalog.product.update', 'catalog.product.delete',
            'catalog.option.list', 'catalog.option.read', 'catalog.option.create', 'catalog.option.update', 'catalog.option.delete',
        ];

        // ── 訂單權限群組 ──
        $order = [
            'order.order.list', 'order.order.read', 'order.order.create', 'order.order.update', 'order.order.delete',
        ];

        $roles = [
            // ── 全域角色 ──
            [
                'name' => 'super_admin',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Super Admin'],
                    'zh_Hant' => ['display_name' => '超級管理員'],
                ],
                'permissions' => [], // Gate::before 處理，不需指派
            ],

            // ── 後台管理角色（admin.*）──
            [
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
                ['name' => $roleData['name']],
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
