<?php

return [
    'ocadmin' => [
        'web_enabled'       => env('OCADMIN_WEB_ENABLED', false),
        'url_slug'          => 'admin',
        'role_prefix'       => env('OCADMIN_ROLE_PREFIX', 'admin'),
        'permission_prefix' => env('OCADMIN_PERMISSION_PREFIX', 'admin'),
        'auth_driver'       => env('OCADMIN_AUTH_DRIVER', 'sanctum'),
        'dir'               => 'Ocadmin',
    ],
    'webv1' => [
        'url_slug'          => '',  // 空字串：path-based 但不帶中間層 → URL 形如 /{locale}/...
        'role_prefix'       => 'webv1',
        'permission_prefix' => 'webv1',
        'dir'               => 'WebV1',
    ],
];
