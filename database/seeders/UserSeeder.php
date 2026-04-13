<?php

namespace Database\Seeders;

use App\Models\Acl\PortalUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * 使用者 Seeder
     *
     * ID 1：系統管理員（super_admin）
     * ID 2：系統帳號（system）
     * ID 3：開發者（developer）
     * ID 4-100：保留
     * ID 101+：Admin 後台測試使用者
     */
    public function run(): void
    {
        // ── ID 1：系統管理員 ──
        $admin = User::updateOrCreate(
            ['id' => 1],
            [
                'email' => 'admin@example.com',
                'name' => 'John Doe',
                'username' => 'admin',
                'password' => '123456',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]
        );
        $admin->syncRoles(['super_admin']);

        // ── ID 2：系統帳號 ──
        $system = User::updateOrCreate(
            ['id' => 2],
            [
                'email' => 'system@localhost',
                'name' => 'System',
                'username' => 'system',
                'password' => null,
                'first_name' => 'System',
                'last_name' => '',
            ]
        );
        $system->syncRoles(['system']);

        // ── ID 3：開發者 ──
        $developer = User::updateOrCreate(
            ['id' => 3],
            [
                'email' => 'elonphp@gmail.com',
                'name' => 'Elon PHP',
                'username' => 'elonphp',
                'password' => '123456',
                'first_name' => 'Elon',
                'last_name' => 'PHP',
            ]
        );
        $developer->syncRoles(['developer']);

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

        // 同步 acl_portal_users（依 role_prefix 自動建立 portal 記錄）
        $rolePrefixes = array_values(array_unique(array_filter(array_column(
            array_diff_key(config('portals'), ['global' => null]),
            'role_prefix'
        ))));
        foreach (User::with('roles')->get() as $user) {
            PortalUser::syncFromRoles($user, 'global');
            foreach ($rolePrefixes as $rolePrefix) {
                PortalUser::syncFromRoles($user, $rolePrefix);
            }
        }
    }
}
