<?php

namespace App\Portals\Ocadmin\Core\Providers;

use App\Portals\Ocadmin\Core\Contracts\Auth\AuthDriver;
use App\Portals\Ocadmin\Core\Drivers\Auth\OauthAuthDriver;
use App\Portals\Ocadmin\Core\Drivers\Auth\SanctumAuthDriver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class OcadminServiceProvider extends ServiceProvider
{
    /**
     * AuthDriver 對應表：config 字串 → driver class
     *
     * 衍生專案如要加新 driver（例：LDAP / SAML），在這裡擴充並建立對應 class。
     */
    protected const AUTH_DRIVERS = [
        'sanctum' => SanctumAuthDriver::class,
        'oauth'   => OauthAuthDriver::class,
    ];

    public function register(): void
    {
        // 認證 driver binding：依 config/portals.php 的 `ocadmin.auth_driver` 決定具體實作
        // routes 與 Controller 永遠引 AuthDriver 介面，切換 driver 不必動程式碼
        $this->app->bind(AuthDriver::class, function ($app) {
            $key = config('portals.ocadmin.auth_driver', 'sanctum');
            $class = self::AUTH_DRIVERS[$key] ?? null;

            if ($class === null) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown ocadmin.auth_driver: "%s"。可用值：%s',
                    $key,
                    implode(', ', array_keys(self::AUTH_DRIVERS)),
                ));
            }

            return $app->make($class);
        });
    }

    public function boot(): void
    {
        $this->assertPortalModeConsistency();

        // 1. 路由檔
        Route::middleware(['web'])->group(app_path('Portals/Ocadmin/routes/ocadmin.php'));

        // 2. 視圖
        View::addNamespace('ocadmin', app_path('Portals/Ocadmin/resources/views/ocadmin'));

        $this->registerViewComposers();
    }

    /**
     * 雙模架構衝突檢查：ocadmin.web_enabled=true 意指 ocadmin 兼任前台（Mode B），
     * 此時不該同時存在獨立的 webv1 portal。詳見 docs/todo/20260516_前後台與單應用雙模架構.md §3
     */
    protected function assertPortalModeConsistency(): void
    {
        $ocadminWebEnabled = config('portals.ocadmin.web_enabled', false);
        $webPortalExists = config('portals.webv1') !== null;

        if ($ocadminWebEnabled && $webPortalExists) {
            throw new \LogicException(
                'Portal 雙模架構衝突：config/portals.php 內 ocadmin.web_enabled=true 表示 ocadmin 兼任前台（Mode B），'
                . '但同時存在 webv1 portal 段。請擇一：移除 webv1 portal 段（純 Mode B）或關閉 ocadmin.web_enabled（Mode A）。'
            );
        }
    }

    // 把 sidebar 選單與當前語系資訊自動注入對應 view，免去每個 Controller 重複塞變數
    protected function registerViewComposers(): void
    {
        view()->composer('ocadmin::layouts.partials.sidebar', \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class);
        view()->composer('ocadmin::*', \App\Portals\Ocadmin\Core\ViewComposers\LocaleComposer::class);
    }
}
