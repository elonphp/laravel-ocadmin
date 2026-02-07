<?php

namespace App\Console\Commands;

use App\Services\System\Database\SchemaDiffService;
use Illuminate\Console\Command;

class DbSyncCommand extends Command
{
    protected $signature = 'db:sync
                            {--table= : 只同步指定表}
                            {--dry-run : 預覽差異，不執行}
                            {--drop-columns : 允許刪除多餘欄位}
                            {--connection= : 指定資料庫連線}';

    protected $description = '比對 schema 定義與資料庫結構，執行同步';

    public function handle(SchemaDiffService $differ): int
    {
        $connection = $this->option('connection');
        $dryRun = $this->option('dry-run');
        $dropColumns = $this->option('drop-columns');
        $specificTable = $this->option('table');

        $this->info('Comparing schema definitions with database...');
        $this->newLine();

        // 取得狀態總覽
        $overview = $differ->getStatusOverview($connection);

        // 篩選指定表
        if ($specificTable) {
            $overview = array_filter($overview, fn($t) => $t['name'] === $specificTable);
            if (empty($overview)) {
                $this->error("Table '{$specificTable}' not found in schema files or database.");
                return 1;
            }
        }

        $changedCount = 0;
        $syncedCount = 0;
        $executedSqls = [];

        foreach ($overview as $table) {
            $name = $table['name'];
            $status = $table['status'];

            // 跳過僅 DB 的表（無 schema 檔）
            if ($status === 'db_only') {
                continue;
            }

            // 已同步
            if ($status === 'synced') {
                if ($this->output->isVerbose()) {
                    $this->line("[{$name}]");
                    $this->info('  (no changes)');
                    $this->newLine();
                }
                $syncedCount++;
                continue;
            }

            // 有差異或僅 schema
            $this->line("[{$name}]");
            $changedCount++;

            $sqls = $differ->generateSql($name, $connection, $dropColumns);
            $diff = $differ->diff($name, $connection);

            // 顯示變更
            foreach ($diff['changes'] as $change) {
                $icon = match ($change['action']) {
                    'add_column', 'add_translation_column' => '+',
                    'modify_column', 'modify_translation_column' => '~',
                    'extra_column' => '!',
                    'create_table', 'create_translation_table' => '+',
                    default => '?',
                };

                $desc = match ($change['action']) {
                    'add_column' => "ADD COLUMN {$change['column']} ({$change['definition']})",
                    'modify_column' => "MODIFY COLUMN {$change['column']} — " . implode(', ', $change['diffs'] ?? []),
                    'extra_column' => "EXTRA COLUMN {$change['column']}" . ($dropColumns ? ' (will drop)' : ' (ignored, use --drop-columns to remove)'),
                    'create_table' => 'CREATE TABLE',
                    'create_translation_table' => "CREATE TRANSLATION TABLE {$change['table']}",
                    'add_translation_column' => "ADD TRANSLATION COLUMN {$change['column']} on {$change['table']}",
                    'modify_translation_column' => "MODIFY TRANSLATION COLUMN {$change['column']} on {$change['table']} — " . implode(', ', $change['diffs'] ?? []),
                    default => $change['action'],
                };

                $color = match ($icon) {
                    '+' => 'info',
                    '~' => 'comment',
                    '!' => 'warn',
                    default => 'line',
                };

                $this->{"$color"}("  {$icon} {$desc}");
            }

            // 顯示 SQL（verbose 模式）
            if ($this->output->isVerbose() && !empty($sqls)) {
                $this->newLine();
                foreach ($sqls as $sql) {
                    $this->line("  <fg=gray>{$sql};</>");
                }
            }

            // 執行
            if (!$dryRun && !empty($sqls)) {
                $result = $differ->apply($name, $connection, $dropColumns);
                $executedSqls = array_merge($executedSqls, $result['executed']);
                $this->info("  => Applied " . count($result['executed']) . " statement(s)");
            }

            $this->newLine();
        }

        // 摘要
        $this->newLine();
        if ($dryRun) {
            $this->info("Summary: {$changedCount} table(s) need changes, {$syncedCount} table(s) up to date.");
            if ($changedCount > 0) {
                $this->comment('Run without --dry-run to apply.');
            }
        } else {
            $this->info("Summary: Executed " . count($executedSqls) . " statement(s) on {$changedCount} table(s).");
        }

        return 0;
    }
}
