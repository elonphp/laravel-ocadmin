<?php

namespace Elonphp\LaravelOcadminModules\Core\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

class ModuleLoader
{
    protected Application $app;

    protected array $loadedModules = [];

    protected array $menuItems = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load all modules.
     */
    public function loadAll(): void
    {
        // Load standard modules from package
        $this->loadStandardModules();

        // Load custom modules from project
        $this->loadCustomModules();

        // Sort modules by priority
        $this->sortModules();
    }

    /**
     * Load standard modules from package.
     */
    protected function loadStandardModules(): void
    {
        $modulesPath = __DIR__ . '/../../Modules';

        if (!is_dir($modulesPath)) {
            return;
        }

        $enabledModules = config('ocadmin.modules', []);

        foreach (scandir($modulesPath) as $moduleName) {
            if ($moduleName === '.' || $moduleName === '..') {
                continue;
            }

            $moduleKey = Str::kebab($moduleName);

            // Check if module is enabled
            if (!($enabledModules[$moduleKey] ?? true)) {
                continue;
            }

            $modulePath = $modulesPath . '/' . $moduleName;

            if (is_dir($modulePath)) {
                $this->loadModule($modulePath, $moduleName, 'package');
            }
        }
    }

    /**
     * Load custom modules from project.
     */
    protected function loadCustomModules(): void
    {
        $customPath = config('ocadmin.custom_modules_path', app_path('Ocadmin/Modules'));

        if (!is_dir($customPath)) {
            return;
        }

        foreach (scandir($customPath) as $moduleName) {
            if ($moduleName === '.' || $moduleName === '..') {
                continue;
            }

            $modulePath = $customPath . '/' . $moduleName;

            if (is_dir($modulePath)) {
                $this->loadModule($modulePath, $moduleName, 'custom');
            }
        }
    }

    /**
     * Load a single module.
     */
    protected function loadModule(string $modulePath, string $moduleName, string $source): void
    {
        $moduleConfig = $this->getModuleConfig($modulePath);

        if (!($moduleConfig['enabled'] ?? true)) {
            return;
        }

        $moduleKey = Str::kebab($moduleName);

        $this->loadedModules[$moduleKey] = [
            'name' => $moduleName,
            'path' => $modulePath,
            'source' => $source,
            'priority' => $moduleConfig['priority'] ?? 50,
            'config' => $moduleConfig,
        ];

        $this->loadModuleRoutes($modulePath, $moduleName);
        $this->loadModuleViews($modulePath, $moduleName);
        $this->loadModuleTranslations($modulePath, $moduleName);
        $this->loadModuleMenu($modulePath, $moduleName);
    }

    /**
     * Get module configuration from module.json.
     */
    protected function getModuleConfig(string $modulePath): array
    {
        $configFile = $modulePath . '/module.json';

        if (!file_exists($configFile)) {
            return [];
        }

        $content = file_get_contents($configFile);

        return json_decode($content, true) ?? [];
    }

    /**
     * Load module routes.
     */
    protected function loadModuleRoutes(string $modulePath, string $moduleName): void
    {
        $routesFile = $modulePath . '/Routes/routes.php';

        if (!file_exists($routesFile)) {
            return;
        }

        // Use same locale prefix as main routes
        $prefix = LocaleHelper::setLocale() . '/' . config('ocadmin.prefix', 'ocadmin');

        Route::middleware(config('ocadmin.middleware', ['web', 'auth']))
            ->prefix($prefix)
            ->as('ocadmin.')
            ->group($routesFile);
    }

    /**
     * Load module views.
     */
    protected function loadModuleViews(string $modulePath, string $moduleName): void
    {
        $viewsPath = $modulePath . '/Views';

        if (!is_dir($viewsPath)) {
            return;
        }

        $namespace = Str::kebab($moduleName);

        $this->app['view']->addNamespace($namespace, $viewsPath);
    }

    /**
     * Load module translations.
     */
    protected function loadModuleTranslations(string $modulePath, string $moduleName): void
    {
        $langPath = $modulePath . '/resources/lang';

        if (!is_dir($langPath)) {
            // Try alternative path
            $langPath = $modulePath . '/Lang';
        }

        if (!is_dir($langPath)) {
            return;
        }

        $namespace = Str::kebab($moduleName);

        $this->app['translator']->addNamespace($namespace, $langPath);
    }

    /**
     * Load module menu configuration.
     */
    protected function loadModuleMenu(string $modulePath, string $moduleName): void
    {
        $menuFile = $modulePath . '/Config/menu.php';

        if (!file_exists($menuFile)) {
            return;
        }

        $menuItems = require $menuFile;

        if (is_array($menuItems)) {
            $this->menuItems = array_merge($this->menuItems, $menuItems);
        }
    }

    /**
     * Sort modules by priority.
     */
    protected function sortModules(): void
    {
        uasort($this->loadedModules, function ($a, $b) {
            return ($a['priority'] ?? 50) <=> ($b['priority'] ?? 50);
        });
    }

    /**
     * Get all loaded modules.
     */
    public function getLoadedModules(): array
    {
        return $this->loadedModules;
    }

    /**
     * Get all menu items.
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    /**
     * Check if a module is loaded.
     */
    public function isLoaded(string $moduleKey): bool
    {
        return isset($this->loadedModules[Str::kebab($moduleKey)]);
    }

    /**
     * Get module by key.
     */
    public function getModule(string $moduleKey): ?array
    {
        return $this->loadedModules[Str::kebab($moduleKey)] ?? null;
    }
}
