<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 多語系路由設定
|--------------------------------------------------------------------------
|
| 所有路由都包含語系前綴，路由名稱使用 lang. 前綴。
| 不使用 Session，純粹使用 URL 識別語系。
|
| URL: /zh-hant/products  → 內部 locale: zh_Hant
| URL: /en/products       → 內部 locale: en
| URL: /                  → 重導向到 /zh-hant/
|
| 路由名稱範例：
| - lang.home
| - lang.login
| - lang.catalog.products.index
|
*/

// 取得支援的語系 URL 前綴
$urlMapping = config('localization.url_mapping', []);
$supportedLocales = config('localization.supported_locales', []);

// 找出支援的 URL 前綴
$supportedUrlLocales = collect($urlMapping)
    ->filter(fn($internal) => in_array($internal, $supportedLocales))
    ->keys()
    ->implode('|');

/*
|--------------------------------------------------------------------------
| 根路由 - 重導向到預設語系首頁
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $urlLocale = array_search($defaultLocale, $urlMapping) ?: 'zh-hant';

    return redirect("/{$urlLocale}");
});

/*
|--------------------------------------------------------------------------
| 多語系路由群組
|--------------------------------------------------------------------------
| 所有前台路由都包在這個群組內，使用 lang. 前綴命名
*/
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => $supportedUrlLocales ?: 'zh-hant|en'],
    'as' => 'lang.',
    'middleware' => ['locale'],
], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    // 在此加入其他前台路由...
    // Route::get('/login', ...)->name('login');
    // Route::get('/products', ...)->name('catalog.products.index');
});
