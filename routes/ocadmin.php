<?php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;
use Elonphp\LaravelOcadminModules\Core\Controllers\DashboardController;
use Elonphp\LaravelOcadminModules\Core\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
|
| All routes include locale prefix, route names use ocadmin. prefix.
|
| URL: /zh-hant/ocadmin/...
| URL: /en/ocadmin/...
|
| Route name examples:
| - ocadmin.dashboard
| - ocadmin.login
|
*/

$prefix = config('ocadmin.prefix', 'ocadmin');

/*
|--------------------------------------------------------------------------
| Locale Route Group
|--------------------------------------------------------------------------
| Uses LocaleHelper::setLocale() to set locale during route definition,
| so controllers can get correct locale in __construct().
*/
Route::group([
    'prefix' => LocaleHelper::setLocale() . '/' . $prefix,
    'as' => 'ocadmin.',
    'middleware' => ['web'],
], function () {

    // Auth routes (Guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Routes requiring authentication
    Route::middleware('auth')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Module routes are loaded by ModuleLoader

    }); // end auth middleware

});

/*
|--------------------------------------------------------------------------
| Redirect routes without locale prefix
|--------------------------------------------------------------------------
| Handle /ocadmin and /ocadmin/{any} requests, redirect to default locale
*/
Route::get('/' . $prefix . '/{any?}', function ($any = '') use ($prefix) {
    $defaultLocale = LocaleHelper::default();
    $urlLocale = LocaleHelper::toUrl($defaultLocale);

    $path = $any ? "/{$prefix}/{$any}" : "/{$prefix}";
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*')->middleware('web');

Route::match(['post', 'put', 'patch', 'delete'], '/' . $prefix . '/{any?}', function ($any = '') use ($prefix) {
    $defaultLocale = LocaleHelper::default();
    $urlLocale = LocaleHelper::toUrl($defaultLocale);

    $path = $any ? "/{$prefix}/{$any}" : "/{$prefix}";
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*')->middleware('web');
