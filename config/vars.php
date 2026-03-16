<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 後台資料夾名稱
    |--------------------------------------------------------------------------
    |
    | URL: /admin, /zh-hant/admin
    |
    */
    'admin_folder' => env('ADMIN_FOLDER', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Portal 閘道 Keys（供 CheckPortalAuthorization 使用）
    |--------------------------------------------------------------------------
    |
    | 每個 Portal 可設定：
    |   api_key      — 靜態 key，略過 IP 白名單檢查（網路層）
    |   mode         — web（session 放行 + redirect）/ api（純 JSON 回應）
    |   redirect_url — web 模式失敗時的重導向 URL
    |
    */
    'portal_keys' => [
        'admin' => [
            'api_key'      => env('ADMIN_API_KEY', ''),
            'mode'         => 'web',
            'redirect_url' => '/admin/login',
        ],
        'api' => [
            'api_key' => env('API_API_KEY', ''),
            'mode'    => 'api',
        ],
    ],
];
