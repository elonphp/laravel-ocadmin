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

        $transitions = require $file;

        if (!is_array($transitions) || empty($transitions)) {
            $this->info('No pending transitions.');
            return 0;
        }

        $valid = array_values(array_filter(
            $transitions,
            fn ($t) => is_array($t) && is_callable($t['up'] ?? null)
        ));

        if (empty($valid)) {
            $this->info('No pending transitions.');
            return 0;
        }

        $this->info(sprintf('Found %d pending transition(s):', count($valid)));
        foreach ($valid as $i => $t) {
            $this->line(sprintf('  [%d] %s', $i + 1, $t['description'] ?? '(no description)'));
        }

        if ($dryRun) {
            $this->comment('(dry-run, will not execute)');
            return 0;
        }

        $conn = DB::connection($connection);

        try {
            $conn->beginTransaction();
            foreach ($valid as $i => $t) {
                $this->line(sprintf('-> [%d] %s', $i + 1, $t['description'] ?? ''));
                call_user_func($t['up']);
            }
            // DDL（如 ALTER TABLE）在 MySQL 會隱式 commit，PDO 層已無 active transaction。
            // Laravel 的 transactionLevel 計數器不會同步歸零，因此不能依賴它；
            // 直接嘗試 commit，遇「no active transaction」視為已隱式提交，忽略。
            try {
                $conn->commit();
            } catch (\PDOException $e) {
                if (!str_contains($e->getMessage(), 'There is no active transaction')) {
                    throw $e;
                }
            }
            $this->info(sprintf('Executed %d transition(s) successfully.', count($valid)));
        } catch (\Throwable $e) {
            try {
                $conn->rollBack();
            } catch (\Throwable $ign) {
                // 同理：若 DDL 已 commit，rollBack 亦無 active transaction 可回
            }
            $this->error("Failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
