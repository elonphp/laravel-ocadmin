<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 請求追蹤 ID - 用於關聯同一請求的多筆日誌記錄
        app()->singleton('request_trace_id', function () {
            return time() . '-' . uniqid();
        });
    }
}
