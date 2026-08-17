<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        // 多型別名表（morph map）：DB 多型欄（model_type / tokenable_type / 衍生專案的 termable_type）
        // 存「短別名」而非完整 FQCN——① model 路徑 refactor 不必動 DB ② 欄位瘦身。
        // enforceMorphMap 為強制模式：任何未註冊的多型 model 會拋例外（防呆）。
        // ⚠️ 全 app 生效——含 Spatie Permission（acl_model_has_roles/permissions → user）與
        //    Sanctum（personal_access_tokens.tokenable → user），故 User 必須在列。
        // 衍生專案（各自的內容表）在自己的 AppServiceProvider 追加別名。
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
        ]);

        // 初始化角色權限快取版本號（僅在 key 不存在時寫入）
        // 用 try-catch 防止 migrate:fresh 時 cache table 尚未建立導致例外
        try {
            Cache::add('role_perm_ver', 1);
        } catch (\Exception $e) {
            // cache table 不存在（如 migrate:fresh），忽略
        }

        // 綁定唯一請求 ID（供日誌追蹤用）
        $this->app->singleton('request_id', fn () => (string) Str::uuid());

        // super_admin 為本系統最高權限，Gate::before 無條件放行
        Gate::before(fn ($user, $ability) => $user->hasRole('super_admin') ? true : null);

        // 登入事件 → 更新 users.last_login_at（涵蓋所有 Laravel Auth 路徑）
        Event::listen(Login::class, UpdateLastLoginAt::class);
    }
}
