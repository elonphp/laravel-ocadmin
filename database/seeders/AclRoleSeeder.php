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
     * - Portal 角色：{portal}.{role}（如 ess.hr_manager, ess.employee）
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

        // ── ESS 共用權限（所有非 super_admin 角色皆擁有）──
        $ess = [
            'ess.profile.read', 'ess.profile.update',
            'ess.attendance.list', 'ess.attendance.create',
            'ess.leave.list', 'ess.leave.create',
            'ess.payslip.list', 'ess.payslip.read',
        ];

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

            // ── ESS 角色 ──
            [
                'name' => 'ess.hr_manager',
                'sort_order' => 10,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'HR Manager'],
                    'zh_Hant' => ['display_name' => 'HR 主管'],
                ],
                'permissions' => array_merge($ess, [
                    'mss.employee.list', 'mss.employee.read', 'mss.employee.create', 'mss.employee.update', 'mss.employee.delete',
                    'mss.department.list', 'mss.department.create', 'mss.department.update', 'mss.department.delete',
                    'mss.attendance.list', 'mss.attendance.read', 'mss.attendance.update',
                    'mss.leave.list', 'mss.leave.read', 'mss.leave.approve',
                ]),
            ],
            [
                'name' => 'ess.hr_operator',
                'sort_order' => 20,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'HR Operator'],
                    'zh_Hant' => ['display_name' => 'HR 管理員'],
                ],
                'permissions' => array_merge($ess, [
                    'mss.employee.list', 'mss.employee.read', 'mss.employee.create', 'mss.employee.update',
                    'mss.department.list',
                    'mss.attendance.list', 'mss.attendance.read', 'mss.attendance.update',
                    'mss.leave.list', 'mss.leave.read', 'mss.leave.approve',
                ]),
            ],
            [
                'name' => 'ess.dept_manager',
                'sort_order' => 100,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Dept. Manager'],
                    'zh_Hant' => ['display_name' => '部門主管'],
                ],
                'permissions' => array_merge($ess, [
                    'mss.employee.list', 'mss.employee.read',
                    'mss.department.list',
                    'mss.attendance.list', 'mss.attendance.read',
                    'mss.leave.list', 'mss.leave.read', 'mss.leave.approve',
                ]),
            ],
            [
                'name' => 'ess.employee',
                'sort_order' => 110,
                'is_active' => true,
                'translations' => [
                    'en' => ['display_name' => 'Employee'],
                    'zh_Hant' => ['display_name' => '一般員工'],
                ],
                'permissions' => $ess, // 非 admin.* 角色，不能進後台
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
