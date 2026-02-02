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
        $this->registerViewComposers();
    }

    protected function loadViews(): void
    {
        $basePath = app_path('Portals/Ocadmin');
        View::addNamespace('ocadmin', $basePath . '/Core/Views');
        $this->loadModuleViews($basePath . '/Modules', '');
    }

    protected function loadModuleViews(string $modulesPath, string $prefix): void
    {
        if (!is_dir($modulesPath)) return;

        $dirs = scandir($modulesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;

            $fullPath = $modulesPath . '/' . $dir;
            if (!is_dir($fullPath)) continue;

            $modulePrefix = $prefix ? $prefix . '.' . strtolower($dir) : strtolower($dir);

            $viewsPath = $fullPath . '/Views';
            if (is_dir($viewsPath)) {
                View::addNamespace('ocadmin.' . $modulePrefix, $viewsPath);
            }

            $this->loadModuleViews($fullPath, $modulePrefix);
        }
    }

    protected function registerViewComposers(): void
    {
        view()->composer('ocadmin::layouts.partials.sidebar', \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class);
    }
}
