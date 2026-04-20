<?php

/**
 * 資料庫結構與資料變更
 *
 * 執行：php artisan db:transition
 * 預覽：php artisan db:transition --dry-run
 *
 * 每筆變更為一個陣列項目，可累積多筆（不同部署之間）。
 * 執行成功後，清空整個陣列（保留空 []），commit 到 git。
 */

// use Illuminate\Support\Facades\DB;

return [

    // 範例：
    // [
    //     'description' => 'products: 新增 color 欄位',
    //     'up' => function () {
    //         DB::statement("ALTER TABLE `ctl_products` ADD COLUMN `color` VARCHAR(50) NULL AFTER `name`");
    //     },
    // ],

];
