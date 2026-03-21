<?php

namespace Database\Seeders;

use App\Models\Acl\PortalUser;
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
     * ID 101+：Admin 後台測試使用者
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

        // ── ID 101+：Admin 後台測試使用者 ──
        $users = [
            ['id' => 101, 'username' => 'order.zhao',  'email' => 'order.zhao@example.com',  'first_name' => '國強', 'last_name' => '趙', 'roles' => ['admin.order_operator']],
            ['id' => 102, 'username' => 'order.sun',   'email' => 'order.sun@example.com',   'first_name' => '麗華', 'last_name' => '孫', 'roles' => ['admin.order_operator']],
            ['id' => 103, 'username' => 'sup.zhou',    'email' => 'sup.zhou@example.com',    'first_name' => '明德', 'last_name' => '周', 'roles' => ['admin.order_supervisor']],
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

        // 同步 acl_portal_users（依角色前綴自動建立 portal 記錄）
        $portals = array_keys(config('portals'));
        foreach (User::with('roles')->get() as $user) {
            foreach ($portals as $portal) {
                PortalUser::syncFromRoles($user, $portal);
            }
        }
    }
}
