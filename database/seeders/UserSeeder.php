<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * 使用者 Seeder
     *
     * ── 全域帳號（id 1-10 保留）──
     *   ID 1：system   系統底層 fallback（不可登入）
     *   ID 2：service  應用層自動化流程（不可登入，走 API token，賦予 system 角色）
     *   ID 3：admin    最高管理者（super_admin）
     *   ID 6：developer 平台維護者（developer）  不建員工記錄
     *
     * ── ID 7-100：保留 ──
     *
     * ── Admin 後台測試使用者（id 101+）──
     *
     * @see docs/md/0128_全域帳號.md
     */
    public function run(): void
    {
        // ── 全域帳號 ──
        $globals = [
            [
                'id' => 1,
                'username' => 'system',
                'email' => 'system@local',
                'password' => null,  // 不可登入
                'first_name' => 'System',
                'last_name' => '',
                'roles' => ['system'],
            ],
            [
                'id' => 2,
                'username' => 'service',
                'email' => 'service@local',
                'password' => null,  // 不可登入（未來走 API token）
                'first_name' => 'Service',
                'last_name' => '',
                'roles' => ['system'],  // service 帳號賦予 system 角色
            ],
            [
                'id' => 3,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => '123456',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'roles' => ['super_admin'],
            ],
        ];

        // ── 開發者 ──
        $developers = [
            [
                'id' => 6,
                'username' => 'developer',
                'email' => 'developer@example.com',
                'password' => '123456',
                'first_name' => 'Developer',
                'last_name' => 'Demo',
                'roles' => ['developer', 'super_admin'],
            ],
        ];

        // ── Admin 後台測試使用者 ──
        $testers = [
            ['id' => 101, 'username' => 'order.zhao', 'email' => 'order.zhao@example.com', 'password' => '123456', 'first_name' => '國強', 'last_name' => '趙', 'roles' => ['admin.order_operator']],
            ['id' => 102, 'username' => 'order.sun',  'email' => 'order.sun@example.com',  'password' => '123456', 'first_name' => '麗華', 'last_name' => '孫', 'roles' => ['admin.order_operator']],
            ['id' => 103, 'username' => 'sup.zhou',   'email' => 'sup.zhou@example.com',   'password' => '123456', 'first_name' => '明德', 'last_name' => '周', 'roles' => ['admin.order_supervisor']],
        ];

        foreach (array_merge($globals, $developers, $testers) as $data) {
            $roles = $data['roles'];
            unset($data['roles']);

            $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']) ?: $data['username'];

            $user = User::updateOrCreate(['id' => $data['id']], $data);
            $user->syncRoles($roles);
        }
    }
}
