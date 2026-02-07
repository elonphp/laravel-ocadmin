<?php

namespace App\Services\System\Database;

use Illuminate\Support\Facades\DB;

/**
 * Schema 匯出服務
 *
 * 從 INFORMATION_SCHEMA 讀取資料庫現有結構，
 * 轉換為 schema 定義格式並匯出為檔案。
 */
class SchemaExportService
{
    protected SchemaParserService $parser;

    /**
     * 排除的 Laravel 框架表（不匯出）
     */
    protected array $excludedTables = [
        'cache', 'cache_locks',
        'failed_jobs', 'job_batches', 'jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'schema_transitions',
    ];

    public function __construct(SchemaParserService $parser)
    {
        $this->parser = $parser;
    }

    /**
     * 取得資料庫所有表名（排除框架表）
     *
     * @return array [表名, ...]
     */
    public function getTableList(?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $tables = DB::connection($connection)->select(
            "SELECT TABLE_NAME, TABLE_COMMENT
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY TABLE_NAME",
            [$database]
        );

        $result = [];
        foreach ($tables as $table) {
            if (!in_array($table->TABLE_NAME, $this->excludedTables)) {
                $result[] = [
                    'name'    => $table->TABLE_NAME,
                    'comment' => $table->TABLE_COMMENT ?: null,
                ];
            }
        }

        return $result;
    }

    /**
     * 讀取單表完整結構
     *
     * @return array ['columns' => [...], 'indexes' => [...], 'foreign_keys' => [...]]
     */
    public function getTableStructure(string $table, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        return [
            'columns'      => $this->getColumns($database, $table, $connection),
            'indexes'       => $this->getIndexes($database, $table, $connection),
            'foreign_keys' => $this->getForeignKeys($database, $table, $connection),
            'comment'      => $this->getTableComment($database, $table, $connection),
        ];
    }

    /**
     * 將資料庫表結構轉為 schema 定義陣列
     */
    public function exportToSchemaArray(string $table, ?string $connection = null): array
    {
        $structure = $this->getTableStructure($table, $connection);
        $schema = [];

        // 表備註
        if (!empty($structure['comment'])) {
            $schema['comment'] = $structure['comment'];
        }

        // 欄位定義
        $schema['columns'] = [];

        // 偵測翻譯表
        $translationTable = $this->detectTranslationTable($table, $connection);

        foreach ($structure['columns'] as $col) {
            $meta = $this->columnToMeta($col, $structure['indexes'], $structure['foreign_keys']);
            $schema['columns'][$col->COLUMN_NAME] = $this->parser->buildColumnDefinition($meta);
        }

        // 複合索引（排除單欄索引和主鍵）
        $compositeIndexes = $this->getCompositeIndexes($structure['indexes']);
        if (!empty($compositeIndexes)) {
            $schema['indexes'] = $compositeIndexes;
        }

        // 複合唯一索引
        $compositeUniques = $this->getCompositeUniques($structure['indexes']);
        if (!empty($compositeUniques)) {
            $schema['unique'] = $compositeUniques;
        }

        // 翻譯欄位
        if ($translationTable) {
            $schema['translations'] = $this->extractTranslationColumns($translationTable, $connection);
        }

        return $schema;
    }

    /**
     * 匯出單表為 schema 檔案
     */
    public function exportToSchemaFile(string $table, ?string $connection = null): void
    {
        $schema = $this->exportToSchemaArray($table, $connection);
        $this->parser->saveSchemaFile($table, $schema);
    }

    /**
     * 匯出所有表
     *
     * @return array 已匯出的表名列表
     */
    public function exportAll(?string $connection = null): array
    {
        $tables = $this->getTableList($connection);
        $exported = [];

        foreach ($tables as $tableInfo) {
            $table = $tableInfo['name'];

            // 跳過翻譯表（會被主表自動包含）
            if ($this->isTranslationTable($table)) {
                continue;
            }

            $this->exportToSchemaFile($table, $connection);
            $exported[] = $table;
        }

        return $exported;
    }

