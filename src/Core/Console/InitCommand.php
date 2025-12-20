<?php

namespace Elonphp\LaravelOcadminModules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ocadmin:init';

    /**
     * The console command description.
     */
    protected $description = 'Initialize Ocadmin directory structure for customization';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $basePath = app_path('Ocadmin');

        if ($this->files->exists($basePath)) {
            $this->warn('app/Ocadmin directory already exists.');

            if (!$this->confirm('Do you want to continue and create missing directories?')) {
                return self::SUCCESS;
            }
        }

        $this->info('Creating app/Ocadmin directory structure...');

        $directories = [
            'Modules',
            'Resources/views/layouts',
            'Resources/views/components',
            'Resources/views/modules',
            'Config',
            'Routes',
        ];

        foreach ($directories as $dir) {
            $path = $basePath . '/' . $dir;
            if (!$this->files->exists($path)) {
                $this->files->makeDirectory($path, 0755, true);
                $this->line("  Created: app/Ocadmin/{$dir}");
            }
        }

        // Create placeholder files
        $this->createPlaceholderFiles($basePath);

        $this->newLine();
        $this->info('app/Ocadmin directory structure created successfully!');
        $this->newLine();
        $this->line('Directory structure:');
        $this->line('  app/Ocadmin/');
        $this->line('  ├── Modules/          # Custom modules');
        $this->line('  ├── Resources/views/  # Override package views');
        $this->line('  ├── Config/           # Custom configuration');
        $this->line('  └── Routes/           # Custom routes');

        return self::SUCCESS;
    }

    /**
     * Create placeholder files.
     */
    protected function createPlaceholderFiles(string $basePath): void
    {
        // Routes/routes.php
        $routesFile = $basePath . '/Routes/routes.php';
        if (!$this->files->exists($routesFile)) {
            $content = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Custom Ocadmin Routes
|--------------------------------------------------------------------------
|
| Define your custom routes here. They will be loaded with the Ocadmin
| middleware group and route prefix.
|
| Namespace: App\Ocadmin\Controllers
|
*/

// Route::get('/custom', [CustomController::class, 'index'])->name('custom.index');
PHP;
            $this->files->put($routesFile, $content);
            $this->line('  Created: app/Ocadmin/Routes/routes.php');
        }

        // Config/menu.php
        $menuFile = $basePath . '/Config/menu.php';
        if (!$this->files->exists($menuFile)) {
            $content = <<<'PHP'
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Menu Items
    |--------------------------------------------------------------------------
    |
    | Define your custom menu items here. They will be merged with the
    | module menu items.
    |
    */

    // [
    //     'title' => 'Custom',
    //     'icon' => 'cog',
    //     'children' => [
    //         [
    //             'title' => 'Custom Page',
    //             'route' => 'custom.index',
    //         ],
    //     ],
    // ],
];
PHP;
            $this->files->put($menuFile, $content);
            $this->line('  Created: app/Ocadmin/Config/menu.php');
        }

        // .gitkeep files
        $gitkeepDirs = [
            'Modules',
            'Resources/views/layouts',
            'Resources/views/components',
            'Resources/views/modules',
        ];

        foreach ($gitkeepDirs as $dir) {
            $gitkeepFile = $basePath . '/' . $dir . '/.gitkeep';
            if (!$this->files->exists($gitkeepFile)) {
                $this->files->put($gitkeepFile, '');
            }
        }
    }
}
