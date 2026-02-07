<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbTransitionCommand extends Command
{
    protected $signature = 'db:transition
                            {--dry-run : 預覽待執行的 transition，不實際執行}
                            {--connection= : 指定資料庫連線}';

    protected $description = '執行資料轉換腳本（database/transitions/）';

    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('database.default');
        $dryRun = $this->option('dry-run');

        // 確保 schema_transitions 表存在
        $this->ensureTransitionsTable($connection);

        // 取得所有 transition 檔案
        $files = $this->getTransitionFiles();

        if (empty($files)) {
            $this->info('No transition files found in database/transitions/');
            return 0;
        }

        // 取得已執行的版本
        $executed = DB::connection($connection)
            ->table('schema_transitions')
            ->pluck('version')
            ->toArray();

        // 篩選待執行
        $pending = [];
        foreach ($files as $file) {
            $transition = require $file;

            if (!isset($transition['version'])) {
                $this->warn("Skipping {$file}: missing 'version' key");
                continue;
            }

            if (in_array($transition['version'], $executed)) {
                if ($this->output->isVerbose()) {
                    $this->line("  Skip (already executed): v{$transition['version']} — {$transition['description']}");
                }
                continue;
            }

            $pending[] = [
                'file'        => $file,
                'version'     => $transition['version'],
                'description' => $transition['description'] ?? '',
                'up'          => $transition['up'] ?? null,
            ];
        }

        if (empty($pending)) {
            $this->info('All transitions are up to date.');
            return 0;
        }

        // 依版本排序
        usort($pending, fn($a, $b) => $a['version'] <=> $b['version']);

        $this->info('Pending transitions: ' . count($pending));
        $this->newLine();

        foreach ($pending as $item) {
            $this->line("[v{$item['version']}] {$item['description']}");

            if ($dryRun) {
                $this->comment('  (dry-run, will not execute)');
                continue;
            }

            if (!is_callable($item['up'])) {
                $this->warn("  Skipping: 'up' is not callable");
                continue;
            }

            try {
                DB::connection($connection)->beginTransaction();

                // 執行 transition
                call_user_func($item['up']);

                // 記錄已執行
                DB::connection($connection)->table('schema_transitions')->insert([
                    'version'     => $item['version'],
                    'description' => $item['description'],
                    'executed_at' => now(),
                ]);

                DB::connection($connection)->commit();

                $this->info("  => Executed successfully");
            } catch (\Throwable $e) {
                DB::connection($connection)->rollBack();
                $this->error("  => Failed: {$e->getMessage()}");
                return 1;
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->comment('Run without --dry-run to execute.');
        } else {
            $this->info('All transitions executed successfully.');
        }

        return 0;
    }

    /**
     * 確保 schema_transitions 表存在
     */
    protected function ensureTransitionsTable(string $connection): void
    {
        if (Schema::connection($connection)->hasTable('schema_transitions')) {
            return;
        }

        DB::connection($connection)->statement("
            CREATE TABLE `schema_transitions` (
                `version`      INT UNSIGNED NOT NULL PRIMARY KEY,
                `description`  VARCHAR(200) NULL,
                `executed_at`  DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->comment('Created schema_transitions table.');
    }

    /**
     * 取得所有 transition 檔案，依檔名排序
     */
    protected function getTransitionFiles(): array
    {
        $dir = database_path('transitions');

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.php');
        sort($files);

        return $files;
    }
}
