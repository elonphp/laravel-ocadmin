<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbTransitionCommand extends Command
{
    protected $signature = 'db:transition
                            {--dry-run : 預覽待執行內容，不實際執行}
                            {--connection= : 指定資料庫連線}';

    protected $description = '執行結構與資料變更腳本（database/schema/transitions.php）';

    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('database.default');
        $dryRun = $this->option('dry-run');

        $file = database_path('schema/transitions.php');

        if (!file_exists($file)) {
            $this->info('File not found: database/schema/transitions.php');
            return 0;
        }

        $transition = require $file;

        if (!is_array($transition) || !is_callable($transition['up'] ?? null)) {
            $this->info('No pending transition.');
            return 0;
        }

        $description = $transition['description'] ?? '';
        $this->info($description ?: 'Pending transition found.');

        if ($dryRun) {
            $this->comment('(dry-run, will not execute)');
            return 0;
        }

        try {
            DB::connection($connection)->beginTransaction();
            call_user_func($transition['up']);
            DB::connection($connection)->commit();
            $this->info('Executed successfully.');
        } catch (\Throwable $e) {
            DB::connection($connection)->rollBack();
            $this->error("Failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
