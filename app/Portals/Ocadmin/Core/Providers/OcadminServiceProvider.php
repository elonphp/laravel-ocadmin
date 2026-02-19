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

    protected array $moduleNamespaces = [];

    /**
     * 視圖目錄名稱，對應 resources/views/{name}/
     * 若更換 UI 框架，只需修改此回傳值
     */
    protected function getViewDirectory(): string
    {
        return 'adminlte';
    }

    protected function loadViews(): void
    {
        $viewsPath = resource_path('views/' . $this->getViewDirectory());

        View::addNamespace('ocadmin', $viewsPath);

        $this->registerSubNamespaces($viewsPath, '');
    }

    protected function registerSubNamespaces(string $basePath, string $prefix): void
    {
        if (!is_dir($basePath)) return;

        foreach (scandir($basePath) as $dir) {
            if ($dir === '.' || $dir === '..') continue;

            $fullPath = $basePath . '/' . $dir;
            if (!is_dir($fullPath)) continue;

            $modulePrefix = $prefix ? $prefix . '.' . $dir : $dir;
            $namespace = 'ocadmin.' . $modulePrefix;
            View::addNamespace($namespace, $fullPath);
            $this->moduleNamespaces[] = $namespace;

            $this->registerSubNamespaces($fullPath, $modulePrefix);
        }
    }

    protected function registerViewComposers(): void
    {
        $menuComposer = \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class;
        $localeComposer = \App\Portals\Ocadmin\Core\ViewComposers\LocaleComposer::class;

        view()->composer('ocadmin::layouts.partials.sidebar', $menuComposer);
        view()->composer('ocadmin::*', $localeComposer);

        foreach ($this->moduleNamespaces as $namespace) {
            view()->composer($namespace . '::*', $localeComposer);
        }
    }
}
