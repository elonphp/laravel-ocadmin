<?php

namespace Database\Seeders;

use App\Models\Acl\Permission;
use App\Models\Acl\PermissionTranslation;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AclPermissionSeeder extends Seeder
{
    /**
     * 權限 Seeder
     *
     * 權限命名規則：三段式 {module}.{resource}.{action}
     *
     * 支援 Wildcard Permission（config/permission.php → enable_wildcard_permission => true）
     * 例如角色擁有 catalog.product.* 即符合 catalog.product.list / .create / .update 等所有動作
     *
     * @see docs/md/0104_權限機制.md §3 權限設計
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── 商品型錄 ────────────────────────────────

            'catalog.product.list'    => ['en' => 'Product List',        'zh_Hant' => '商品列表'],
            'catalog.product.read'    => ['en' => 'View Product',        'zh_Hant' => '商品檢視'],
            'catalog.product.create'  => ['en' => 'Create Product',      'zh_Hant' => '商品新增'],
            'catalog.product.update'  => ['en' => 'Update Product',      'zh_Hant' => '商品修改'],
            'catalog.product.delete'  => ['en' => 'Delete Product',      'zh_Hant' => '商品刪除'],

            'catalog.option.list'     => ['en' => 'Option List',         'zh_Hant' => '選項列表'],
            'catalog.option.read'     => ['en' => 'View Option',         'zh_Hant' => '選項檢視'],
            'catalog.option.create'   => ['en' => 'Create Option',       'zh_Hant' => '選項新增'],
            'catalog.option.update'   => ['en' => 'Update Option',       'zh_Hant' => '選項修改'],
            'catalog.option.delete'   => ['en' => 'Delete Option',       'zh_Hant' => '選項刪除'],

            // ── 會員管理 ────────────────────────────────

            'member.member.list'      => ['en' => 'Member List',          'zh_Hant' => '會員列表'],
            'member.member.read'      => ['en' => 'View Member',          'zh_Hant' => '會員檢視'],
            'member.member.create'    => ['en' => 'Create Member',        'zh_Hant' => '會員新增'],
            'member.member.update'    => ['en' => 'Update Member',        'zh_Hant' => '會員修改'],
            'member.member.delete'    => ['en' => 'Delete Member',        'zh_Hant' => '會員刪除'],

            // ── 訂單管理 ────────────────────────────────

            'order.order.list'        => ['en' => 'Order List',          'zh_Hant' => '訂單列表'],
            'order.order.read'        => ['en' => 'View Order',          'zh_Hant' => '訂單檢視'],
            'order.order.create'      => ['en' => 'Create Order',        'zh_Hant' => '訂單新增'],
            'order.order.update'      => ['en' => 'Update Order',        'zh_Hant' => '訂單修改'],
            'order.order.delete'      => ['en' => 'Delete Order',        'zh_Hant' => '訂單刪除'],

            // ── 財務管理 ────────────────────────────────

            'finance.payment.list'    => ['en' => 'Payment List',        'zh_Hant' => '付款列表'],
            'finance.payment.read'    => ['en' => 'View Payment',        'zh_Hant' => '付款檢視'],
            'finance.payment.update'  => ['en' => 'Update Payment',      'zh_Hant' => '付款修改'],
            'finance.refund.list'     => ['en' => 'Refund List',         'zh_Hant' => '退款列表'],
            'finance.refund.read'     => ['en' => 'View Refund',         'zh_Hant' => '退款檢視'],
            'finance.refund.approve'  => ['en' => 'Approve Refund',      'zh_Hant' => '退款審核'],
        ];

        foreach ($permissions as $name => $translations) {
            $perm = Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );

            foreach ($translations as $locale => $displayName) {
                PermissionTranslation::updateOrCreate(
                    ['permission_id' => $perm->id, 'locale' => $locale],
                    ['display_name' => $displayName],
                );
            }
        }
    }
}
