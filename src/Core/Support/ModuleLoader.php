<?php

namespace Elonphp\LaravelOcadminModules\Core\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

class ModuleLoader
{
    protected Application $app;

    protected array $loadedModules = [];

    protected array $menuItems = [];

    protected ?array $dbModuleStates = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load all modules.
     */
    public function loadAll(): void
    {
        // Load module states from database
        $this->loadDbModuleStates();

        // Load custom modules first (they can override package modules)
        $this->loadCustomModules();

        // Load standard modules from package
        $this->loadStandardModules();

        // Sort modules by priority
        $this->sortModules();
    }

    /**
     * Load module states from database.
     */
    protected function loadDbModuleStates(): void
    {
        try {
            if (!Schema::hasTable('ocadmin_modules')) {
                $this->dbModuleStates = null;
                return;
            }

            $modules = DB::table('ocadmin_modules')->get();
            $this->dbModuleStates = [];

            foreach ($modules as $module) {
                $this->dbModuleStates[$module->alias] = (bool) $module->enabled;
            }
        } catch (\Exception $e) {
            // Database not ready yet
            $this->dbModuleStates = null;
        }
    }

    /**
     * Check if a module is enabled (considering database state).
     */
    protected function isModuleEnabled(string $alias, array $config): bool
    {
        // If database is available, use database state
        if ($this->dbModuleStates !== null) {
            // Module is in database - use its state
            if (isset($this->dbModuleStates[$alias])) {
                return $this->dbModuleStates[$alias];
            }

            // Module not in database - not installed yet
            // For ModuleManager (system module), always enable
            if ($config['system'] ?? false) {
                return true;
            }

            // For other modules, check config default
            return $config['enabled'] ?? false;
        }

        // No database yet - use config setting or module.json setting
        $configEnabled = config("ocadmin.modules.{$alias}");
        if ($configEnabled !== null) {
            return $configEnabled;
        }

        return $config['enabled'] ?? true;
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

        foreach (scandir($modulesPath) as $moduleName) {
            if ($moduleName === '.' || $moduleName === '..') {
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
        $alias = $moduleConfig['alias'] ?? Str::kebab($moduleName);

        // Check if module is enabled (database state takes priority)
        if (!$this->isModuleEnabled($alias, $moduleConfig)) {
            return;
        }

        $moduleKey = Str::kebab($moduleName);

        // Check if custom module overrides package module
        if ($source === 'package' && isset($this->loadedModules[$moduleKey])) {
            if ($this->loadedModules[$moduleKey]['source'] === 'custom') {
                // Custom module already loaded, skip package module
                return;
            }
        }

        $this->loadedModules[$moduleKey] = [
            'name' => $moduleName,
            'alias' => $alias,
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
        // Flat structure: routes.php in module root
        $routesFile = $modulePath . '/routes.php';

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
        // Flat structure: views/ in module root
        $viewsPath = $modulePath . '/views';

        if (!is_dir($viewsPath)) {
            return;
        }

        $moduleConfig = $this->getModuleConfig($modulePath);
        $namespace = $moduleConfig['alias'] ?? Str::kebab($moduleName);

        $this->app['view']->addNamespace($namespace, $viewsPath);
    }

    /**
     * Load module translations.
     */
    protected function loadModuleTranslations(string $modulePath, string $moduleName): void
    {
        // Flat structure: lang/ in module root
        $langPath = $modulePath . '/lang';

        if (!is_dir($langPath)) {
            return;
        }

        $moduleConfig = $this->getModuleConfig($modulePath);
        $namespace = $moduleConfig['alias'] ?? Str::kebab($moduleName);

        $this->app['translator']->addNamespace($namespace, $langPath);
    }

    /**
     * Load module menu configuration.
     */
    protected function loadModuleMenu(string $modulePath, string $moduleName): void
    {
        // Flat structure: menu.php in module root
        $menuFile = $modulePath . '/menu.php';

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
