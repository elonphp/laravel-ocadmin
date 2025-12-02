<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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

        // super_admin 繞過所有權限檢查
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });

        // // Route Model Binding - 子目錄中的 Model 需要明確綁定
        // Route::model('country', \App\Models\System\Localization\Country::class);
        // Route::model('division', \App\Models\System\Localization\Division::class);
        // Route::model('setting', \App\Models\System\Setting::class);
        // Route::model('metaKey', \App\Models\System\Database\MetaKey::class);
        // Route::model('user', \App\Models\Identity\User::class);
    }
}
