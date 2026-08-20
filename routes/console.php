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

// useCache() 的唯一作用是指定「排程互斥鎖」存在哪個 cache store，與快取讀寫無關
// （Illuminate\Console\Scheduling\Schedule::useCache → eventMutex / schedulingMutex 的 useStore）。
// 參數是 config('cache.stores') 的鍵名，不是 driver 名。
//
// 指定 database 後，withoutOverlapping() / onOneServer() 的鎖固定落在 cache_locks 表；
// 不指定則跟著 CACHE_STORE 漂移，改成 file 時鎖會變成與快取檔同目錄的鎖檔，
// 被 cache:clear file 連「正在被持有」的一起刪掉。詳 docs/common/10029 §7-4。
Schedule::useCache('database');

// 每季 1 日凌晨全清快取。快取是可拋棄的暫存，任何項目被刪掉都只是下次 miss、重建一次，
// 因此不做「只刪過期」的精細回收。cache:clear 一次只作用一個 store，故分兩行。
// 需要精細回收（不能承受一次全 miss）的環境改掛 cache:prune-expired，詳 docs/common/10029 §七。
Schedule::command('cache:clear database')->quarterlyOn(1, '03:00');
Schedule::command('cache:clear file')->quarterlyOn(1, '03:10');

// 每月 1 日凌晨 4 點清理超過 6 個月的 request_logs
Schedule::command('request-logs:purge')
    ->monthlyOn(1, '04:00')
    ->withoutOverlapping();
