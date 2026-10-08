<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('<h1>Coming Soon</h1>', 200)
        ->header('Content-Type', 'text/html');
});

// DevLogin（POST /dev/login，僅本機）註冊在 AppServiceProvider::registerDevLoginRoute()，
// 寫在這裡會被 Portal 的 `{locale}/login` 先吃掉，原因詳見該方法註解。

// 沒帶語系段的網址（/admin/... 等）由 LocaleFallbackController 補上語系段，見 LocaleServiceProvider
