<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除快取
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== 建立權限 =====
        // 命名規則: {portal}.{path} 或 {portal}.{path}.{action}
        // path 多段時用底線連接，例如 system_access_permission
        // 2段 = 選單權限，3段 = 操作權限 (access/modify/create/delete...)
        $permissions = [
            // 首頁
            ['name' => 'ocadmin.dashboard', 'title' => '首頁'],

            // 商品型錄
            ['name' => 'ocadmin.catalog',         'title' => '商品型錄'],
            ['name' => 'ocadmin.catalog_product', 'title' => '商品管理'],
            ['name' => 'ocadmin.catalog_option',  'title' => '選項管理'],

            // 會員管理
            ['name' => 'ocadmin.member',             'title' => '會員管理'],
            ['name' => 'ocadmin.member_user',        'title' => '會員帳號'],
            ['name' => 'ocadmin.member_user.access', 'title' => '讀取會員'],
            ['name' => 'ocadmin.member_user.modify', 'title' => '修改會員'],
            ['name' => 'ocadmin.member_level',        'title' => '會員等級'],
            ['name' => 'ocadmin.member_level.access', 'title' => '讀取等級'],
            ['name' => 'ocadmin.member_level.modify', 'title' => '修改等級'],

            // 銷售管理
            ['name' => 'ocadmin.sales',              'title' => '銷售管理'],
            ['name' => 'ocadmin.sales_order',        'title' => '訂單管理'],
            ['name' => 'ocadmin.sales_order.access', 'title' => '讀取訂單'],
            ['name' => 'ocadmin.sales_order.modify', 'title' => '修改訂單'],
            ['name' => 'ocadmin.sales_return',       'title' => '退貨管理'],

            // 系統（訪問控制裡的 user 指後台使用者，不含前台會員）
            ['name' => 'ocadmin.system',                         'title' => '系統'],
            ['name' => 'ocadmin.system_access',                  'title' => '訪問控制'],
            ['name' => 'ocadmin.system_access_permission',        'title' => '權限管理'],
            ['name' => 'ocadmin.system_access_permission.access', 'title' => '讀取權限'],
            ['name' => 'ocadmin.system_access_permission.modify', 'title' => '修改權限'],
            ['name' => 'ocadmin.system_access_role',              'title' => '角色管理'],
            ['name' => 'ocadmin.system_access_role.access',       'title' => '讀取角色'],
            ['name' => 'ocadmin.system_access_role.modify',       'title' => '修改角色'],
            ['name' => 'ocadmin.system_access_user',              'title' => '後台使用者'],
            ['name' => 'ocadmin.system_setting',                  'title' => '參數設定'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['title' => $perm['title']]
            );
        }

        $this->command->info('已建立 ' . count($permissions) . ' 筆權限');

        // ===== 建立角色 =====
        $roles = [
            // 後台角色（admin. 前綴）
            ['name' => 'ocadmin',                   'title' => '後台人員',   'description' => '可進入後台的基本角色，無此角色無法進入後台'],
            ['name' => 'ocadmin.sys_admin',         'title' => '系統管理員', 'description' => '擁有所有後台權限，繞過權限檢查'],
            ['name' => 'ocadmin.order_operator',    'title' => '訂單管理員', 'description' => '訂單相關功能'],
            ['name' => 'ocadmin.product_operator',  'title' => '商品管理員', 'description' => '商品相關功能'],

            // 前台角色
            ['name' => 'member', 'title' => '會員', 'description' => '一般會員'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => 'web'],
                ['title' => $role['title'], 'description' => $role['description']]
            );
        }

        $this->command->info('已建立 ' . count($roles) . ' 筆角色');

        // ===== 分配角色權限 =====

        // ocadmin：僅有首頁權限
        $ocadminRole = Role::findByName('ocadmin');
        $ocadminRole->syncPermissions(['ocadmin.dashboard']);

        // ocadmin.sys_admin：所有權限（但實際上會被 Gate::before 繞過）
        // $superAdminRole = Role::findByName('ocadmin.sys_admin');
        // $superAdminRole->syncPermissions(Permission::all());

        // ocadmin.order_operator：首頁 + 銷售模組
        $orderOperatorRole = Role::findByName('ocadmin.order_operator');
        $orderOperatorRole->syncPermissions([
            'ocadmin.dashboard',
            'ocadmin.sales',
            'ocadmin.sales_order',
            'ocadmin.sales_order.access',
            'ocadmin.sales_order.modify',
            'ocadmin.sales_return',
        ]);

        // ocadmin.product_operator：首頁 + 商品模組
        $productManagerRole = Role::findByName('ocadmin.product_operator');
        $productManagerRole->syncPermissions([
            'ocadmin.dashboard',
            'ocadmin.catalog',
            'ocadmin.catalog_product',
            'ocadmin.catalog_option',
        ]);

        // member：無後台權限
        // 不分配任何權限

        $this->command->info('已完成角色權限分配');
    }
}
