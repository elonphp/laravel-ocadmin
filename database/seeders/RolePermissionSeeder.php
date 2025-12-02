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
        $permissions = [
            // 選單權限 (type=menu)
            ['name' => 'admin.dashboard',           'title' => '首頁',       'type' => 'menu'],
            ['name' => 'admin.catalog',             'title' => '商品',       'type' => 'menu'],
            ['name' => 'admin.catalog.product',     'title' => '商品作業',   'type' => 'menu'],
            ['name' => 'admin.catalog.category',    'title' => '分類作業',   'type' => 'menu'],
            ['name' => 'admin.catalog.attribute',   'title' => '屬性作業',   'type' => 'menu'],
            ['name' => 'admin.sales',               'title' => '銷售',       'type' => 'menu'],
            ['name' => 'admin.sales.order',         'title' => '訂單作業',   'type' => 'menu'],
            ['name' => 'admin.sales.return',        'title' => '退貨作業',   'type' => 'menu'],
            ['name' => 'admin.system',              'title' => '系統',       'type' => 'menu'],
            ['name' => 'admin.system.user',         'title' => '使用者',     'type' => 'menu'],
            ['name' => 'admin.system.role',         'title' => '角色管理',   'type' => 'menu'],
            ['name' => 'admin.system.setting',      'title' => '系統設定',   'type' => 'menu'],

            // 功能權限 (type=action)
            ['name' => 'admin.catalog.product.create', 'title' => '新增商品', 'type' => 'action'],
            ['name' => 'admin.catalog.product.edit',   'title' => '編輯商品', 'type' => 'action'],
            ['name' => 'admin.catalog.product.delete', 'title' => '刪除商品', 'type' => 'action'],
            ['name' => 'admin.sales.order.create',     'title' => '新增訂單', 'type' => 'action'],
            ['name' => 'admin.sales.order.edit',       'title' => '編輯訂單', 'type' => 'action'],
            ['name' => 'admin.sales.order.cancel',     'title' => '取消訂單', 'type' => 'action'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['title' => $perm['title'], 'type' => $perm['type']]
            );
        }

        $this->command->info('已建立 ' . count($permissions) . ' 筆權限');

        // ===== 建立角色 =====
        $roles = [
            // 後台角色（admin. 前綴）
            ['name' => 'admin.staff',           'title' => '後台人員',   'description' => '可進入後台的基本角色，無此角色無法進入後台'],
            ['name' => 'admin.super_admin',     'title' => '超級管理員', 'description' => '擁有所有後台權限，繞過權限檢查'],
            ['name' => 'admin.order_manager',   'title' => '訂單管理員', 'description' => '訂單相關功能'],
            ['name' => 'admin.product_manager', 'title' => '商品管理員', 'description' => '商品相關功能'],
            ['name' => 'admin.report_viewer',   'title' => '報表檢視',   'description' => '僅能檢視報表'],

            // 前台角色
            ['name' => 'member',                'title' => '會員',       'description' => '一般會員'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => 'web'],
                ['title' => $role['title'], 'description' => $role['description']]
            );
        }

        $this->command->info('已建立 ' . count($roles) . ' 筆角色');

        // ===== 分配角色權限 =====

        // admin.staff：僅有首頁權限
        $staffRole = Role::findByName('admin.staff');
        $staffRole->syncPermissions(['admin.dashboard']);

        // admin.super_admin：所有權限（但實際上會被 Gate::before 繞過）
        $superAdminRole = Role::findByName('admin.super_admin');
        $superAdminRole->syncPermissions(Permission::all());

        // admin.order_manager：首頁 + 銷售模組
        $orderManagerRole = Role::findByName('admin.order_manager');
        $orderManagerRole->syncPermissions([
            'admin.dashboard',
            'admin.sales',
            'admin.sales.order',
            'admin.sales.return',
            'admin.sales.order.create',
            'admin.sales.order.edit',
        ]);

        // admin.product_manager：首頁 + 商品模組
        $productManagerRole = Role::findByName('admin.product_manager');
        $productManagerRole->syncPermissions([
            'admin.dashboard',
            'admin.catalog',
            'admin.catalog.product',
            'admin.catalog.category',
            'admin.catalog.product.create',
            'admin.catalog.product.edit',
        ]);

        // admin.report_viewer：首頁（僅能檢視報表，未來擴充）
        $reportViewerRole = Role::findByName('admin.report_viewer');
        $reportViewerRole->syncPermissions(['admin.dashboard']);

        // member：無後台權限
        // 不分配任何權限

        $this->command->info('已完成角色權限分配');
    }
}
