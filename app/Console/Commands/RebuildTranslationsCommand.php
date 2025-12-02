<?php

namespace App\Console\Commands;

use App\Services\System\Database\TranslationTableSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 重建 sysdata translations 表
 *
 * 使用方式：
 * php artisan translations:rebuild terms        # 重建指定表
 * php artisan translations:rebuild --all        # 重建所有表
 * php artisan translations:rebuild terms --sync # 只同步資料，不調整結構
 */
class RebuildTranslationsCommand extends Command
{
    protected $signature = 'translations:rebuild
                            {table? : 指定資料表名稱（如 terms）}
                            {--all : 重建所有有翻譯資料的表}
                            {--sync : 只同步資料，不調整表結構}';

    protected $description = '重建 sysdata translations 表（從 metas 同步）';

    public function handle(TranslationTableSyncService $service): int
    {
        $tables = $this->getTables();

        if (empty($tables)) {
            $this->error('請指定 table 或使用 --all');
            return Command::FAILURE;
        }

        foreach ($tables as $table) {
            $this->info("處理 {$table}...");

            // 同步結構（除非只要 --sync）
            if (!$this->option('sync')) {
                $this->line('  同步表結構...');
                $changes = $service->syncTableStructure($table);
                $this->line("    新增欄位: " . count($changes['added']));
                $this->line("    移除欄位: " . count($changes['dropped']));
            }

            // 同步資料
            $this->line('  同步資料...');
            $count = $service->syncData($table);
            $this->line("    同步 {$count} 筆");

            $this->info("  完成!");
        }

        return Command::SUCCESS;
    }

    /**
     * 取得要處理的表名稱
     */
    protected function getTables(): array
    {
        if ($this->option('all')) {
            // 找出所有有 locale 非空資料的 metas 表
            return $this->findTablesWithTranslations();
        }

        if ($this->argument('table')) {
            return [$this->argument('table')];
        }

        return [];
    }

    /**
     * 找出所有有翻譯資料的主表
     */
    protected function findTablesWithTranslations(): array
    {
        $tables = [];

        // 取得所有 _metas 結尾的表
        $metaTables = DB::select("SHOW TABLES LIKE '%_metas'");

        foreach ($metaTables as $row) {
            $metaTable = array_values((array) $row)[0];

            // 檢查是否有 locale 欄位且有非空資料
            try {
                $hasTranslations = DB::table($metaTable)
                    ->whereNotNull('locale')
                    ->where('locale', '<>', '')
                    ->exists();

                if ($hasTranslations) {
                    // term_metas → terms
                    $mainTable = str_replace('_metas', 's', $metaTable);
                    $tables[] = $mainTable;
                }
            } catch (\Exception $e) {
                // 表可能沒有 locale 欄位，跳過
                continue;
            }
        }

        return $tables;
    }
}
