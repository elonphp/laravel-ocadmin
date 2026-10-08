<?php

/*
|--------------------------------------------------------------------------
| 語系與網址語系段
|--------------------------------------------------------------------------
|
| 網址的第一段是語系：/zh-hant/admin/...、/en/admin/...
| 機制總覽：docs/common/10002_多語機制.md §二
|
| 常見組合：
|
|   多語系、網址一律帶語系段（本範本）
|     supported_locales => ['zh_Hant', 'en'], hide_default_locale => false
|     /admin/x → 302 /zh-hant/admin/x
|
|   多語系、預設語系不帶語系段
|     supported_locales => ['zh_Hant', 'en'], hide_default_locale => true
|     /admin/x 直接是中文；/en/admin/x 英文；/zh-hant/admin/x → 301 /admin/x
|
|   單一語系、不帶語系段
|     supported_locales => ['zh_Hant'], hide_default_locale => true
|     網址裡完全沒有語系段；/en/... 一律 404
|
*/

return [
    // 支援的語系（PHP / Laravel 格式）。從這裡拿掉的語系，網址一律 404
    'supported_locales' => ['zh_Hant', 'en'],

    'default_locale' => 'zh_Hant',

    // true：預設語系的網址不帶語系段
    'hide_default_locale' => false,

    // 網址沒帶語系段時用哪個語系（hide_default_locale=true 時不作用，一律預設語系）
    //   false：一律預設語系
    //   true ：帳號的預設語言（users.locale，沒有這個欄位就跳過）→ session（最後看過的語系）
    //          → 瀏覽器語言（Accept-Language）→ 預設語系
    'detect_preferred' => false,

    'admin_locale' => 'zh_Hant',

    // 網址語系段 → 語系
    'url_mapping' => [
        'zh-hant' => 'zh_Hant',
        'en'      => 'en',
    ],

    'locale_names' => [
        'zh_Hant' => '繁體中文',
        'en'      => 'English',
    ],

    // 舊網址的第一段（語系段之後）改寫：['舊' => '新']，'' 表示拿掉那一段
    // 例：['admin' => ''] → /zh-hant/admin/foo 導到 /zh-hant/foo
    // 只在原網址不存在、改寫後的網址存在時才重導
    'legacy_paths' => [],

    /*
    | BCP-47 alias 對照（瀏覽器 Accept-Language → internal locale），detect_preferred 用
    |
    | Symfony getPreferredLanguage() 只做「拿掉 region 退化到 primary」（zh-TW → zh），
    | 不認得「zh-TW 語意上等於 zh-Hant」這種 script-aware 推論。所以這張表補完。
    | 比對大小寫無關；不在 supported_locales 內的會被略過。
    */
    'bcp47_aliases' => [
        'zh_Hant' => ['zh', 'zh-Hant', 'zh-TW', 'zh-HK', 'zh-MO'],
        'en'      => ['en', 'en-US', 'en-GB', 'en-CA', 'en-AU', 'en-NZ'],
        // 'zh_Hans' => ['zh-Hans', 'zh-CN', 'zh-SG'],
        // 'ja'      => ['ja', 'ja-JP'],
    ],
];
