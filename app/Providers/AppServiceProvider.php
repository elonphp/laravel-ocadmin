<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
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
        // 用 try-catch 防止 migrate:fresh 時 cache table 尚未建立導致例外
        try {
            Cache::add('role_perm_ver', 1);
        } catch (\Exception $e) {
            // cache table 不存在（如 migrate:fresh），忽略
        }

        // 綁定唯一請求 ID（供日誌追蹤用）
        $this->app->singleton('request_id', fn () => (string) Str::uuid());

        // developer 角色無條件放行（開發商最高權限）
        Gate::before(fn ($user, $ability) => $user->hasRole('developer') ? true : null);

        // 登入事件 → 更新 users.last_login_at（涵蓋所有 Laravel Auth 路徑）
        Event::listen(Login::class, UpdateLastLoginAt::class);
    }
}
