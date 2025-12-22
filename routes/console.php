<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| 排程任務
|--------------------------------------------------------------------------
| php artisan schedule:run  (執行一次)
| php artisan schedule:work (持續執行)
*/

// 每日刪除超過 90 天的檔案日誌
Schedule::command('app:delete-file-logs', ['90'])->dailyAt('02:00');

// 每月 1 日 03:00 歸檔上月日誌
Schedule::command('logs:archive')->monthlyOn(1, '03:00');

// 每月 1 日 04:00 刪除超過三個月的資料庫日誌
Schedule::command('logs:cleanup', ['3'])->monthlyOn(1, '04:00');
