<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemModuleManager;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SystemModuleManagerService
{
    /**
     * Get all discovered modules (both package and custom).
     */
    public function discoverModules(): array
    {
        $modules = [];

        // Scan package modules
        $packagePath = dirname(__DIR__);
        $modules = array_merge($modules, $this->scanModules($packagePath, 'package'));

        // Scan custom modules
        $customPath = config('ocadmin.custom_modules_path', app_path('Ocadmin/Modules'));
        if (is_dir($customPath)) {
            $modules = array_merge($modules, $this->scanModules($customPath, 'custom'));
        }

        // Merge with database state
        return $this->mergeWithDatabase($modules);
    }

    /**
     * Scan modules from a directory.
     */
    protected function scanModules(string $basePath, string $source): array
    {
        $modules = [];

        if (!is_dir($basePath)) {
            return $modules;
        }

        $directories = scandir($basePath);

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $modulePath = $basePath . '/' . $dir;

            if (!is_dir($modulePath)) {
                continue;
            }

            $config = $this->getModuleConfig($modulePath);

            if (empty($config)) {
                continue;
            }

            $alias = $config['alias'] ?? Str::kebab($dir);

            $modules[$alias] = [
                'name' => $config['name'] ?? $dir,
                'alias' => $alias,
                'source' => $source,
                'version' => $config['version'] ?? '1.0.0',
                'description' => $config['description'] ?? '',
                'priority' => $config['priority'] ?? 50,
                'system' => $config['system'] ?? false,
                'path' => $modulePath,
                'config' => $config,
            ];
        }

        return $modules;
    }

    /**
     * Read module.json config.
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
     * Merge discovered modules with database state.
     */
    protected function mergeWithDatabase(array $modules): array
    {
        // Check if table exists
        if (!Schema::hasTable('ocadmin_modules')) {
            // Table not created yet, return with default disabled state
            foreach ($modules as $alias => &$module) {
                $module['installed'] = false;
                $module['enabled'] = $module['config']['enabled'] ?? false;
                $module['installed_at'] = null;
            }
            return $modules;
        }

        $dbModules = OcadminModule::all()->keyBy('alias');

        foreach ($modules as $alias => &$module) {
            if ($dbModules->has($alias)) {
                $dbModule = $dbModules->get($alias);
                $module['installed'] = true;
                $module['enabled'] = $dbModule->enabled;
                $module['installed_at'] = $dbModule->installed_at;
            } else {
                $module['installed'] = false;
                $module['enabled'] = false;
                $module['installed_at'] = null;
            }
        }

        return $modules;
    }

    /**
     * Get a single module info.
     */
    public function getModule(string $alias): ?array
    {
        $modules = $this->discoverModules();
        return $modules[$alias] ?? null;
    }

    /**
     * Check if a module is enabled.
     */
    public function isEnabled(string $alias): bool
    {
        // Check database first
        if (Schema::hasTable('ocadmin_modules')) {
            $module = OcadminModule::where('alias', $alias)->first();
            if ($module) {
                return $module->enabled;
            }
        }

        // Fallback to config
        return config("ocadmin.modules.{$alias}", true);
    }

    /**
     * Install a module.
     */
    public function install(string $alias, array $options = []): array
    {
        $module = $this->getModule($alias);

        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        // Check dependencies
        $depCheck = $this->checkDependencies($module);
        if (!$depCheck['success']) {
            return $depCheck;
        }

        // Check migrations
        $migrationCheck = $this->checkMigrations($module, $options);
        if (!$migrationCheck['success']) {
            return $migrationCheck;
        }

        // Run migrations if needed
        if (!empty($module['config']['migrations'])) {
            $this->runMigrations($module);
        }

        // Run seeders if requested
        if ($options['run_seeders'] ?? false) {
            $this->runSeeders($module);
        }

        // Save to database
        OcadminModule::updateOrCreate(
            ['alias' => $alias],
            [
                'name' => $module['name'],
                'source' => $module['source'],
                'version' => $module['version'],
                'enabled' => true,
                'installed_at' => now(),
                'config' => $module['config'],
            ]
        );

        // Clear caches
        $this->clearCaches();

        return ['success' => true, 'message' => 'Module installed successfully'];
    }

    /**
     * Enable a module.
     */
    public function enable(string $alias): array
    {
        $module = OcadminModule::where('alias', $alias)->first();

        if (!$module) {
            // Not installed yet, install it
            return $this->install($alias);
        }

        $module->update(['enabled' => true]);
        $this->clearCaches();

        return ['success' => true, 'message' => 'Module enabled'];
    }

    /**
     * Disable a module.
     */
    public function disable(string $alias): array
    {
        $moduleInfo = $this->getModule($alias);

        // Prevent disabling system modules
        if ($moduleInfo && ($moduleInfo['system'] ?? false)) {
            return ['success' => false, 'message' => 'Cannot disable system module'];
        }

        $module = OcadminModule::where('alias', $alias)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed'];
        }

        $module->update(['enabled' => false]);
        $this->clearCaches();

        return ['success' => true, 'message' => 'Module disabled'];
    }

    /**
     * Check module dependencies.
     */
    protected function checkDependencies(array $module): array
    {
        $deps = $module['config']['dependencies'] ?? [];

        // Check PHP version
        if (isset($deps['php'])) {
            // Simple version check (could be improved)
            $required = ltrim($deps['php'], '^~');
            if (version_compare(PHP_VERSION, $required, '<')) {
                return [
                    'success' => false,
                    'message' => "PHP {$deps['php']} required, current: " . PHP_VERSION
                ];
            }
        }

        // Check required modules
        if (isset($deps['modules'])) {
            foreach ($deps['modules'] as $requiredModule) {
                if (!$this->isEnabled($requiredModule)) {
                    return [
                        'success' => false,
                        'message' => "Required module '{$requiredModule}' is not enabled"
                    ];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Check migration status.
     */
    protected function checkMigrations(array $module, array $options): array
    {
        $migrations = $module['config']['migrations'] ?? [];

        foreach ($migrations as $migration) {
            // Extract table name from migration name (e.g., create_settings_table -> settings)
            if (preg_match('/create_(\w+)_table/', $migration, $matches)) {
                $tableName = $matches[1];

                if (Schema::hasTable($tableName)) {
                    if (!($options['use_existing_table'] ?? false)) {
                        return [
                            'success' => false,
                            'message' => "Table '{$tableName}' already exists",
                            'table_exists' => $tableName,
                        ];
                    }
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Run module migrations.
     */
    protected function runMigrations(array $module): void
    {
        $migrationsPath = $module['path'] . '/database/migrations';

        if (is_dir($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => $this->getRelativeMigrationPath($migrationsPath),
                '--force' => true,
            ]);
        }
    }

    /**
     * Get relative migration path for artisan command.
     */
    protected function getRelativeMigrationPath(string $absolutePath): string
    {
        $basePath = base_path();

        if (str_starts_with($absolutePath, $basePath)) {
            return str_replace($basePath . '/', '', $absolutePath);
        }

        // For vendor packages
        return $absolutePath;
    }

    /**
     * Run module seeders.
     */
    protected function runSeeders(array $module): void
    {
        $seeders = $module['config']['seeders'] ?? [];

        foreach ($seeders as $seeder) {
            // Build seeder class name based on module source
            if ($module['source'] === 'package') {
                $class = "Elonphp\\LaravelOcadminModules\\Modules\\{$module['name']}\\Database\\Seeders\\{$seeder}";
            } else {
                $class = "App\\Ocadmin\\Modules\\{$module['name']}\\Database\\Seeders\\{$seeder}";
            }

            if (class_exists($class)) {
                Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
            }
        }
    }

    /**
     * Clear application caches.
     */
    protected function clearCaches(): void
    {
        Artisan::call('cache:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Run pre-install checks and return results.
     */
    public function runPreInstallChecks(array $module): array
    {
        $checks = [
            'php_version' => $this->checkPhpVersion($module),
            'dependencies' => $this->checkModuleDependencies($module),
            'tables' => $this->checkTableConflicts($module),
        ];

        return $checks;
    }

    /**
     * Check PHP version requirement.
     */
    protected function checkPhpVersion(array $module): array
    {
        $required = $module['config']['dependencies']['php'] ?? null;

        if (!$required) {
            return ['passed' => true, 'message' => 'No PHP version requirement'];
        }

        $requiredVersion = ltrim($required, '^~');
        $passed = version_compare(PHP_VERSION, $requiredVersion, '>=');

        return [
            'passed' => $passed,
            'required' => $required,
            'current' => PHP_VERSION,
            'message' => $passed
                ? "PHP {$required} ✓ (current: " . PHP_VERSION . ")"
                : "PHP {$required} required, current: " . PHP_VERSION,
        ];
    }

    /**
     * Check module dependencies.
     */
    protected function checkModuleDependencies(array $module): array
    {
        $requiredModules = $module['config']['dependencies']['modules'] ?? [];

        if (empty($requiredModules)) {
            return ['passed' => true, 'message' => 'No module dependencies', 'modules' => []];
        }

        $missing = [];
        foreach ($requiredModules as $requiredModule) {
            if (!$this->isEnabled($requiredModule)) {
                $missing[] = $requiredModule;
            }
        }

        return [
            'passed' => empty($missing),
            'required' => $requiredModules,
            'missing' => $missing,
            'message' => empty($missing)
                ? 'All required modules are enabled'
                : 'Missing modules: ' . implode(', ', $missing),
        ];
    }

    /**
     * Check for table conflicts.
     */
    protected function checkTableConflicts(array $module): array
    {
        $migrations = $module['config']['migrations'] ?? [];
        $conflicts = [];

        foreach ($migrations as $migration) {
            if (preg_match('/create_(\\w+)_table/', $migration, $matches)) {
                $tableName = $matches[1];
                if (Schema::hasTable($tableName)) {
                    $conflicts[] = $tableName;
                }
            }
        }

        return [
            'passed' => empty($conflicts),
            'conflicts' => $conflicts,
            'message' => empty($conflicts)
                ? 'No table conflicts'
                : 'Existing tables: ' . implode(', ', $conflicts),
        ];
    }

    /**
     * Get all enabled modules (for menu building).
     */
    public function getEnabledModules(): array
    {
        $modules = $this->discoverModules();

        return array_filter($modules, fn($m) => $m['enabled']);
    }
}
