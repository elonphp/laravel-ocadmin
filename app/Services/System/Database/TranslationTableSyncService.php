<?php

namespace App\Services\System\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TranslationTableSyncService
 *
 * 負責同步 sysdata 的 xxx_translations 表：
 * 1. 根據 meta_keys 自動調整表結構
 * 2. 從 xxx_metas 同步資料到 xxx_translations
 * 3. 支援單筆/批次同步和刪除
 */
class TranslationTableSyncService
{
    protected string $sysConnection = 'sysdata';

    /**
     * 同步指定表的 translations 結構
     */
    public function syncTableStructure(string $tableName): array
    {
        $translationTable = $this->getTranslationTableName($tableName);

        // 取得應有的欄位（從 meta_keys）
        $expectedColumns = $this->getExpectedColumns($tableName);

        // 取得現有欄位
        $currentColumns = $this->getCurrentColumns($translationTable);

        $changes = [
            'added' => [],
            'dropped' => [],
            'modified' => [],
        ];

        // 確保表存在
        if (!Schema::connection($this->sysConnection)->hasTable($translationTable)) {
            $this->createTranslationTable($tableName, $expectedColumns);
            $changes['added'] = array_keys($expectedColumns);
            return $changes;
        }

        // 比對差異並執行 ALTER
        foreach ($expectedColumns as $column => $definition) {
            if (!isset($currentColumns[$column])) {
                // 新增欄位
                $this->addColumn($translationTable, $column, $definition);
                $changes['added'][] = $column;
            } else {
                // 檢查是否需要修改欄位定義
                $expectedSql = $this->mapDataType($definition);
                $currentSql = $currentColumns[$column];
                if ($this->needsColumnModification($expectedSql, $currentSql)) {
                    $this->modifyColumn($translationTable, $column, $definition);
                    $changes['modified'][] = $column;
                }
            }
        }

        // 移除不再需要的欄位（保留 primary key 欄位）
        $reservedColumns = [$this->getForeignKeyName($tableName), 'locale'];
        foreach ($currentColumns as $column => $type) {
            if (!isset($expectedColumns[$column]) && !in_array($column, $reservedColumns)) {
                $this->dropColumn($translationTable, $column);
                $changes['dropped'][] = $column;
            }
        }

        return $changes;
    }

    /**
     * 取得應有的翻譯欄位
     *
     * 1. meta_keys 中 table_name 指定為此表的欄位 → 一律建立
     * 2. meta_keys 中 table_name 為 NULL（共用）且在 metas 表中有 locale 資料 → 建立
     */
    protected function getExpectedColumns(string $tableName): array
    {
        $metaTable = $this->getMetaTableName($tableName);

        // 1. 從 meta_keys 取得專屬於此表的所有欄位定義
        $dedicatedKeys = DB::table('meta_keys')
            ->where('table_name', $tableName)
            ->get();

        // 2. 若 metas 表存在，找出使用共用欄位且有 locale 的 meta_key_id
        $sharedKeys = collect();
        if (Schema::hasTable($metaTable)) {
            $sharedKeyIds = DB::table($metaTable)
                ->whereNotNull('locale')
                ->where('locale', '<>', '')
                ->distinct()
                ->pluck('meta_key_id')
                ->toArray();

            if (!empty($sharedKeyIds)) {
                $sharedKeys = DB::table('meta_keys')
                    ->whereIn('id', $sharedKeyIds)
                    ->whereNull('table_name')
                    ->get();
            }
        }

        // 合併所有欄位定義
        $columns = [];
        foreach ($dedicatedKeys->merge($sharedKeys) as $key) {
            $columns[$key->name] = [
                'type' => $key->data_type ?? 'varchar',
                'precision' => $key->precision,
                'nullable' => true,
            ];
        }

        return $columns;
    }

    /**
     * 取得現有欄位及其類型
     *
     * @return array [column_name => column_type, ...]
     */
    protected function getCurrentColumns(string $table): array
    {
        if (!Schema::connection($this->sysConnection)->hasTable($table)) {
            return [];
        }

        $columns = DB::connection($this->sysConnection)
            ->select("SHOW COLUMNS FROM `{$table}`");

        $result = [];
        foreach ($columns as $column) {
            $result[$column->Field] = strtoupper($column->Type);
        }

        return $result;
    }

