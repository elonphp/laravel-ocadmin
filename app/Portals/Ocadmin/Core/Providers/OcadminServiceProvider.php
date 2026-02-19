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

    protected function loadViews(): void
    {
        $basePath = app_path('Portals/Ocadmin');

        // adminlte namespace — 僅指向 AdminLTE 視圖
        View::addNamespace('adminlte', resource_path('views/adminlte'));

        // ocadmin namespace — AdminLTE 優先，Ocadmin fallback
        View::addNamespace('ocadmin', [
            resource_path('views/adminlte'),
            $basePath . '/Core/Views',
        ]);

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

            $kebab = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $dir));
            $modulePrefix = $prefix ? $prefix . '.' . $kebab : $kebab;

            $viewsPath = $fullPath . '/Views';
            if (is_dir($viewsPath)) {
                $namespace = 'ocadmin.' . $modulePrefix;
                $adminltePath = resource_path('views/adminlte/' . str_replace('.', '/', $modulePrefix));
                View::addNamespace($namespace, [
                    $adminltePath,
                    $viewsPath,
                ]);
                $this->moduleNamespaces[] = $namespace;
            }

            $this->loadModuleViews($fullPath, $modulePrefix);
        }
    }

    protected function registerViewComposers(): void
    {
        $menuComposer = \App\Portals\Ocadmin\Core\ViewComposers\MenuComposer::class;
        $localeComposer = \App\Portals\Ocadmin\Core\ViewComposers\LocaleComposer::class;

        // sidebar composer — 同時綁定 adminlte 與 ocadmin namespace
        view()->composer('adminlte::layouts.partials.sidebar', $menuComposer);
        view()->composer('ocadmin::layouts.partials.sidebar', $menuComposer);

        // locale composer — 同時綁定 adminlte 與 ocadmin namespace
        view()->composer('adminlte::*', $localeComposer);
        view()->composer('ocadmin::*', $localeComposer);

        // 為所有模組 view namespace 註冊 LocaleComposer
        foreach ($this->moduleNamespaces as $namespace) {
            view()->composer($namespace . '::*', $localeComposer);
        }
    }
}
