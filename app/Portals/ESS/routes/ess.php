<?php

use Illuminate\Support\Facades\Route;
use App\Portals\ESS\Modules\Auth\LoginController;
use App\Portals\ESS\Modules\Dashboard\DashboardController;
use App\Portals\ESS\Modules\Hrm\Employee\ProfileController;
use App\Portals\ESS\Core\Middleware\HandleEssInertiaRequests;

/*
|--------------------------------------------------------------------------
| ESS Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix'     => '{locale}/ess',
    'as'         => 'lang.ess.',
    'middleware'  => ['setLocale', HandleEssInertiaRequests::class],
], function () {

    // 未登入
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    // 已登入
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // 個人資料
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

});

/*
|--------------------------------------------------------------------------
| 無語系前綴的重導向
|--------------------------------------------------------------------------
*/
Route::get('/ess/{any?}', function ($any = '') {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $flipped = array_flip($urlMapping);
    $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';

    $path = $any ? "/ess/{$any}" : '/ess';
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*');
