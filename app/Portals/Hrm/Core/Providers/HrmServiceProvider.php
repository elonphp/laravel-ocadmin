<?php

namespace App\Portals\Hrm\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HrmServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 註冊 Console Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Portals\Hrm\Console\Commands\GenerateCalendarDaysCommand::class,
                \App\Portals\Hrm\Console\Commands\CalculateMonthlySummaryCommand::class,
                \App\Portals\Hrm\Console\Commands\CheckAbnormalPunchCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 載入 Views
        $this->loadViewsFrom(app_path('Portals/Hrm/Core/Views'), 'hrm');

        // 載入 Routes
        $this->loadRoutes();
    }

    /**
     * 載入路由
     */
    protected function loadRoutes(): void
    {
        Route::middleware(['web'])
            ->prefix('hrm')
            ->name('hrm.')
            ->group(base_path('app/Portals/Hrm/routes/web.php'));
    }
}
