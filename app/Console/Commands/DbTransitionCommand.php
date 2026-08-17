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

        // 確保 schema_transitions 表與欄位齊備
        $this->ensureTransitionsTable($connection);

        // 取得所有 transition 檔案
        $files = $this->getTransitionFiles();

        if (empty($files)) {
            $this->info('No transition files found in database/transitions/');
            return 0;
        }

        // 防呆：偵測重複版本號。schema_transitions.version 是 PK，同版本號多檔時排序在後者
        // 會被當成「已執行」而靜默跳過。一律中止、逼使修正。
        if (!$this->assertNoDuplicateVersions($files)) {
            return 1;
        }

        // 取得已成功執行的版本（status='success' 才算完成；failed 會重試）
        $executed = DB::connection($connection)
            ->table('schema_transitions')
            ->where('status', 'success')
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
                'transaction' => $transition['transaction'] ?? true,
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

            // DDL 語句（ALTER TABLE 等）會觸發 MySQL 隱式 commit，不能包在 transaction 裡
            $useTransaction = $item['transaction'] ?? true;

            $status = 'success';
            $errorMessage = null;

            try {
                if ($useTransaction) {
                    DB::connection($connection)->beginTransaction();
                }

                call_user_func($item['up']);

                if ($useTransaction) {
                    DB::connection($connection)->commit();
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();

                if ($useTransaction && DB::connection($connection)->transactionLevel() > 0) {
                    try {
                        DB::connection($connection)->rollBack();
                    } catch (\Throwable $ign) {
                        // DDL 隱式 commit 之後可能無法 rollBack，忽略
                    }
                }
            }

            // 記錄要在 up() 的 transaction 之外，否則 DML 失敗時 rollBack 會把失敗紀錄一併吃掉
            DB::connection($connection)->table('schema_transitions')->updateOrInsert(
                ['version' => $item['version']],
                [
                    'description'   => $item['description'],
                    'status'        => $status,
                    'error_message' => $errorMessage,
                    'executed_at'   => now(),
                ]
            );

            if ($status === 'success') {
                $this->info("  => Executed successfully");
            } else {
                $this->error("  => Failed: {$errorMessage}");
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
     * 確保 schema_transitions 表存在且欄位齊備
     *
     * 第一次執行：建表（含 status / error_message）
     * 既有舊版：idempotent ADD COLUMN，舊行 default status='success'（既有紀錄都是執行成功才會在表裡）
     */
    protected function ensureTransitionsTable(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('schema_transitions')) {
            DB::connection($connection)->statement("
                CREATE TABLE `schema_transitions` (
                    `version`       VARCHAR(100) NOT NULL PRIMARY KEY,
                    `description`   VARCHAR(200) NULL,
                    `status`        VARCHAR(20)  NOT NULL DEFAULT 'success',
                    `error_message` TEXT         NULL,
                    `executed_at`   DATETIME     NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->comment('Created schema_transitions table.');
            return;
        }

        // 舊版建表時 version 可能是 INT，需升級為 VARCHAR(100)
        $col = collect(DB::connection($connection)->select("SHOW COLUMNS FROM `schema_transitions` LIKE 'version'"))->first();
        if ($col && !str_starts_with(strtolower($col->Type), 'varchar')) {
            DB::connection($connection)->statement("
                ALTER TABLE `schema_transitions`
                MODIFY COLUMN `version` VARCHAR(100) NOT NULL
            ");
            $this->comment('Upgraded schema_transitions.version to VARCHAR(100).');
        }

        // 舊版無 status 欄 → ADD COLUMN，default 'success'
        if (!Schema::connection($connection)->hasColumn('schema_transitions', 'status')) {
            DB::connection($connection)->statement("
                ALTER TABLE `schema_transitions`
                ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'success' AFTER `description`
            ");
            $this->comment('Added schema_transitions.status column (default success for existing rows).');
        }

        // 舊版無 error_message 欄 → ADD COLUMN
        if (!Schema::connection($connection)->hasColumn('schema_transitions', 'error_message')) {
            DB::connection($connection)->statement("
                ALTER TABLE `schema_transitions`
                ADD COLUMN `error_message` TEXT NULL AFTER `status`
            ");
            $this->comment('Added schema_transitions.error_message column.');
        }
    }

    /**
     * 防呆：偵測重複的版本號。
     *
     * schema_transitions.version 是 PK，用來判定「是否已執行」。若兩支腳本共用同一個
     * version，排序在後者會被 in_array($executed) 判成「已執行」而**整支靜默跳過**，
     * 且第一支成功寫入紀錄後，第二支永遠不會有機會執行。一旦偵測到重複即中止，逼使
     * 作者改成不重複的版本號。
     *
     * 回傳 true = 無重複可繼續；false = 有重複、呼叫端應中止。
     */
    protected function assertNoDuplicateVersions(array $files): bool
    {
        $seen = []; // version => [檔名, ...]
        foreach ($files as $file) {
            $transition = require $file;

            if (!is_array($transition) || !isset($transition['version'])) {
                continue;
            }

            $seen[(string) $transition['version']][] = basename($file);
        }

        $dups = array_filter($seen, fn($fs) => count($fs) > 1);

        if (empty($dups)) {
            return true;
        }

        $this->error('偵測到重複的 transition 版本號，已中止（請修正後再執行）：');
        foreach ($dups as $version => $fs) {
            $this->line("  v{$version}");
            foreach ($fs as $f) {
                $this->line("    - {$f}");
            }
        }
        $this->newLine();
        $this->warn('同版本號會讓排序在後的腳本被當成「已執行」而靜默跳過。請將其中一支改成未使用的版本號（建立新版本號前先查最近 5 支檔案）。');

        return false;
    }

    /**
     * 取得所有 transition 檔案，依檔名排序
     *
     * 排除 example.php — 它是給開發者複製用的範本，沒有 'version' key 是故意的。
     */
    protected function getTransitionFiles(): array
    {
        $dir = database_path('transitions');

        if (!is_dir($dir)) {
            return [];
        }

        $files = array_filter(
            glob($dir . '/*.php'),
            fn ($f) => basename($f) !== 'example.php',
        );
        sort($files);

        return $files;
    }
}
