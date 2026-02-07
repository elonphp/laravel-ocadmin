<?php

namespace App\Console\Commands;

use App\Services\System\Database\SchemaExportService;
use Illuminate\Console\Command;

class DbExportSchemaCommand extends Command
{
    protected $signature = 'db:export-schema
                            {--table= : 只匯出指定表}
                            {--dry-run : 預覽要匯出的表，不實際寫入}
                            {--connection= : 指定資料庫連線}';

    protected $description = '從資料庫現有結構匯出 schema 定義檔';

    public function handle(SchemaExportService $exporter): int
    {
        $connection = $this->option('connection');
        $dryRun = $this->option('dry-run');
        $specificTable = $this->option('table');

        if ($specificTable) {
            return $this->exportSingle($exporter, $specificTable, $connection, $dryRun);
        }

        return $this->exportAll($exporter, $connection, $dryRun);
    }

    protected function exportSingle(SchemaExportService $exporter, string $table, ?string $connection, bool $dryRun): int
    {
        $this->info("Exporting schema for: {$table}");

        if ($dryRun) {
            $schema = $exporter->exportToSchemaArray($table, $connection);
            $this->newLine();
            $this->line("Columns: " . count($schema['columns'] ?? []));
            if (!empty($schema['translations'])) {
                $this->line("Translations: " . count($schema['translations']));
            }
            if (!empty($schema['indexes'])) {
                $this->line("Composite indexes: " . count($schema['indexes']));
            }
            $this->newLine();
            $this->comment('Run without --dry-run to write the file.');
            return 0;
        }

        $exporter->exportToSchemaFile($table, $connection);
        $this->info("Schema file written: database/schema/tables/{$table}.php");

        return 0;
    }

    protected function exportAll(SchemaExportService $exporter, ?string $connection, bool $dryRun): int
    {
        $tables = $exporter->getTableList($connection);

        $this->info('Found ' . count($tables) . ' table(s) in database.');
        $this->newLine();

        $exported = [];

        foreach ($tables as $tableInfo) {
            $table = $tableInfo['name'];

            // 跳過翻譯表
            if (str_ends_with($table, '_translations')) {
                if ($this->output->isVerbose()) {
                    $this->line("  Skip (translation): {$table}");
                }
                continue;
            }

            if ($dryRun) {
                $comment = $tableInfo['comment'] ? " — {$tableInfo['comment']}" : '';
                $this->line("  {$table}{$comment}");
            } else {
                $exporter->exportToSchemaFile($table, $connection);
                $this->info("  Exported: {$table}");
            }

            $exported[] = $table;
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Total: " . count($exported) . " table(s) will be exported.");
            $this->comment('Run without --dry-run to write files.');
        } else {
            $this->info("Total: " . count($exported) . " table(s) exported to database/schema/tables/");
        }

        return 0;
    }
}
