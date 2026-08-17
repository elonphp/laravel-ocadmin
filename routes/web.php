<?php

use App\Helpers\Classes\LocaleHelper;
use App\Http\Controllers\Auth\DevLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('<h1>Coming Soon</h1>', 200)
        ->header('Content-Type', 'text/html');
});

/*
|--------------------------------------------------------------------------
| DevLogin 開發登入繞過機制（僅 APP_ENV=local + .env DEV_LOGIN_TOKEN 非空）
|--------------------------------------------------------------------------
|
| 任一條件不過 → 路由根本不註冊，端點對外即「不存在」。
| 詳：docs/common/10027_DevLogin開發登入繞過機制.md
|
*/
if (app()->environment('local') && config('auth.dev_login.token')) {
    Route::post('/dev/login', [DevLoginController::class, 'login'])
        ->middleware('logRequest')
        ->name('dev.login');
}

/*
|--------------------------------------------------------------------------
| Portal 無 locale 前綴 → 自動重導至預設語系
|--------------------------------------------------------------------------
|
| /admin{path} → /zh-hant/admin{path}
|
*/
$portalSlugs = collect(config('portals'))
    ->pluck('url_slug')
    ->filter()
    ->values();

foreach ($portalSlugs as $slug) {
    Route::get("{$slug}/{any?}", function (string $any = '') use ($slug) {
        $defaultUrlLocale = LocaleHelper::toUrlFormat(LocaleHelper::getDefaultLocale());
        $path = $any !== '' ? "/{$any}" : '';
        return redirect("/{$defaultUrlLocale}/{$slug}{$path}", 302);
    })->where('any', '.*');
}
