<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * 使用者 Seeder
     *
     * ID 1：系統管理員（super_admin）
     * ID 2-100：保留
     * ID 101+：測試使用者
     */
    public function run(): void
    {
        // ── ID 1：系統管理員 ──
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'John Doe',
                'username' => 'admin',
                'password' => '123456',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]
        );
        $admin->syncRoles(['super_admin']);

        // ── ID 101+：測試使用者（保留 2-100）──
        $users = [
            ['id' => 101, 'username' => 'hr.wang',     'email' => 'hr.wang@example.com',     'first_name' => '志明', 'last_name' => '王', 'roles' => ['ess.hr_manager']],
            ['id' => 102, 'username' => 'hr.chen',     'email' => 'hr.chen@example.com',     'first_name' => '美玲', 'last_name' => '陳', 'roles' => ['ess.hr_manager']],
            ['id' => 103, 'username' => 'op.lin',      'email' => 'op.lin@example.com',      'first_name' => '淑芬', 'last_name' => '林', 'roles' => ['ess.hr_operator']],
            ['id' => 104, 'username' => 'op.huang',    'email' => 'op.huang@example.com',    'first_name' => '建宏', 'last_name' => '黃', 'roles' => ['ess.hr_operator']],
            ['id' => 105, 'username' => 'mgr.zhang',   'email' => 'mgr.zhang@example.com',   'first_name' => '家豪', 'last_name' => '張', 'roles' => ['ess.dept_manager']],
            ['id' => 106, 'username' => 'mgr.liu',     'email' => 'mgr.liu@example.com',     'first_name' => '雅婷', 'last_name' => '劉', 'roles' => ['ess.dept_manager']],
            ['id' => 107, 'username' => 'mgr.wu',      'email' => 'mgr.wu@example.com',      'first_name' => '俊傑', 'last_name' => '吳', 'roles' => ['ess.dept_manager']],
            ['id' => 108, 'username' => 'emp.li',      'email' => 'emp.li@example.com',      'first_name' => '怡君', 'last_name' => '李', 'roles' => ['ess.employee']],
            ['id' => 109, 'username' => 'emp.yang',    'email' => 'emp.yang@example.com',    'first_name' => '宗翰', 'last_name' => '楊', 'roles' => ['ess.employee']],
            ['id' => 110, 'username' => 'emp.xu',      'email' => 'emp.xu@example.com',      'first_name' => '佳蓉', 'last_name' => '許', 'roles' => ['ess.employee']],
            ['id' => 111, 'username' => 'emp.zheng',   'email' => 'emp.zheng@example.com',   'first_name' => '冠宇', 'last_name' => '鄭', 'roles' => ['ess.employee']],
            ['id' => 112, 'username' => 'emp.xie',     'email' => 'emp.xie@example.com',     'first_name' => '筱涵', 'last_name' => '謝', 'roles' => ['ess.employee']],
            ['id' => 113, 'username' => 'emp.he',      'email' => 'emp.he@example.com',      'first_name' => '承翰', 'last_name' => '何', 'roles' => ['ess.employee']],
            ['id' => 114, 'username' => 'emp.guo',     'email' => 'emp.guo@example.com',     'first_name' => '詩涵', 'last_name' => '郭', 'roles' => ['ess.employee']],
            ['id' => 115, 'username' => 'emp.cai',     'email' => 'emp.cai@example.com',     'first_name' => '柏翰', 'last_name' => '蔡', 'roles' => ['ess.employee']],
            ['id' => 116, 'username' => 'emp.su',      'email' => 'emp.su@example.com',      'first_name' => '雅芳', 'last_name' => '蘇', 'roles' => ['ess.employee']],
            ['id' => 117, 'username' => 'emp.zeng',    'email' => 'emp.zeng@example.com',    'first_name' => '彥廷', 'last_name' => '曾', 'roles' => ['ess.employee']],
            ['id' => 118, 'username' => 'emp.pan',     'email' => 'emp.pan@example.com',     'first_name' => '欣怡', 'last_name' => '潘', 'roles' => ['ess.employee']],
            ['id' => 119, 'username' => 'emp.lu',      'email' => 'emp.lu@example.com',      'first_name' => '育誠', 'last_name' => '呂', 'roles' => ['ess.employee']],
            ['id' => 120, 'username' => 'emp.luo',     'email' => 'emp.luo@example.com',     'first_name' => '佩珊', 'last_name' => '羅', 'roles' => ['ess.employee']],
        ];

        foreach ($users as $userData) {
            $roles = $userData['roles'];
            unset($userData['roles']);

            $userData['password'] = '123456';
            $userData['name'] = trim($userData['first_name'] . ' ' . $userData['last_name']);

            $user = User::updateOrCreate(
                ['id' => $userData['id']],
                $userData
            );

            $user->syncRoles($roles);
        }
    }
}
