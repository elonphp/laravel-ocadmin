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
     * ID 2-50：保留
     * ID 51-60：後台管理者（隨機 admin.* 角色）
     * ID 61-80：一般會員（無角色）
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

        // ── ID 51-60：後台管理者 ──
        $adminUsers = [
            ['id' => 51, 'username' => 'order.wang',   'email' => 'order.wang@example.com',   'first_name' => '志明', 'last_name' => '王',  'roles' => ['admin.order_operator']],
            ['id' => 52, 'username' => 'order.chen',   'email' => 'order.chen@example.com',   'first_name' => '美玲', 'last_name' => '陳',  'roles' => ['admin.order_operator']],
            ['id' => 53, 'username' => 'order.lin',    'email' => 'order.lin@example.com',    'first_name' => '淑芬', 'last_name' => '林',  'roles' => ['admin.order_supervisor']],
            ['id' => 54, 'username' => 'fin.huang',    'email' => 'fin.huang@example.com',    'first_name' => '建宏', 'last_name' => '黃',  'roles' => ['admin.finance_officer']],
            ['id' => 55, 'username' => 'fin.zhang',    'email' => 'fin.zhang@example.com',    'first_name' => '家豪', 'last_name' => '張',  'roles' => ['admin.finance_officer']],
            ['id' => 56, 'username' => 'ops.liu',      'email' => 'ops.liu@example.com',      'first_name' => '怡君', 'last_name' => '劉',  'roles' => ['admin.order_operator', 'admin.finance_officer']],
            ['id' => 57, 'username' => 'ops.wu',       'email' => 'ops.wu@example.com',       'first_name' => '俊傑', 'last_name' => '吳',  'roles' => ['admin.order_supervisor']],
            ['id' => 58, 'username' => 'ops.xu',       'email' => 'ops.xu@example.com',       'first_name' => '雅婷', 'last_name' => '許',  'roles' => ['admin.order_operator']],
            ['id' => 59, 'username' => 'fin.yang',     'email' => 'fin.yang@example.com',     'first_name' => '宗翰', 'last_name' => '楊',  'roles' => ['admin.finance_officer']],
            ['id' => 60, 'username' => 'ops.zheng',    'email' => 'ops.zheng@example.com',    'first_name' => '佳蓉', 'last_name' => '鄭',  'roles' => ['admin.order_operator', 'admin.order_supervisor']],
        ];

        foreach ($adminUsers as $userData) {
            $roles = $userData['roles'];
            unset($userData['roles']);

            $userData['password'] = '123456';
            $userData['name'] = trim($userData['last_name'] . $userData['first_name']);

            $user = User::updateOrCreate(
                ['id' => $userData['id']],
                $userData
            );

            $user->syncRoles($roles);
        }

        // ── ID 61-80：一般會員 ──
        $members = [
            ['id' => 61, 'username' => 'member.li',      'email' => 'li.member@example.com',      'first_name' => '心怡', 'last_name' => '李'],
            ['id' => 62, 'username' => 'member.zhao',     'email' => 'zhao.member@example.com',    'first_name' => '冠廷', 'last_name' => '趙'],
            ['id' => 63, 'username' => 'member.sun',      'email' => 'sun.member@example.com',     'first_name' => '雅琪', 'last_name' => '孫'],
            ['id' => 64, 'username' => 'member.zhou',     'email' => 'zhou.member@example.com',    'first_name' => '柏翰', 'last_name' => '周'],
            ['id' => 65, 'username' => 'member.cai',      'email' => 'cai.member@example.com',     'first_name' => '詩涵', 'last_name' => '蔡'],
            ['id' => 66, 'username' => 'member.he',       'email' => 'he.member@example.com',      'first_name' => '承恩', 'last_name' => '何'],
            ['id' => 67, 'username' => 'member.guo',      'email' => 'guo.member@example.com',     'first_name' => '佩珊', 'last_name' => '郭'],
            ['id' => 68, 'username' => 'member.xie',      'email' => 'xie.member@example.com',     'first_name' => '宇軒', 'last_name' => '謝'],
            ['id' => 69, 'username' => 'member.lv',       'email' => 'lv.member@example.com',      'first_name' => '欣妤', 'last_name' => '呂'],
            ['id' => 70, 'username' => 'member.peng',     'email' => 'peng.member@example.com',    'first_name' => '彥廷', 'last_name' => '彭'],
            ['id' => 71, 'username' => 'member.zeng',     'email' => 'zeng.member@example.com',    'first_name' => '筱涵', 'last_name' => '曾'],
            ['id' => 72, 'username' => 'member.pan',      'email' => 'pan.member@example.com',     'first_name' => '冠宇', 'last_name' => '潘'],
            ['id' => 73, 'username' => 'member.su',       'email' => 'su.member@example.com',      'first_name' => '雅芳', 'last_name' => '蘇'],
            ['id' => 74, 'username' => 'member.ye',       'email' => 'ye.member@example.com',      'first_name' => '子豪', 'last_name' => '葉'],
            ['id' => 75, 'username' => 'member.du',       'email' => 'du.member@example.com',      'first_name' => '佳琪', 'last_name' => '杜'],
            ['id' => 76, 'username' => 'member.jiang',    'email' => 'jiang.member@example.com',   'first_name' => '品睿', 'last_name' => '江'],
            ['id' => 77, 'username' => 'member.fan',      'email' => 'fan.member@example.com',     'first_name' => '宜庭', 'last_name' => '范'],
            ['id' => 78, 'username' => 'member.song',     'email' => 'song.member@example.com',    'first_name' => '振豪', 'last_name' => '宋'],
            ['id' => 79, 'username' => 'member.tang',     'email' => 'tang.member@example.com',    'first_name' => '雨萱', 'last_name' => '唐'],
            ['id' => 80, 'username' => 'member.wei',      'email' => 'wei.member@example.com',     'first_name' => '浩然', 'last_name' => '魏'],
        ];

        foreach ($members as $userData) {
            $userData['password'] = '123456';
            $userData['name'] = trim($userData['last_name'] . $userData['first_name']);

            User::updateOrCreate(
                ['id' => $userData['id']],
                $userData
            );
        }
    }
}
