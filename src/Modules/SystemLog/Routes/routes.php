<?php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Modules\SystemLog\Controllers\LogController;

Route::prefix('system/logs')->as('system.logs.')->group(function () {
    Route::get('database', [LogController::class, 'database'])->name('database');
    Route::get('archived', [LogController::class, 'archived'])->name('archived');
    Route::get('{id}', [LogController::class, 'show'])->name('show');
    Route::post('cleanup', [LogController::class, 'cleanup'])->name('cleanup');
});
