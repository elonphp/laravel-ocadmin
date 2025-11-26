<?php

use Illuminate\Support\Facades\Route;
use Portals\Ocadmin\Http\Controllers\DashboardController;

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

});
