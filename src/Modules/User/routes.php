<?php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Modules\User\UserController;

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/list', [UserController::class, 'list'])->name('list');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    Route::post('/destroy-multiple', [UserController::class, 'destroyMultiple'])->name('destroy-multiple');
});
