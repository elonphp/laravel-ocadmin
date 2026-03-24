<?php

/**
 * 資料庫結構與資料變更
 *
 * 執行：php artisan db:transition
 * 預覽：php artisan db:transition --dry-run
 *
 * 執行完畢後，清空 description 與 up，commit 到 git。
 */

// use Illuminate\Support\Facades\DB;

return [
    'description' => '',
    'up'          => null,

    // 範例：
    // 'description' => 'products: 新增 color 欄位',
    // 'up' => function () {
    //     DB::statement("ALTER TABLE `ctl_products` ADD COLUMN `color` VARCHAR(50) NULL AFTER `name`");
    // },
];
