<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\Classes\LocaleHelper;

Route::get('/', function () {
    return response('<h1>Coming Soon</h1>', 200)
        ->header('Content-Type', 'text/html');
});

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
