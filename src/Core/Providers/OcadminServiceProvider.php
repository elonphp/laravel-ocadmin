<?php

namespace Elonphp\LaravelOcadminModules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Elonphp\LaravelOcadminModules\Core\Support\ModuleLoader;
use Elonphp\LaravelOcadminModules\Core\Middleware\SetLocale;
use Elonphp\LaravelOcadminModules\Core\Middleware\RedirectToLocale;
use Elonphp\LaravelOcadminModules\Core\ViewComposers\MenuComposer;
use Elonphp\LaravelOcadminModules\Core\Console\InitCommand;
use Elonphp\LaravelOcadminModules\Core\Console\ModuleCommand;
use Elonphp\LaravelOcadminModules\Core\Console\ListCommand;

class OcadminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/ocadmin.php',
            'ocadmin'
        );

        $this->app->singleton(ModuleLoader::class, function ($app) {
            return new ModuleLoader($app);
        });

        $this->app->alias(ModuleLoader::class, 'ocadmin.modules');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMiddleware();
        $this->registerViews();
        $this->registerViewComposers();
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerBladeDirectives();
        $this->loadModules();
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('ocadmin.locale', SetLocale::class);
        $router->aliasMiddleware('ocadmin.redirect-locale', RedirectToLocale::class);
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../../resources/views', 'ocadmin');

        // Register custom views path (higher priority)
        $customViewsPath = app_path('Ocadmin/Resources/views');
        if (is_dir($customViewsPath)) {
            $this->loadViewsFrom($customViewsPath, 'ocadmin');
        }
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        // MenuComposer: 自動注入 $menus 變數到 sidebar
        View::composer('ocadmin::layouts.partials.sidebar', MenuComposer::class);
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang', 'ocadmin');

        // Allow publishing translations
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../../resources/lang');
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        // Load ocadmin routes (handles its own middleware and locale prefix)
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/ocadmin.php');
    }

    /**
     * Register publishing.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../../../config/ocadmin.php' => config_path('ocadmin.php'),
            ], 'ocadmin-config');

            // Migrations
            $this->publishes([
                __DIR__ . '/../../../database/migrations' => database_path('migrations'),
            ], 'ocadmin-migrations');

            // Assets
            $this->publishes([
                __DIR__ . '/../../../public/ocadmin' => public_path('vendor/ocadmin'),
            ], 'ocadmin-assets');

            // Translations
            $this->publishes([
                __DIR__ . '/../../../resources/lang' => $this->app->langPath('vendor/ocadmin'),
            ], 'ocadmin-lang');
        }
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitCommand::class,
                ModuleCommand::class,
                ListCommand::class,
            ]);
        }
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('ocadminStyles', function () {
            return "<?php echo app('Elonphp\\LaravelOcadminModules\\Core\\Support\\AssetManager')->renderStyles(); ?>";
        });

        Blade::directive('ocadminScripts', function () {
            return "<?php echo app('Elonphp\\LaravelOcadminModules\\Core\\Support\\AssetManager')->renderScripts(); ?>";
        });
    }

    /**
     * Load all modules.
     */
    protected function loadModules(): void
    {
        $this->app->make(ModuleLoader::class)->loadAll();
    }
}
