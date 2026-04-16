<?php

namespace Database\Seeders;

use App\Models\Acl\Permission;
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
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 商品型錄權限群組 ──
        $catalog = [
            'admin.catalog.product.access', 'admin.catalog.product.modify', 'admin.catalog.product.delete',
            'admin.catalog.option.access', 'admin.catalog.option.modify', 'admin.catalog.option.delete',
        ];

        // ── 訂單權限群組 ──
        $order = [
            'admin.order.order.access', 'admin.order.order.modify', 'admin.order.order.delete',
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
                'permissions' => [],
            ],
            [
                'id' => 4,
                'name' => 'service',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Service'],
                    'zh_Hant' => ['display_name' => '服務帳號'],
                ],
                'permissions' => [], // 應用層自動化流程掛名用，走 API token 認證
            ],
            [
                'id' => 5,
                'name' => 'demo',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Demo'],
                    'zh_Hant' => ['display_name' => '展示帳號'],
                ],
                'permissions' => [], // 限定可操作範圍，避免誤碰正式資料
            ],
            [
                'id' => 6,
                'name' => 'reader',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Reader'],
                    'zh_Hant' => ['display_name' => '唯讀'],
                ],
                'permissions' => [], // 只讀角色，未來可同步所有 .access 結尾的權限
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

            // super_admin 自動同步所有啟用權限
            if ($role->name === 'super_admin') {
                $allPermissions = Permission::where('is_active', true)->pluck('name');
                $role->syncPermissions($allPermissions);
            }
        }
    }
}
