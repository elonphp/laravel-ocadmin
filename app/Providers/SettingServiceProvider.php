<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * 將 is_autoload=true 的 settings 預載入 Config
 *
 * 啟動時一次性查詢 DB，將結果寫入 config('settings.{code}')，
 * 後續透過 config() 或 setting() helper 存取，零 DB 查詢。
 *
 * 適用場景：Middleware 高頻讀取的設定（如 IP 白名單）、全域共用設定值。
 */
class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 避免 migrate 前資料表或欄位不存在時報錯
        if (!Schema::hasTable('settings') || !Schema::hasColumn('settings', 'is_autoload')) {
            return;
        }

        $settings = \App\Models\System\Setting::where('is_autoload', true)->get();

        foreach ($settings as $setting) {
            Config::set("settings.{$setting->code}", $setting->parsed_value);
        }
    }
}
