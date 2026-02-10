<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// HRM 排程
// ============================================================

// 每天凌晨 2 點執行昨天的打卡異常檢查
Schedule::command('hrm:check-abnormal-punch ' . now()->subDay()->toDateString())
    ->dailyAt('02:00');

// 每天凌晨 2 點統計本月的月報資料
Schedule::command('hrm:calculate-monthly-summary ' . now()->format('Ym'))
    ->dailyAt('02:00');

// 每月 1 日凌晨 2 點統計上個月的月報
Schedule::command('hrm:calculate-monthly-summary ' . now()->subMonth()->format('Ym'))
    ->monthlyOn(1, '02:00');
