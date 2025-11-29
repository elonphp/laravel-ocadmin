<?php

use Illuminate\Support\Facades\Route;
use Portals\Ocadmin\Http\Controllers\DashboardController;
use Portals\Ocadmin\Http\Controllers\System\SettingController;
use Portals\Ocadmin\Http\Controllers\System\Localization\CountryController;
use Portals\Ocadmin\Http\Controllers\System\Localization\DivisionController;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('ocadmin')->name('ocadmin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard API
    Route::get('/dashboard/chart-sales', [DashboardController::class, 'chartSales'])->name('dashboard.chart-sales');
    Route::get('/dashboard/map-data', [DashboardController::class, 'mapData'])->name('dashboard.map-data');

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
    });

});
