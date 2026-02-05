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
     * - ess.*  — 員工自助服務（資料範圍：僅自己）
     * - mss.*  — 管理功能（資料範圍：由 Policy 依角色決定）
     *
     * 支援 Wildcard Permission（config/permission.php → enable_wildcard_permission => true）
     * 例如角色擁有 mss.employee.* 即符合 mss.employee.list / .read / .create 等所有動作
     *
     * @see docs/md/0104_權限機制.md §3 權限設計
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── ESS：員工自助服務 ──────────────────────────

            'ess.profile.read'        => ['en' => 'View Profile',         'zh_Hant' => '個人資料檢視'],
            'ess.profile.update'      => ['en' => 'Update Profile',       'zh_Hant' => '個人資料修改'],
            'ess.attendance.list'     => ['en' => 'My Attendance',        'zh_Hant' => '個人出勤紀錄'],
            'ess.attendance.create'   => ['en' => 'Clock In/Out',         'zh_Hant' => '打卡'],
            'ess.leave.list'          => ['en' => 'My Leaves',            'zh_Hant' => '個人請假紀錄'],
            'ess.leave.create'        => ['en' => 'Apply Leave',          'zh_Hant' => '請假申請'],
            'ess.payslip.list'        => ['en' => 'My Payslips',          'zh_Hant' => '個人薪資單'],
            'ess.payslip.read'        => ['en' => 'View Payslip',         'zh_Hant' => '薪資單檢視'],

            // ── MSS：管理功能 ─────────────────────────────

            // 員工管理
            'mss.employee.list'       => ['en' => 'Employee List',        'zh_Hant' => '員工列表'],
            'mss.employee.read'       => ['en' => 'View Employee',        'zh_Hant' => '員工檢視'],
            'mss.employee.create'     => ['en' => 'Create Employee',      'zh_Hant' => '員工新增'],
            'mss.employee.update'     => ['en' => 'Update Employee',      'zh_Hant' => '員工修改'],
            'mss.employee.delete'     => ['en' => 'Delete Employee',      'zh_Hant' => '員工刪除'],

            // 部門管理
            'mss.department.list'     => ['en' => 'Department List',      'zh_Hant' => '部門列表'],
            'mss.department.create'   => ['en' => 'Create Department',    'zh_Hant' => '部門新增'],
            'mss.department.update'   => ['en' => 'Update Department',    'zh_Hant' => '部門修改'],
            'mss.department.delete'   => ['en' => 'Delete Department',    'zh_Hant' => '部門刪除'],

            // 出勤管理
            'mss.attendance.list'     => ['en' => 'Attendance List',      'zh_Hant' => '出勤列表'],
            'mss.attendance.read'     => ['en' => 'View Attendance',      'zh_Hant' => '出勤檢視'],
            'mss.attendance.update'   => ['en' => 'Update Attendance',    'zh_Hant' => '出勤修改'],

            // 請假管理
            'mss.leave.list'          => ['en' => 'Leave List',           'zh_Hant' => '請假列表'],
            'mss.leave.read'          => ['en' => 'View Leave',           'zh_Hant' => '請假檢視'],
            'mss.leave.approve'       => ['en' => 'Approve Leave',        'zh_Hant' => '請假審核'],
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
