<?php

namespace Elonphp\LaravelOcadminModules\Core\Console;

use Illuminate\Console\Command;
use Elonphp\LaravelOcadminModules\Core\Support\ModuleLoader;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ocadmin:list';

    /**
     * The console command description.
     */
    protected $description = 'List all loaded Ocadmin modules';

    /**
     * Execute the console command.
     */
    public function handle(ModuleLoader $loader): int
    {
        $modules = $loader->getLoadedModules();

        if (empty($modules)) {
            $this->info('No modules loaded.');
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($modules as $key => $module) {
            $rows[] = [
                $module['name'],
                $module['source'],
                $module['priority'],
                $module['config']['enabled'] ?? true ? 'active' : 'disabled',
            ];
        }

        $this->table(
            ['Module', 'Source', 'Priority', 'Status'],
            $rows
        );

        return self::SUCCESS;
    }
}
