<?php

namespace Database\Seeders;

use App\Models\Identity\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清空資料表並重設 auto_increment
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 固定的前幾筆資料（含角色分配）
        $fixedUsers = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'name' => '管理員',
                'roles' => ['ocadmin', 'ocadmin.sys_admin'],  // 超級管理員
            ],
            [
                'username' => 'demo',
                'email' => 'demo@example.com',
                'name' => '訪客',
                'roles' => ['ocadmin'],  // 觀察員
            ],
            [
                'username' => 'elonphp',
                'email' => 'elonphp@gmail.com',
                'name' => 'Elon PHP',
                'roles' => ['ocadmin', 'ocadmin.sys_admin'],  // 超級管理員
            ],
        ];

        foreach ($fixedUsers as $userData) {
            $roles = $userData['roles'] ?? [];
            unset($userData['roles']);

            $user = User::create(array_merge($userData, [
                'password' => '123456',
                'is_active' => true,
            ]));

            // 分配角色
            if (!empty($roles)) {
                $user->assignRole($roles);
            }
        }

        // 產生隨機資料（密碼為 null，禁止登入，無後台權限）
        $faker = Faker::create('zh_TW');

        for ($i = 0; $i < 46; $i++) {
            $user = User::create([
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->safeEmail(),
                'mobile' => $faker->optional(0.7)->phoneNumber(),
                'password' => null,
                'name' => $faker->name(),
                // 'display_name' => $faker->optional(0.5)->name(),
                'is_active' => $faker->boolean(90),
            ]);

            // 隨機分配 member 角色（前台會員）
            $user->assignRole('member');
        }

        $this->command->info('已建立 50 筆使用者資料並分配角色');
    }
}