    /**
     * 取得欄位資訊
     */
    protected function getColumns(string $database, string $table, string $connection): array
    {
        return DB::connection($connection)->select("
            SELECT
                COLUMN_NAME,
                COLUMN_TYPE,
                DATA_TYPE,
                CHARACTER_MAXIMUM_LENGTH,
                NUMERIC_PRECISION,
                NUMERIC_SCALE,
                IS_NULLABLE,
                COLUMN_DEFAULT,
                COLUMN_COMMENT,
                EXTRA,
                COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$database, $table]);
    }

    /**
     * 取得索引資訊
     */
    protected function getIndexes(string $database, string $table, string $connection): array
    {
        return DB::connection($connection)->select("
            SELECT
                INDEX_NAME,
                COLUMN_NAME,
                NON_UNIQUE,
                SEQ_IN_INDEX
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY INDEX_NAME, SEQ_IN_INDEX
        ", [$database, $table]);
    }

    /**
     * 取得外鍵資訊
     */
    protected function getForeignKeys(string $database, string $table, string $connection): array
    {
        return DB::connection($connection)->select("
            SELECT
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table]);
    }

    /**
     * 取得表備註
     */
    protected function getTableComment(string $database, string $table, string $connection): ?string
    {
        $result = DB::connection($connection)->select("
            SELECT TABLE_COMMENT
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [$database, $table]);

        return $result[0]->TABLE_COMMENT ?: null;
    }

    /**
     * 將 INFORMATION_SCHEMA 欄位轉為 meta 陣列
     */
    protected function columnToMeta(object $col, array $indexes, array $foreignKeys): array
    {
        $meta = [
            'type'           => $col->DATA_TYPE,
            'length'         => null,
            'unsigned'       => str_contains($col->COLUMN_TYPE, 'unsigned'),
            'nullable'       => $col->IS_NULLABLE === 'YES',
            'default'        => $col->COLUMN_DEFAULT,
            'has_default'    => $col->COLUMN_DEFAULT !== null,
            'auto_increment' => str_contains($col->EXTRA, 'auto_increment'),
            'primary'        => $col->COLUMN_KEY === 'PRI',
            'index'          => false,
            'unique'         => false,
            'foreign'        => null,
            'comment'        => $col->COLUMN_COMMENT ?: null,
            'after'          => null,
        ];

        // 長度/精度
        $meta['length'] = $this->resolveLength($col);

        // 單欄索引（排除主鍵）
        $singleIndexes = $this->getSingleColumnIndexes($indexes);
        if (isset($singleIndexes[$col->COLUMN_NAME])) {
            $indexInfo = $singleIndexes[$col->COLUMN_NAME];
            if ($indexInfo['unique']) {
                $meta['unique'] = true;
            } else {
                $meta['index'] = true;
            }
        }

        // 外鍵
        foreach ($foreignKeys as $fk) {
            if ($fk->COLUMN_NAME === $col->COLUMN_NAME) {
                $meta['foreign'] = $fk->REFERENCED_TABLE_NAME . '.' . $fk->REFERENCED_COLUMN_NAME;
                break;
            }
        }

        // MariaDB 的 nullable 欄位回報 COLUMN_DEFAULT 為字串 "NULL"，視為無預設值
        if ($meta['nullable'] && $col->COLUMN_DEFAULT === 'NULL') {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        // auto_increment 的預設值不輸出
        if ($meta['auto_increment']) {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        // timestamp 的 CURRENT_TIMESTAMP 預設值不輸出（由 Laravel 處理）
        if ($col->DATA_TYPE === 'timestamp' && $meta['default'] === 'CURRENT_TIMESTAMP') {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        return $meta;
    }

    /**
     * 解析欄位長度/精度
     */
    protected function resolveLength(object $col): ?string
    {
        // varchar, char → 字元長度
        if (in_array($col->DATA_TYPE, ['varchar', 'char', 'varbinary', 'binary'])) {
            return $col->CHARACTER_MAXIMUM_LENGTH ? (string)$col->CHARACTER_MAXIMUM_LENGTH : null;
        }

        // decimal, float, double → 精度,小數位
        if (in_array($col->DATA_TYPE, ['decimal', 'float', 'double'])) {
            if ($col->NUMERIC_PRECISION !== null) {
                return $col->NUMERIC_PRECISION . ',' . ($col->NUMERIC_SCALE ?? 0);
            }
        }

        // enum, set → 從 COLUMN_TYPE 取值
        if (in_array($col->DATA_TYPE, ['enum', 'set'])) {
            // enum('a','b','c') → a,b,c
            if (preg_match("/^(?:enum|set)\((.+)\)$/i", $col->COLUMN_TYPE, $matches)) {
                return str_replace("'", '', $matches[1]);
            }
        }

        return null;
    }

    /**
     * 取得單欄索引（排除主鍵和複合索引）
     *
     * @return array [column_name => ['unique' => bool], ...]
     */
    protected function getSingleColumnIndexes(array $indexes): array
    {
        // 先分組
        $grouped = [];
        foreach ($indexes as $idx) {
            $grouped[$idx->INDEX_NAME][] = $idx;
        }

        $result = [];
        foreach ($grouped as $name => $columns) {
            // 跳過主鍵和複合索引
            if ($name === 'PRIMARY' || count($columns) > 1) {
                continue;
            }
            $col = $columns[0];
            $result[$col->COLUMN_NAME] = [
                'unique' => $col->NON_UNIQUE == 0,
            ];
        }

        return $result;
    }

    /**
     * 取得複合索引（非唯一、非主鍵、多欄）
     */
    protected function getCompositeIndexes(array $indexes): array
    {
        return $this->getCompositeIndexesByType($indexes, false);
    }

    /**
     * 取得複合唯一索引
     */
    protected function getCompositeUniques(array $indexes): array
    {
        return $this->getCompositeIndexesByType($indexes, true);
    }

    /**
     * 取得複合索引（依類型）
     */
    protected function getCompositeIndexesByType(array $indexes, bool $unique): array
    {
        $grouped = [];
        foreach ($indexes as $idx) {
            $grouped[$idx->INDEX_NAME][] = $idx;
        }

        $result = [];
        foreach ($grouped as $name => $columns) {
            if ($name === 'PRIMARY' || count($columns) <= 1) {
                continue;
            }

            $isUnique = $columns[0]->NON_UNIQUE == 0;
            if ($isUnique !== $unique) {
                continue;
            }

            $colNames = array_map(fn($c) => $c->COLUMN_NAME, $columns);
            $result[$name] = $colNames;
        }

        return $result;
    }

    /**
     * 偵測是否有對應的翻譯表
     *
     * 規則：主表 xxx → 翻譯表 xxx_translations 或 xxx_translation
     */
    protected function detectTranslationTable(string $table, ?string $connection = null): ?string
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        // 嘗試 _translations（複數）
        $candidates = [
            $this->buildTranslationTableName($table),
        ];

        foreach ($candidates as $candidate) {
            $exists = DB::connection($connection)->select(
                "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [$database, $candidate]
            );

            if (!empty($exists)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * 建構翻譯表名
     *
     * 規則：
     *   cfg_terms → cfg_term_translations
     *   companies → company_translations
     */
    protected function buildTranslationTableName(string $table): string
    {
        // 去掉結尾的 s/es/ies
        $singular = $this->singularize($table);
        return $singular . '_translations';
    }

    /**
     * 簡易單數化
     */
    protected function singularize(string $word): string
    {
        // 優先使用 Laravel 的 Str::singular
        if (class_exists(\Illuminate\Support\Str::class)) {
            return \Illuminate\Support\Str::singular($word);
        }

        return $word;
    }

    /**
     * 判斷是否為翻譯表
     */
    protected function isTranslationTable(string $table): bool
    {
        return str_ends_with($table, '_translations');
    }

    /**
     * 從翻譯表提取翻譯欄位
     *
     * 排除 id, *_id, locale 等結構欄位，只保留翻譯內容欄位
     */
    protected function extractTranslationColumns(string $translationTable, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $columns = $this->getColumns($database, $translationTable, $connection);
        $result = [];

        // 結構欄位（排除）
        $skipPatterns = ['id', 'locale', 'created_at', 'updated_at'];

        foreach ($columns as $col) {
            $name = $col->COLUMN_NAME;

            // 跳過結構欄位
            if (in_array($name, $skipPatterns) || str_ends_with($name, '_id')) {
                continue;
            }

            $meta = [
                'type'        => $col->DATA_TYPE,
                'length'      => $this->resolveLength($col),
                'nullable'    => $col->IS_NULLABLE === 'YES',
                'has_default' => false,
                'unsigned'    => false,
                'auto_increment' => false,
                'index'       => false,
                'unique'      => false,
                'foreign'     => null,
                'comment'     => $col->COLUMN_COMMENT ?: null,
                'default'     => null,
                'after'       => null,
            ];

            $result[$name] = $this->parser->buildColumnDefinition($meta);
        }

        return $result;
    }
}
