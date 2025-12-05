<?php

namespace Portals\Ocadmin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OcadminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 載入路由（套用 web + locale middleware）
        // 注意：使用完整類名而非別名，避免與 PHP intl 的 Locale 類衝突
        Route::middleware(['web', \App\Http\Middleware\SetLocale::class])
            ->group(base_path('portals/Ocadmin/routes/ocadmin.php'));

        // 載入視圖（命名空間 ocadmin::）
        $this->loadViewsFrom(base_path('portals/Ocadmin/resources/views'), 'ocadmin');

        // 註冊 View Composers
        $this->registerViewComposers();
    }

    protected function registerViewComposers(): void
    {
        view()->composer('ocadmin::layouts.partials.sidebar', \Portals\Ocadmin\ViewComposers\MenuComposer::class);
    }
}