    /**
     * 建立 translations 表
     */
    protected function createTranslationTable(string $tableName, array $columns): void
    {
        $translationTable = $this->getTranslationTableName($tableName);
        $foreignKey = $this->getForeignKeyName($tableName);

        Schema::connection($this->sysConnection)->create($translationTable, function ($table) use ($foreignKey, $columns) {
            $table->unsignedBigInteger($foreignKey);
            $table->string('locale', 10);

            foreach ($columns as $name => $definition) {
                $this->addColumnToBlueprint($table, $name, $definition);
            }

            $table->primary([$foreignKey, 'locale']);
            $table->index('locale');
        });
    }

    /**
     * 新增欄位
     */
    protected function addColumn(string $table, string $column, array $definition): void
    {
        $sqlType = $this->mapDataType($definition);

        DB::connection($this->sysConnection)->statement(
            "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$sqlType} NULL"
        );
    }

    /**
     * 移除欄位
     */
    protected function dropColumn(string $table, string $column): void
    {
        Schema::connection($this->sysConnection)->table($table, function ($blueprint) use ($column) {
            $blueprint->dropColumn($column);
        });
    }

    /**
     * 修改欄位定義
     */
    protected function modifyColumn(string $table, string $column, array $definition): void
    {
        $sqlType = $this->mapDataType($definition);

        DB::connection($this->sysConnection)->statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$sqlType} NULL"
        );
    }

    /**
     * 檢查是否需要修改欄位
     */
    protected function needsColumnModification(string $expected, string $current): bool
    {
        // 標準化比對（忽略大小寫、空格）
        $expected = strtoupper(trim($expected));
        $current = strtoupper(trim($current));

        // 完全相同
        if ($expected === $current) {
            return false;
        }

        // 特殊處理：INT vs INT(11) 等同
        $expectedBase = preg_replace('/\(\d+\)$/', '', $expected);
        $currentBase = preg_replace('/\(\d+\)$/', '', $current);

        // 整數類型不比較顯示寬度（MySQL 8.0+ 已棄用）
        $intTypes = ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT'];
        if (in_array($expectedBase, $intTypes) && in_array($currentBase, $intTypes)) {
            return $expectedBase !== $currentBase;
        }

        return $expected !== $current;
    }

    /**
     * 加入欄位到 Blueprint
     */
    protected function addColumnToBlueprint($table, string $name, array $definition): void
    {
        $type = $definition['type'] ?? 'varchar';
        $precision = $definition['precision'] ?? null;

        // 解析 precision（如 "13.4" → [13, 4]）
        [$total, $decimal] = $this->parsePrecision($precision);

        match ($type) {
            'varchar' => $table->string($name, $total ?: 255)->nullable(),
            'text' => $table->text($name)->nullable(),
            'longtext' => $table->longText($name)->nullable(),
            'json' => $table->json($name)->nullable(),
            'tinyint' => $table->tinyInteger($name)->nullable(),
            'smallint' => $table->smallInteger($name)->nullable(),
            'mediumint' => $table->mediumInteger($name)->nullable(),
            'int' => $table->integer($name)->nullable(),
            'bigint' => $table->bigInteger($name)->nullable(),
            'decimal' => $table->decimal($name, $total ?: 10, $decimal ?: 2)->nullable(),
            'boolean' => $table->boolean($name)->nullable(),
            default => $table->string($name, 255)->nullable(),
        };
    }

    /**
     * 解析精度設定
     *
     * @param string|null $precision 如 "255" 或 "13.4"
     * @return array [total, decimal] 如 [255, null] 或 [13, 4]
     */
    protected function parsePrecision(?string $precision): array
    {
        if (!$precision) {
            return [null, null];
        }

        if (str_contains($precision, '.')) {
            $parts = explode('.', $precision);
            return [(int) $parts[0], (int) $parts[1]];
        }

        return [(int) $precision, null];
    }

    /**
     * 資料類型對應 SQL
     */
    protected function mapDataType(array $definition): string
    {
        $type = $definition['type'] ?? 'varchar';
        $precision = $definition['precision'] ?? null;

        [$total, $decimal] = $this->parsePrecision($precision);

        return match ($type) {
            'varchar' => 'VARCHAR(' . ($total ?: 255) . ')',
            'text' => 'TEXT',
            'longtext' => 'LONGTEXT',
            'json' => 'JSON',
            'tinyint' => 'TINYINT',
            'smallint' => 'SMALLINT',
            'mediumint' => 'MEDIUMINT',
            'int' => 'INT',
            'bigint' => 'BIGINT',
            'decimal' => 'DECIMAL(' . ($total ?: 10) . ',' . ($decimal ?: 2) . ')',
            'boolean' => 'TINYINT(1)',
            default => 'VARCHAR(255)',
        };
    }

    /**
     * 同步資料（從 metas 到 translations）
     *
     * @param string $tableName 主表名稱（如 terms）
     * @param int|null $entityId 指定 ID，null 表示全部
     * @return int 同步筆數
     */
    public function syncData(string $tableName, ?int $entityId = null): int
    {
        $translationTable = $this->getTranslationTableName($tableName);
        $metaTable = $this->getMetaTableName($tableName);
        $foreignKey = $this->getForeignKeyName($tableName);

        // 查詢有 locale 的 metas 資料（翻譯資料）
        $query = DB::table($metaTable)
            ->whereNotNull('locale')
            ->where('locale', '<>', '');

        if ($entityId) {
            $query->where($foreignKey, $entityId);
        }

        $metas = $query->get();

        if ($metas->isEmpty()) {
            return 0;
        }

        // 取得相關的 meta_key_id → name 對應
        $keyIds = $metas->pluck('meta_key_id')->unique()->toArray();
        $metaKeyMap = DB::table('meta_keys')
            ->whereIn('id', $keyIds)
            ->pluck('name', 'id')
            ->toArray();

        // 整理為 translations 格式
        $translations = [];
        foreach ($metas as $meta) {
            $key = $meta->{$foreignKey} . ':' . $meta->locale;
            if (!isset($translations[$key])) {
                $translations[$key] = [
                    $foreignKey => $meta->{$foreignKey},
                    'locale' => $meta->locale,
                ];
            }
            $columnName = $metaKeyMap[$meta->meta_key_id];
            $translations[$key][$columnName] = $meta->meta_value;
        }

        // 寫入 translations 表
        $count = 0;
        foreach ($translations as $row) {
            DB::connection($this->sysConnection)
                ->table($translationTable)
                ->updateOrInsert(
                    [$foreignKey => $row[$foreignKey], 'locale' => $row['locale']],
                    $row
                );
            $count++;
        }

        return $count;
    }

    /**
     * 同步單一欄位的資料
     *
     * @param string $tableName 主表名稱
     * @param int $entityId 實體 ID
     * @param string $locale 語系
     * @param string $columnName 欄位名稱
     * @param mixed $value 值
     */
    public function syncSingleField(string $tableName, int $entityId, string $locale, string $columnName, $value): void
    {
        $translationTable = $this->getTranslationTableName($tableName);
        $foreignKey = $this->getForeignKeyName($tableName);

        DB::connection($this->sysConnection)
            ->table($translationTable)
            ->updateOrInsert(
                [$foreignKey => $entityId, 'locale' => $locale],
                [$columnName => $value]
            );
    }

    /**
     * 刪除 translations 資料
     */
    public function deleteTranslation(string $tableName, int $entityId): void
    {
        $translationTable = $this->getTranslationTableName($tableName);
        $foreignKey = $this->getForeignKeyName($tableName);

        DB::connection($this->sysConnection)
            ->table($translationTable)
            ->where($foreignKey, $entityId)
            ->delete();
    }

    /**
     * 刪除指定語系的 translations 資料
     */
    public function deleteTranslationByLocale(string $tableName, int $entityId, string $locale): void
    {
        $translationTable = $this->getTranslationTableName($tableName);
        $foreignKey = $this->getForeignKeyName($tableName);

        DB::connection($this->sysConnection)
            ->table($translationTable)
            ->where($foreignKey, $entityId)
            ->where('locale', $locale)
            ->delete();
    }

    /**
     * 清空指定表的所有 translations 資料
     */
    public function truncateTranslations(string $tableName): void
    {
        $translationTable = $this->getTranslationTableName($tableName);

        if (Schema::connection($this->sysConnection)->hasTable($translationTable)) {
            DB::connection($this->sysConnection)->table($translationTable)->truncate();
        }
    }

    /**
     * 刪除 translations 表
     */
    public function dropTranslationTable(string $tableName): void
    {
        $translationTable = $this->getTranslationTableName($tableName);

        Schema::connection($this->sysConnection)->dropIfExists($translationTable);
    }

    /**
     * 取得 translations 表名稱
     */
    protected function getTranslationTableName(string $tableName): string
    {
        // products → product_translations
        return rtrim($tableName, 's') . '_translations';
    }

    /**
     * 取得 metas 表名稱
     */
    protected function getMetaTableName(string $tableName): string
    {
        // products → product_metas
        return rtrim($tableName, 's') . '_metas';
    }

    /**
     * 取得外鍵名稱
     */
    protected function getForeignKeyName(string $tableName): string
    {
        // products → product_id
        return rtrim($tableName, 's') . '_id';
    }
}
