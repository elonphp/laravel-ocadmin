<?php

namespace Database\Seeders;

use App\Models\Acl\Role;
use App\Models\Acl\RoleTranslation;
use Illuminate\Database\Seeder;

class AclRoleSeeder extends Seeder
{
    /**
     * 角色 Seeder
     *
     * 角色命名規則：
     * - 全域角色：不帶 prefix（如 super_admin）
     * - Portal 角色：{portal}.{role}（如 admin.order_operator）
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'sort_order' => 0,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Super Admin'],
                    'zh_Hant' => ['display_name' => '超級管理員'],
                ],
            ],
            [
                'name' => 'admin.dealer_manager',
                'sort_order' => 15,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Dealer Manager'],
                    'zh_Hant' => ['display_name' => '經銷商管理員'],
                ],
            ],
            [
                'name' => 'admin.order_operator',
                'sort_order' => 20,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Operator'],
                    'zh_Hant' => ['display_name' => '訂單操作員'],
                ],
            ],
            [
                'name' => 'admin.order_viewer',
                'sort_order' => 30,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Order Viewer'],
                    'zh_Hant' => ['display_name' => '訂單檢視員'],
                ],
            ],
            [
                'name' => 'web.company_admin',
                'sort_order' => 100,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Company Admin'],
                    'zh_Hant' => ['display_name' => '公司管理員'],
                ],
            ],
            [
                'name' => 'web.sales',
                'sort_order' => 110,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Sales'],
                    'zh_Hant' => ['display_name' => '業務員'],
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $translations = $roleData['translations'];
            unset($roleData['translations']);

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
        }
    }
}
