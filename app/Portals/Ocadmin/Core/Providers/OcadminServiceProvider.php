<?php

namespace App\Portals\Ocadmin\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class OcadminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['web'])
            ->group(app_path('Portals/Ocadmin/routes/ocadmin.php'));

        $this->loadViews();
        $this->registerViewComposers();
    }

    protected function loadViews(): void
    {
        View::addNamespace('ocadmin', app_path('Portals/Ocadmin/resources/views/ocadmin'));
    }

    protected function registerViewComposers(): void
    {
        view()->composer('ocadmin::layouts.partials.sidebar', \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class);
        view()->composer('ocadmin::*', \App\Portals\Ocadmin\Core\ViewComposers\LocaleComposer::class);
    }
}
