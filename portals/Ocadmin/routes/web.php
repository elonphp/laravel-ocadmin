<?php

use Illuminate\Support\Facades\Route;
use Portals\Ocadmin\Http\Controllers\DashboardController;
use Portals\Ocadmin\Http\Controllers\Localization\CountryController;
use Portals\Ocadmin\Http\Controllers\SettingController;

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

    // 系統管理 - 參數設定
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/create', [SettingController::class, 'create'])->name('create');
        Route::post('/', [SettingController::class, 'store'])->name('store');
        Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('edit');
        Route::put('/{setting}', [SettingController::class, 'update'])->name('update');
        Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy');
        Route::post('/batch-delete', [SettingController::class, 'batchDelete'])->name('batch-delete');
    });

    // 系統管理 - 本地化設定 (Localization)
    Route::prefix('localization')->name('localization.')->group(function () {
        // 國家管理
        Route::prefix('country')->name('country.')->group(function () {
            Route::get('/', [CountryController::class, 'index'])->name('index');
            Route::get('/list', [CountryController::class, 'list'])->name('list');
            Route::get('/create', [CountryController::class, 'create'])->name('create');
            Route::post('/', [CountryController::class, 'store'])->name('store');
            Route::get('/{country}/edit', [CountryController::class, 'edit'])->name('edit');
            Route::put('/{country}', [CountryController::class, 'update'])->name('update');
            Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
            Route::post('/batch-delete', [CountryController::class, 'batchDelete'])->name('batch-delete');
        });
    });

});
