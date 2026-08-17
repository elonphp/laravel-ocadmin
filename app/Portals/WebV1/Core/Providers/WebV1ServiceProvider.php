<?php

namespace App\Portals\WebV1\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * WebV1 Portal Service Provider
 *
 * 掛載前台官網路由 + 視圖 namespace。對應 config/portals.php 的 `webv1` 段。
 *
 * 雙模架構：本 portal 屬 Mode A 的前台；Mode B（ocadmin 兼任前台）不應同時存在 webv1 段，
 * OcadminServiceProvider::assertPortalModeConsistency() 會在 boot 階段擋下衝突態。
 *
 * @see docs/common/10001_Portal概述.md §九
 * @see docs/todo/20260516_前後台與單應用雙模架構.md
 */
class WebV1ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web'])->group(app_path('Portals/WebV1/routes/webv1.php'));

        View::addNamespace('webv1', app_path('Portals/WebV1/resources/views/webv1'));
    }
}
