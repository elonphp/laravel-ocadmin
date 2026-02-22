<?php

namespace App\Portals\Ocadmin\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        $this->loadTranslations();
        $this->registerViewComposers();
    }

    protected function loadViews(): void
    {
        View::addNamespace('ocadmin', app_path('Portals/Ocadmin/resources/views/ocadmin'));
    }

    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(app_path('Portals/Ocadmin/resources/lang'), 'ocadmin');
    }

    protected function registerViewComposers(): void
    {
        view()->composer('ocadmin::layouts.partials.sidebar', \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class);
        view()->composer('ocadmin::*', \App\Portals\Ocadmin\Core\ViewComposers\LocaleComposer::class);
    }
}
