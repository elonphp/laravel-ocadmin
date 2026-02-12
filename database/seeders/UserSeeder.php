<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
            ['id' => 101, 'username' => 'order.wang',   'email' => 'order.wang@example.com',   'first_name' => '志明', 'last_name' => '王', 'roles' => ['order_operator']],
            ['id' => 102, 'username' => 'order.chen',   'email' => 'order.chen@example.com',   'first_name' => '美玲', 'last_name' => '陳', 'roles' => ['order_operator']],
            ['id' => 103, 'username' => 'order.lin',    'email' => 'order.lin@example.com',    'first_name' => '淑芬', 'last_name' => '林', 'roles' => ['order_supervisor']],
            ['id' => 104, 'username' => 'fin.huang',    'email' => 'fin.huang@example.com',    'first_name' => '建宏', 'last_name' => '黃', 'roles' => ['finance_officer']],
            ['id' => 105, 'username' => 'fin.zhang',    'email' => 'fin.zhang@example.com',    'first_name' => '家豪', 'last_name' => '張', 'roles' => ['finance_officer']],
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
