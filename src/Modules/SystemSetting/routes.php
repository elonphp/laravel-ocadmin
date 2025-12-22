<?php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Modules\SystemSetting\SystemSettingController;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SystemSettingController::class, 'index'])->name('index');
    Route::get('/list', [SystemSettingController::class, 'list'])->name('list');
    Route::get('/create', [SystemSettingController::class, 'create'])->name('create');
    Route::post('/', [SystemSettingController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [SystemSettingController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SystemSettingController::class, 'update'])->name('update');
    Route::delete('/{id}', [SystemSettingController::class, 'destroy'])->name('destroy');
    Route::post('/destroy-multiple', [SystemSettingController::class, 'destroyMultiple'])->name('destroy-multiple');
});
