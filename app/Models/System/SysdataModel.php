<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * Sysdata 資料庫基底 Model
 *
 * 所有存放在 sysdata 的 Model 應繼承此類別。
 * 覆寫 newRelatedInstance()，確保關聯查詢回到主資料庫，
 * 而非繼承 sysdata 連線。
 */
abstract class SysdataModel extends Model
{
    protected $connection = 'sysdata';

    /**
     * 覆寫：當關聯 Model 未宣告 $connection 時，
     * 使用應用程式預設連線（主資料庫），而非繼承 sysdata。
     */
    protected function newRelatedInstance($class)
    {
        return tap(new $class, function ($instance) {
            if (! $instance->getConnectionName()) {
                $instance->setConnection(config('database.default'));
            }
        });
    }
}
