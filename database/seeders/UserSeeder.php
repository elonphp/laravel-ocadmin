<?php

namespace Database\Seeders;

use App\Models\Identity\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 固定的前三筆資料
        $fixedUsers = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'name' => 'Admin',
                'display_name' => '管理員',
            ],
            [
                'username' => 'test',
                'email' => 'test@example.com',
                'name' => 'Test',
                'display_name' => '測試員',
            ],
            [
                'username' => 'elonphp',
                'email' => 'elonphp@gmail.com',
                'name' => 'Elon',
                'display_name' => 'Elon PHP',
            ],
            [
                'username' => 'demo',
                'email' => 'demo@example.com',
                'name' => 'Demo',
                'display_name' => '展示員',
            ],
        ];

        foreach ($fixedUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => 'password',
                'is_active' => true,
            ]));
        }

        // 產生 47 筆隨機資料（密碼為 null，禁止登入）
        $faker = Faker::create('zh_TW');

        for ($i = 0; $i < 46; $i++) {
            User::create([
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->safeEmail(),
                'mobile' => $faker->optional(0.7)->phoneNumber(),
                'password' => null,
                'name' => $faker->name(),
                'display_name' => $faker->optional(0.5)->name(),
                'is_active' => $faker->boolean(90), // 90% 機率啟用
            ]);
        }

        $this->command->info('已建立 50 筆使用者資料');
    }
}
