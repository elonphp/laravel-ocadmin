<?php

/**
 * ACL 可授權路由白名單
 *
 * 只有在此列出的路由才會出現在權限設定介面
 * 格式：'路由名稱' => '顯示名稱'
 *
 * 安全考量：
 * - 測試路由、開發路由不應列入
 * - 新增路由時需手動加入此設定檔
 */

return [

    /*
    |--------------------------------------------------------------------------
    | 後台路由（admin portal）
    |--------------------------------------------------------------------------
    */
    'admin' => [

        // 儀表板
        'admin.dashboard' => '儀表板',

        // 型錄管理
        'admin.catalog.options' => '選項管理',
        'admin.catalog.options.create' => '選項管理 - 新增',
        'admin.catalog.options.edit' => '選項管理 - 編輯',
        'admin.catalog.options.delete' => '選項管理 - 刪除',

        // 銷售管理
        'admin.sale.orders' => '訂單列表',
        'admin.sale.orders.show' => '訂單詳情',
        'admin.sale.orders.export' => '訂單匯出',

        // 經銷商管理
        'admin.dealer.organizations' => '經銷商列表',
        'admin.dealer.organizations.create' => '經銷商 - 新增',
        'admin.dealer.organizations.edit' => '經銷商 - 編輯',
        'admin.dealer.users' => '經銷商帳號',

        // 系統設定
        'admin.system.users' => '管理員帳號',
        'admin.system.users.create' => '管理員帳號 - 新增',
        'admin.system.users.edit' => '管理員帳號 - 編輯',
        'admin.system.roles' => '角色管理',
        'admin.system.roles.create' => '角色管理 - 新增',
        'admin.system.roles.edit' => '角色管理 - 編輯',

    ],

    /*
    |--------------------------------------------------------------------------
    | 前台路由（web portal）
    |--------------------------------------------------------------------------
    */
    'web' => [

        // 儀表板
        'web.dashboard' => '儀表板',

        // 訂單
        'web.orders' => '訂單列表',
        'web.orders.create' => '新增訂單',
        'web.orders.show' => '訂單詳情',
        'web.orders.edit' => '編輯訂單',

        // 帳號
        'web.profile' => '個人資料',

    ],

];
