<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Vite::prefetch(concurrency: 3);

        // 初始化角色權限快取版本號（僅在 key 不存在時寫入）
        Cache::add('role_perm_ver', 1);

        // 綁定唯一請求 ID（供日誌追蹤用）
        $this->app->singleton('request_id', fn () => (string) Str::uuid());

        // developer 角色無條件放行（開發商最高權限）
        Gate::before(fn ($user, $ability) => $user->hasRole('developer') ? true : null);
    }
}
