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
     * - 功能角色：依業務職能命名（如 order_operator, finance_officer）
     *
     * @see docs/md/0104_權限機制.md §2 角色設計
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 權限群組 ──

        $catalog = [
            'catalog.product.list', 'catalog.product.read', 'catalog.product.create', 'catalog.product.update', 'catalog.product.delete',
            'catalog.option.list', 'catalog.option.read', 'catalog.option.create', 'catalog.option.update', 'catalog.option.delete',
        ];

        $order = [
            'order.order.list', 'order.order.read', 'order.order.create', 'order.order.update', 'order.order.delete',
        ];

        $financeReadOnly = [
            'catalog.product.list', 'catalog.product.read',
            'catalog.option.list', 'catalog.option.read',
            'order.order.list', 'order.order.read',
            'finance.payment.list', 'finance.payment.read',
            'finance.refund.list', 'finance.refund.read', 'finance.refund.approve',
        ];

        $roles = [
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
            [
                'name' => 'order_operator',
                'sort_order' => 10,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Operator'],
                    'zh_Hant' => ['display_name' => '訂單管理員'],
                ],
                'permissions' => array_merge($catalog, $order),
            ],
            [
                'name' => 'order_supervisor',
                'sort_order' => 20,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Supervisor'],
                    'zh_Hant' => ['display_name' => '訂單主管'],
                ],
                'permissions' => array_merge($catalog, $order),
            ],
            [
                'name' => 'finance_officer',
                'sort_order' => 30,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Finance Officer'],
                    'zh_Hant' => ['display_name' => '財務人員'],
                ],
                'permissions' => $financeReadOnly,
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
