<?php

namespace App\Providers;

use App\Http\Controllers\Auth\DevLoginController;
use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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

        $this->registerDevLoginRoute();
    }

    /**
     * DevLogin 開發登入繞過路由（僅 APP_ENV=local + .env DEV_LOGIN_TOKEN 非空）
     *
     * 任一條件不過 → 路由根本不註冊，端點對外即「不存在」。
     * 4 道閘與用法見 DevLoginController；詳：docs/common/10027_DevLogin開發登入繞過機制.md
     *
     * ⚠️ 為什麼註冊在這裡而不是 routes/web.php：
     * Portal 路由群組前綴是 `{locale}` 且無格式限制，一旦有 Portal 開了 `{locale}/login`
     * （如前台會員登入），`POST /dev/login` 就會被它（locale="dev"）先吃掉，SetLocale
     * 認不得 "dev" 就 302——永遠進不了 DevLoginController。
     * routes/web.php 是在 bootstrap/providers.php 各 provider（含 Portal 路由）
     * boot 完之後才載入，寫在那裡搶不到優先序；AppServiceProvider 排第一位，在此註冊才先比對到。
     */
    private function registerDevLoginRoute(): void
    {
        if (! $this->app->environment('local') || ! config('auth.dev_login.token')) {
            return;
        }

        Route::middleware(['web', 'logRequest'])
            ->post('/dev/login', [DevLoginController::class, 'login'])
            ->name('dev.login');
    }
}
