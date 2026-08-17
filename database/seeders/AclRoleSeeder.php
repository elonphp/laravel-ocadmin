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
     * ID 區段規劃：
     * - 1~20：系統保留角色（後台不可見、不可改）
     * - 21~100：系統預設角色（後台可見、可指派）
     * - 101+：使用者自建角色（auto_increment 起始）
     *
     * 角色命名規則：
     * - 全域角色：不帶 prefix（如 system、super_admin）— 用 roleName($name, true) 標例外
     * - Portal 角色：{role_prefix}.{name} — 用 roleName($name)，runtime 拼接 config('portals.ocadmin.role_prefix')
     *
     * Permission 群組同理：用 permName($suffix) 拼接。
     *
     * @see docs/common/10007_權限機制.md
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 商品型錄權限群組 ──
        $catalog = [
            permName('catalog.product.access'), permName('catalog.product.modify'), permName('catalog.product.delete'),
            permName('catalog.option.access'), permName('catalog.option.modify'), permName('catalog.option.delete'),
        ];

        // ── 訂單權限群組 ──
        $order = [
            permName('order.order.access'), permName('order.order.modify'), permName('order.order.delete'),
        ];

        $roles = [
            // ── 系統保留角色（id 1~20，後台不可見）──
            [
                'id' => 1,
                'name' => roleName('system', true),

                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'System'],
                    'zh_Hant' => ['display_name' => '系統'],
                ],
                'permissions' => [],
            ],

            // ── 系統預設角色（id 21~100，後台可見）──
            [
                'id' => 21,
                'name' => roleName('super_admin', true),

                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Super Admin'],
                    'zh_Hant' => ['display_name' => '超級管理員'],
                ],
                'permissions' => [], // Gate::before 放行，不需指派個別權限
            ],

            // ── Portal 角色（id 101+，後台可自建）──
            [
                'id' => 101,
                'name' => roleName('order_operator'),

                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Operator'],
                    'zh_Hant' => ['display_name' => '訂單管理員'],
                ],
                'permissions' => array_merge($catalog, $order),
            ],
            [
                'id' => 102,
                'name' => roleName('order_supervisor'),

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
