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
    | Portal 閘道設定（供 CheckPortalAuthorization 使用）
    |--------------------------------------------------------------------------
    |
    | 每個 Portal 可設定：
    |   api_key      — 應用層閘道 key（X-API-KEY），驗證請求來自我方授權的 App
    |   dev_key      — Dev Impersonation 專用 key（X-DEV-KEY），僅非 production 環境
    |   mode         — web（session 放行 + redirect）/ api（純 JSON 回應）
    |   redirect_url — web 模式失敗時的重導向 URL
    |   ip_restrict  — 是否啟用 Per-Portal IP 限制（白名單存於 settings 資料表）
    |
    */
    'portal_keys' => [
        'admin' => [
            'api_key'      => env('OCADMIN_API_KEY', ''),
            'dev_key'      => env('OCADMIN_DEV_KEY', ''),
            'mode'         => 'web',
            'redirect_url' => '/admin/login',
            'ip_restrict'  => env('OCADMIN_IP_RESTRICT', false),
        ],
        'api' => [
            'api_key'     => env('API_API_KEY', ''),
            'dev_key'     => env('API_DEV_KEY', ''),
            'mode'        => 'api',
            'ip_restrict' => env('API_IP_RESTRICT', false),
        ],
    ],
];
