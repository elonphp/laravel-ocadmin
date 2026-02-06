<?php

namespace App\Portals\ESS\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class EssServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
    }

    protected function loadRoutes(): void
    {
        Route::middleware(['web'])
            ->group(app_path('Portals/ESS/routes/ess.php'));
    }

    protected function loadViews(): void
    {
        View::addNamespace('ess', app_path('Portals/ESS/resources/views'));
    }
}
