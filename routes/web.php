<?php

use App\Helpers\Classes\LocaleHelper;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('<h1>Coming Soon</h1>', 200)
        ->header('Content-Type', 'text/html');
});

// DevLogin（POST /dev/login，僅本機）註冊在 AppServiceProvider::registerDevLoginRoute()，
// 寫在這裡會被 Portal 的 `{locale}/login` 先吃掉，原因詳見該方法註解。

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
