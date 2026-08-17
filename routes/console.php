<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| 系統維護排程
|--------------------------------------------------------------------------
*/

// 每月 1 日凌晨 4 點清理超過 6 個月的 request_logs
Schedule::command('request-logs:purge')
    ->monthlyOn(1, '04:00')
    ->withoutOverlapping();
