<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Portals\Ocadmin\Http\Controllers\DashboardController;
use Portals\Ocadmin\Http\Controllers\System\SettingController;
use Portals\Ocadmin\Http\Controllers\System\Localization\CountryController;
use Portals\Ocadmin\Http\Controllers\System\Localization\DivisionController;
use Portals\Ocadmin\Http\Controllers\System\Database\MetaKeyController;
use Portals\Ocadmin\Http\Controllers\System\LogController;
use Portals\Ocadmin\Http\Controllers\System\Taxonomy\TaxonomyController;
use Portals\Ocadmin\Http\Controllers\System\Taxonomy\TermController;
use Portals\Ocadmin\Http\Controllers\Account\AccountController;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
|
| 所有路由都包含語系前綴，路由名稱使用 lang.ocadmin. 前綴。
|
| URL: /zh-hant/ocadmin/...
| URL: /en/ocadmin/...
|
| 路由名稱範例：
| - lang.ocadmin.dashboard
| - lang.ocadmin.login
| - lang.ocadmin.system.setting.index
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
| 多語系路由群組
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => '{locale}/ocadmin',
    'where' => ['locale' => $supportedUrlLocales ?: 'zh-hant|en'],
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

        // 帳號管理 (Account)
        Route::prefix('account')->name('account.')->group(function () {

            // 帳號
            Route::prefix('account')->name('account.')->group(function () {
                Route::get('/', [AccountController::class, 'index'])->name('index');
                Route::get('/list', [AccountController::class, 'list'])->name('list');
                Route::get('/create', [AccountController::class, 'create'])->name('create');
                Route::post('/', [AccountController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [AccountController::class, 'edit'])->name('edit');
                Route::put('/{user}', [AccountController::class, 'update'])->name('update');
                Route::delete('/{user}', [AccountController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [AccountController::class, 'batchDelete'])->name('batch-delete');
            });
        });

        // 系統管理 (System)
        Route::prefix('system')->name('system.')->group(function () {

            // 本地化設定 (Localization)
            Route::prefix('localization')->name('localization.')->group(function () {

                // 國家管理
                Route::prefix('country')->name('country.')->group(function () {
                    Route::get('/', [CountryController::class, 'index'])->name('index');
                    Route::get('/list', [CountryController::class, 'list'])->name('list');
                    Route::get('/all', [CountryController::class, 'all'])->name('all');
                    Route::get('/create', [CountryController::class, 'create'])->name('create');
                    Route::post('/', [CountryController::class, 'store'])->name('store');
                    Route::get('/{country}/edit', [CountryController::class, 'edit'])->name('edit');
                    Route::put('/{country}', [CountryController::class, 'update'])->name('update');
                    Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
                    Route::post('/batch-delete', [CountryController::class, 'batchDelete'])->name('batch-delete');
                });

                // 行政區域
                Route::prefix('division')->name('division.')->group(function () {
                    Route::get('/', [DivisionController::class, 'index'])->name('index');
                    Route::get('/list', [DivisionController::class, 'list'])->name('list');
                    Route::get('/create', [DivisionController::class, 'create'])->name('create');
                    Route::post('/', [DivisionController::class, 'store'])->name('store');
                    Route::get('/{division}/edit', [DivisionController::class, 'edit'])->name('edit');
                    Route::put('/{division}', [DivisionController::class, 'update'])->name('update');
                    Route::delete('/{division}', [DivisionController::class, 'destroy'])->name('destroy');
                    Route::post('/batch-delete', [DivisionController::class, 'batchDelete'])->name('batch-delete');
                });
            });

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

            // 資料庫 (Database)
            Route::prefix('database')->name('database.')->group(function () {

                // 欄位定義 (Meta Keys)
                Route::prefix('meta-key')->name('meta_key.')->group(function () {
                    Route::get('/', [MetaKeyController::class, 'index'])->name('index');
                    Route::get('/list', [MetaKeyController::class, 'list'])->name('list');
                    Route::get('/all', [MetaKeyController::class, 'all'])->name('all');
                    Route::get('/table-names', [MetaKeyController::class, 'tableNames'])->name('table-names');
                    Route::get('/create', [MetaKeyController::class, 'create'])->name('create');
                    Route::post('/', [MetaKeyController::class, 'store'])->name('store');
                    Route::get('/{metaKey}/edit', [MetaKeyController::class, 'edit'])->name('edit');
                    Route::put('/{metaKey}', [MetaKeyController::class, 'update'])->name('update');
                    Route::delete('/{metaKey}', [MetaKeyController::class, 'destroy'])->name('destroy');
                    Route::post('/batch-delete', [MetaKeyController::class, 'batchDelete'])->name('batch-delete');
                });
            });

            // 系統日誌 (Log)
            Route::prefix('log')->name('log.')->group(function () {
                Route::get('/', [LogController::class, 'index'])->name('index');
                Route::get('/list', [LogController::class, 'list'])->name('list');
                Route::get('/form', [LogController::class, 'form'])->name('form');
                Route::get('/files', [LogController::class, 'files'])->name('files');
            });

            // 詞彙管理 (Taxonomy)
            Route::prefix('taxonomy')->name('taxonomy.')->group(function () {

                // 分類法 (Taxonomies)
                Route::prefix('taxonomy')->name('taxonomy.')->group(function () {
                    Route::get('/', [TaxonomyController::class, 'index'])->name('index');
                    Route::get('/list', [TaxonomyController::class, 'list'])->name('list');
                    Route::get('/all', [TaxonomyController::class, 'all'])->name('all');
                    Route::get('/create', [TaxonomyController::class, 'create'])->name('create');
                    Route::post('/', [TaxonomyController::class, 'store'])->name('store');
                    Route::get('/{id}/edit', [TaxonomyController::class, 'edit'])->name('edit');
                    Route::put('/{id}', [TaxonomyController::class, 'update'])->name('update');
                    Route::delete('/{id}', [TaxonomyController::class, 'destroy'])->name('destroy');
                    Route::post('/batch-delete', [TaxonomyController::class, 'batchDelete'])->name('batch-delete');
                });

                // 詞彙 (Terms)
                Route::prefix('term')->name('term.')->group(function () {
                    Route::get('/', [TermController::class, 'index'])->name('index');
                    Route::get('/list', [TermController::class, 'list'])->name('list');
                    Route::get('/by-taxonomy/{taxonomyId}', [TermController::class, 'byTaxonomy'])->name('by-taxonomy');
                    Route::get('/create', [TermController::class, 'create'])->name('create');
                    // Route::get('/abc', [TermController::class, 'create'])->name('create'); // ok
                    // Route::get('/abc', [TermController::class, 'edit'])->name('edit'); // 404
                    Route::post('/', [TermController::class, 'store'])->name('store');
                    Route::get('/{id}/edit', [TermController::class, 'edit'])->name('edit');
                    Route::put('/{id}', [TermController::class, 'update'])->name('update');
                    Route::delete('/{id}', [TermController::class, 'destroy'])->name('destroy');
                    Route::post('/batch-delete', [TermController::class, 'batchDelete'])->name('batch-delete');
                });
            });
        });

    }); // end auth middleware

});

/*
|--------------------------------------------------------------------------
| 無語系前綴的重導向
|--------------------------------------------------------------------------
| 處理 /ocadmin 和 /ocadmin/{any} 的請求，重導向到預設語系
*/
Route::get('/ocadmin/{any?}', function ($any = '') {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $flipped = array_flip($urlMapping);
    $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';

    $path = $any ? "/ocadmin/{$any}" : '/ocadmin';
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*');

Route::match(['post', 'put', 'patch', 'delete'], '/ocadmin/{any?}', function ($any = '') {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $flipped = array_flip($urlMapping);
    $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';

    $path = $any ? "/ocadmin/{$any}" : '/ocadmin';
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*');
