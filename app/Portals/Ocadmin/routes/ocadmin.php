<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\LocaleHelper;
use App\Portals\Ocadmin\Core\Controllers\LoginController;
use App\Portals\Ocadmin\Modules\Dashboard\DashboardController;
use App\Portals\Ocadmin\Modules\System\Setting\SettingController;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => LocaleHelper::setLocale() . '/admin',
    'as' => 'lang.ocadmin.',
], function () {

    // 認證路由 (Guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [LoginController::class, 'login'])->name('login');
    });

    // 登出
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 需要登入的路由
    Route::middleware('auth')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Dashboard API
        Route::get('/dashboard/chart-sales', [DashboardController::class, 'chartSales'])->name('dashboard.chart-sales');
        Route::get('/dashboard/map-data', [DashboardController::class, 'mapData'])->name('dashboard.map-data');

        // 系統管理
        Route::prefix('system')->name('system.')->group(function () {

            // 參數設定
            Route::prefix('setting')->name('setting.')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('index');
                Route::get('/create', [SettingController::class, 'create'])->name('create');
                Route::post('/', [SettingController::class, 'store'])->name('store');
                Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('edit');
                Route::put('/{setting}', [SettingController::class, 'update'])->name('update');
                Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [SettingController::class, 'batchDelete'])->name('batch-delete');
                Route::post('/parse-serialize', [SettingController::class, 'parseSerialize'])->name('parse-serialize');
                Route::post('/to-serialize', [SettingController::class, 'toSerialize'])->name('to-serialize');
            });

        });

    });

});

/*
|--------------------------------------------------------------------------
| 無語系前綴的重導向
|--------------------------------------------------------------------------
*/
Route::get('/admin/{any?}', function ($any = '') {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $flipped = array_flip($urlMapping);
    $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';

    $path = $any ? "/admin/{$any}" : '/admin';
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*');
