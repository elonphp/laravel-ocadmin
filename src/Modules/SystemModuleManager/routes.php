<?php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Modules\SystemModuleManager\SystemModuleManagerController;

Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [SystemModuleManagerController::class, 'index'])->name('index');
    Route::get('/{alias}', [SystemModuleManagerController::class, 'show'])->name('show');
    Route::get('/{alias}/install', [SystemModuleManagerController::class, 'installForm'])->name('install.form');
    Route::post('/{alias}/install', [SystemModuleManagerController::class, 'install'])->name('install');
    Route::post('/{alias}/enable', [SystemModuleManagerController::class, 'enable'])->name('enable');
    Route::post('/{alias}/disable', [SystemModuleManagerController::class, 'disable'])->name('disable');
});
