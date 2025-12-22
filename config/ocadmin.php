<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for all Ocadmin routes.
    |
    */
    'prefix' => 'ocadmin',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to Ocadmin routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Localization Settings
    |--------------------------------------------------------------------------
    */
    'localization' => [
        // URL localization
        'url' => [
            'enabled' => true,
            'prefix' => true,
            'hide_default' => false,
        ],

        // Default locale
        'default' => 'zh_Hant',

        // Supported locales
        'supported' => ['zh_Hant', 'en'],

        // Locale display names
        'names' => [
            'zh_Hant' => '繁體中文',
            'en' => 'English',
        ],

        // URL format to internal format mapping
        'url_mapping' => [
            'zh-hant' => 'zh_Hant',
            'en' => 'en',
        ],

        // Content translation mode: SUFFIX or EAV
        'content' => [
            'mode' => env('TRANSLATION_MODE', 'SUFFIX'),
            'eav' => [
                'meta_keys_table' => 'meta_keys',
                'profile_prefix' => 'ztm_',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enabled Standard Modules
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'system-log' => true,
        'access-control' => true,
        'taxonomy' => true,
        'setting' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Order
    |--------------------------------------------------------------------------
    |
    | Define the order of top-level menu items by their title keys.
    |
    */
    'menu_order' => [
        'ocadmin::menu.users',   // 帳號管理
        'ocadmin::menu.system',  // 系統管理
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Modules Path
    |--------------------------------------------------------------------------
    |
    | Path to custom modules directory. Default: app/Ocadmin/Modules
    |
    */
    'custom_modules_path' => app_path('Ocadmin/Modules'),

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Specify which model classes to use. You can override package models
    | with your own models here.
    |
    */
    'models' => [
        'user' => \App\Models\User::class,
        // 'log' => \Elonphp\LaravelOcadminModules\Models\Log::class,
        // 'setting' => \Elonphp\LaravelOcadminModules\Models\Setting::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'css' => [
            '/vendor/ocadmin/css/app.css',
        ],
        'js' => [
            '/vendor/ocadmin/js/app.js',
        ],
    ],
];
