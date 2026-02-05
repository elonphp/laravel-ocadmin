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
     * - Portal 角色：{portal}.{role}（如 ess.hr_manager, ess.employee）
     *
     * @see docs/md/0104_權限機制.md §2 角色設計
     * @see docs/md/0105_Portal概述.md
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
                'name' => 'ess.hr_manager',
                'sort_order' => 10,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'HR Manager'],
                    'zh_Hant' => ['display_name' => 'HR 主管'],
                ],
            ],
            [
                'name' => 'ess.hr_operator',
                'sort_order' => 20,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'HR Operator'],
                    'zh_Hant' => ['display_name' => 'HR 管理員'],
                ],
            ],
            [
                'name' => 'ess.dept_manager',
                'sort_order' => 100,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Dept. Manager'],
                    'zh_Hant' => ['display_name' => '部門主管'],
                ],
            ],
            [
                'name' => 'ess.employee',
                'sort_order' => 110,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Employee'],
                    'zh_Hant' => ['display_name' => '一般員工'],
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
