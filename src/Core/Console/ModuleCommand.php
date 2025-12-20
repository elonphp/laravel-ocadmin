<?php

namespace Elonphp\LaravelOcadminModules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ocadmin:module {name : The name of the module}
                           {--description= : The module description}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new Ocadmin module';

    protected Filesystem $files;

    protected string $stubsPath;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->stubsPath = __DIR__ . '/../../../stubs/module';
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $description = $this->option('description') ?? "{$name} module";

        $basePath = app_path('Ocadmin/Modules');

        if (!$this->files->exists($basePath)) {
            $this->error('app/Ocadmin directory not found. Run "php artisan ocadmin:init" first.');
            return self::FAILURE;
        }

        $modulePath = $basePath . '/' . $name;

        if ($this->files->exists($modulePath)) {
            $this->error("Module {$name} already exists.");
            return self::FAILURE;
        }

        $this->info("Creating module: {$name}");

        // Create directories
        $directories = [
            'Controllers',
            'Models',
            'Views',
            'Routes',
            'Config',
        ];

        foreach ($directories as $dir) {
            $this->files->makeDirectory($modulePath . '/' . $dir, 0755, true);
        }

        // Create files from stubs
        $this->createFromStub('module.json', $modulePath . '/module.json', [
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_DESCRIPTION}}' => $description,
        ]);

        $moduleKey = Str::kebab($name);
        $namespace = 'App\\Ocadmin\\Modules\\' . $name;

        $this->createFromStub('Controllers/Controller.php', $modulePath . '/Controllers/' . $name . 'Controller.php', [
            '{{NAMESPACE}}' => $namespace,
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_KEY}}' => $moduleKey,
        ]);

        $this->createFromStub('Routes/routes.php', $modulePath . '/Routes/routes.php', [
            '{{NAMESPACE}}' => $namespace,
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_KEY}}' => $moduleKey,
        ]);

        $this->createFromStub('Views/index.blade.php', $modulePath . '/Views/index.blade.php', [
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_KEY}}' => $moduleKey,
        ]);

        $this->createFromStub('Views/form.blade.php', $modulePath . '/Views/form.blade.php', [
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_KEY}}' => $moduleKey,
        ]);

        $this->createFromStub('Config/menu.php', $modulePath . '/Config/menu.php', [
            '{{MODULE_NAME}}' => $name,
            '{{MODULE_KEY}}' => $moduleKey,
        ]);

        $this->newLine();
        $this->info("Module {$name} created successfully!");
        $this->line("Location: app/Ocadmin/Modules/{$name}");
        $this->line("Namespace: {$namespace}");

        return self::SUCCESS;
    }

    /**
     * Create a file from stub.
     */
    protected function createFromStub(string $stub, string $destination, array $replacements): void
    {
        $stubPath = $this->stubsPath . '/' . $stub . '.stub';

        if (!$this->files->exists($stubPath)) {
            $this->warn("Stub not found: {$stub}");
            return;
        }

        $content = $this->files->get($stubPath);

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        $this->files->put($destination, $content);

        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $destination);
        $this->line("  Created: {$relativePath}");
    }
}
