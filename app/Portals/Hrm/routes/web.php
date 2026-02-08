<?php

use App\Portals\Hrm\Modules\Calendar\CalendarDayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRM Portal Routes
|--------------------------------------------------------------------------
|
| HRM 人力資源管理系統路由
|
*/

// Dashboard
Route::get('/', function () {
    return inertia('Dashboard/Index', [
        'message' => 'Welcome to HRM Portal',
    ]);
})->name('dashboard');

// 行事曆管理
Route::prefix('calendar')->name('calendar.')->group(function () {
    // RESTful CRUD
    Route::get('/', [CalendarDayController::class, 'index'])->name('index');
    Route::get('/create', [CalendarDayController::class, 'create'])->name('create');
    Route::post('/', [CalendarDayController::class, 'store'])->name('store');
    Route::get('/{calendarDay}', [CalendarDayController::class, 'show'])->name('show');
    Route::get('/{calendarDay}/edit', [CalendarDayController::class, 'edit'])->name('edit');
    Route::put('/{calendarDay}', [CalendarDayController::class, 'update'])->name('update');
    Route::delete('/{calendarDay}', [CalendarDayController::class, 'destroy'])->name('destroy');

    // 批次操作
    Route::post('/batch-delete', [CalendarDayController::class, 'batchDelete'])->name('batch-delete');
    Route::post('/batch-create', [CalendarDayController::class, 'batchCreate'])->name('batch-create');

    // 特殊功能
    Route::post('/import-holidays', [CalendarDayController::class, 'importHolidays'])->name('import-holidays');
    Route::post('/set-makeup-workday', [CalendarDayController::class, 'setMakeupWorkday'])->name('set-makeup-workday');

    // API（查詢用）
    Route::get('/api/month', [CalendarDayController::class, 'getMonth'])->name('api.month');
});
