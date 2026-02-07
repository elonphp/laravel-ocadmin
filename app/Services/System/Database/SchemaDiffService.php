<?php

namespace App\Services\System\Database;

use Illuminate\Support\Facades\DB;

/**
 * Schema 差異比對服務
 *
 * 比對 database/schema/tables/*.php 定義與實際資料庫結構的差異，
 * 產生 ALTER TABLE SQL 並可選擇執行。
 */
class SchemaDiffService
{
    protected SchemaParserService $parser;
    protected SchemaExportService $exporter;

    public function __construct(SchemaParserService $parser, SchemaExportService $exporter)
    {
        $this->parser = $parser;
        $this->exporter = $exporter;
    }

    /**
     * 比對 schema 定義與資料庫的差異
     *
     * @return array [
     *     'status' => 'synced' | 'diff' | 'schema_only' | 'db_only',
     *     'changes' => [
     *         ['action' => 'add_column', 'column' => '...', 'definition' => '...'],
     *         ['action' => 'modify_column', 'column' => '...', 'from' => '...', 'to' => '...'],
     *         ['action' => 'drop_column', 'column' => '...'],
     *         ...
     *     ]
     * ]
     */
    public function diff(string $table, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $schema = $this->parser->loadSchemaFile($table);
        $dbExists = $this->tableExists($database, $table, $connection);

        // Schema 檔存在但 DB 無此表
        if ($schema !== null && !$dbExists) {
            return [
                'status'  => 'schema_only',
                'changes' => [['action' => 'create_table']],
            ];
        }

        // DB 有此表但無 schema 檔
        if ($schema === null && $dbExists) {
            return [
                'status'  => 'db_only',
                'changes' => [],
            ];
        }

        // 兩邊都沒有
        if ($schema === null && !$dbExists) {
            return ['status' => 'synced', 'changes' => []];
        }

        // 兩邊都有，比對差異
        $changes = [];

        // 取得 DB 現有結構
        $dbStructure = $this->exporter->getTableStructure($table, $connection);
        $dbColumns = $this->indexColumnsByName($dbStructure['columns']);
        $schemaColumns = $schema['columns'] ?? [];

        // 比對欄位
        $prevColumn = null;
        foreach ($schemaColumns as $colName => $definition) {
            $schemaMeta = $this->parser->parseColumnDefinition($definition);

            if (!isset($dbColumns[$colName])) {
                // 新增欄位
                $changes[] = [
                    'action'     => 'add_column',
                    'column'     => $colName,
                    'definition' => $definition,
                    'after'      => $prevColumn,
                ];
            } else {
                // 比對差異
                $dbCol = $dbColumns[$colName];
                $dbMeta = $this->dbColumnToComparableMeta($dbCol, $dbStructure['indexes'], $dbStructure['foreign_keys']);

                $columnDiffs = $this->compareColumnMeta($schemaMeta, $dbMeta);
                if (!empty($columnDiffs)) {
                    $changes[] = [
                        'action'     => 'modify_column',
                        'column'     => $colName,
                        'definition' => $definition,
                        'diffs'      => $columnDiffs,
                    ];
                }
            }

            $prevColumn = $colName;
        }

        // 多餘欄位（DB 有但 schema 沒定義）
        foreach ($dbColumns as $colName => $dbCol) {
            if (!isset($schemaColumns[$colName])) {
                $changes[] = [
                    'action' => 'extra_column',
                    'column' => $colName,
                ];
            }
        }

        // 比對欄位順序（也會為已有的 modify_column 加入 after 資訊）
        $this->applyColumnOrderDiff($schemaColumns, $dbColumns, $changes);

        // 翻譯表差異
        $translationChanges = $this->diffTranslations($table, $schema, $connection);
        $changes = array_merge($changes, $translationChanges);

        return [
            'status'  => empty($changes) ? 'synced' : 'diff',
            'changes' => $changes,
        ];
    }

    /**
     * 產生 ALTER TABLE SQL
     *
     * @return array SQL 語句陣列
     */
    public function generateSql(string $table, ?string $connection = null, bool $dropColumns = false): array
    {
        $diff = $this->diff($table, $connection);
        $sqls = [];

        if ($diff['status'] === 'schema_only') {
            $sqls = array_merge($sqls, $this->generateCreateTableSql($table));
            return $sqls;
        }

        foreach ($diff['changes'] as $change) {
            match ($change['action']) {
                'add_column' => $sqls[] = $this->generateAddColumnSql($table, $change),
                'modify_column' => $sqls[] = $this->generateModifyColumnSql($table, $change),
                'reorder_column' => $sqls[] = $this->generateReorderColumnSql($table, $change),
                'extra_column' => $dropColumns
                    ? $sqls[] = "ALTER TABLE `{$table}` DROP COLUMN `{$change['column']}`"
                    : null,
                'create_translation_table' => $sqls[] = $change['sql'],
                'add_translation_column' => $sqls[] = $change['sql'],
                'modify_translation_column' => $sqls[] = $change['sql'],
                default => null,
            };
        }

        return $sqls;
    }

    /**
     * 執行同步
     *
     * @return array ['executed' => SQL[], 'changes' => changes[]]
     */
    public function apply(string $table, ?string $connection = null, bool $dropColumns = false): array
    {
        $connection = $connection ?: config('database.default');
        $sqls = $this->generateSql($table, $connection, $dropColumns);

        foreach ($sqls as $sql) {
            DB::connection($connection)->statement($sql);
        }

        return [
            'executed' => $sqls,
            'changes'  => $this->diff($table, $connection),
        ];
    }

    /**
     * 取得所有表的同步狀態總覽
     *
     * @return array [['name' => ..., 'status' => ..., 'column_count' => ..., 'translation_count' => ...], ...]
     */
    public function getStatusOverview(?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');

        // 取得 DB 所有表
        $dbTables = collect($this->exporter->getTableList($connection))
            ->filter(fn($t) => !str_ends_with($t['name'], '_translations'))
            ->keyBy('name');

        // 取得 schema 檔所有表
        $schemaTables = $this->parser->getSchemaTableNames();

        // 合併
        $allTables = $dbTables->keys()->merge($schemaTables)->unique()->sort()->values();

        $result = [];
        foreach ($allTables as $table) {
            $schema = $this->parser->loadSchemaFile($table);
            $inDb = $dbTables->has($table);

            $columnCount = 0;
            $translationCount = 0;

            if ($schema) {
                $columnCount = count($schema['columns'] ?? []);
                $translationCount = count($schema['translations'] ?? []);
            } elseif ($inDb) {
                $structure = $this->exporter->getTableStructure($table, $connection);
                $columnCount = count($structure['columns']);
            }

            $diff = $this->diff($table, $connection);

            $result[] = [
                'name'              => $table,
                'comment'           => $schema['comment'] ?? ($dbTables[$table]['comment'] ?? null),
                'status'            => $diff['status'],
                'column_count'      => $columnCount,
                'translation_count' => $translationCount,
                'change_count'      => count($diff['changes']),
            ];
        }

        return $result;
    }

    /**
     * 判斷表是否存在
     */
    protected function tableExists(string $database, string $table, string $connection): bool
    {
        $result = DB::connection($connection)->select(
            "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$database, $table]
        );

        return !empty($result);
    }

    /**
     * 將 DB 欄位陣列以名稱為 key
     */
    protected function indexColumnsByName(array $columns): array
    {
        $result = [];
        foreach ($columns as $col) {
            $result[$col->COLUMN_NAME] = $col;
        }
        return $result;
    }

    /**
     * 將 DB 欄位轉為可比較的 meta（對齊 parseColumnDefinition 的格式）
     */
    protected function dbColumnToComparableMeta(object $col, array $indexes, array $foreignKeys): array
    {
        $meta = [
            'type'           => $col->DATA_TYPE,
            'length'         => $this->resolveDbLength($col),
            'unsigned'       => str_contains($col->COLUMN_TYPE, 'unsigned'),
            'nullable'       => $col->IS_NULLABLE === 'YES',
            'default'        => $col->COLUMN_DEFAULT,
            'has_default'    => $col->COLUMN_DEFAULT !== null,
            'auto_increment' => str_contains($col->EXTRA, 'auto_increment'),
            'primary'        => $col->COLUMN_KEY === 'PRI',
        ];

        // MariaDB 的 nullable 欄位回報 COLUMN_DEFAULT 為字串 "NULL"，視為無預設值
        if ($meta['nullable'] && $col->COLUMN_DEFAULT === 'NULL') {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        // auto_increment 不比較 default
        if ($meta['auto_increment']) {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        if ($col->DATA_TYPE === 'timestamp' && $meta['default'] === 'CURRENT_TIMESTAMP') {
            $meta['has_default'] = false;
            $meta['default'] = null;
        }

        return $meta;
    }

    /**
     * 解析 DB 欄位長度
     */
    protected function resolveDbLength(object $col): ?string
    {
        if (in_array($col->DATA_TYPE, ['varchar', 'char', 'varbinary', 'binary'])) {
            return $col->CHARACTER_MAXIMUM_LENGTH ? (string)$col->CHARACTER_MAXIMUM_LENGTH : null;
        }

        if (in_array($col->DATA_TYPE, ['decimal', 'float', 'double'])) {
            if ($col->NUMERIC_PRECISION !== null) {
                return $col->NUMERIC_PRECISION . ',' . ($col->NUMERIC_SCALE ?? 0);
            }
        }

        if (in_array($col->DATA_TYPE, ['enum', 'set'])) {
            if (preg_match("/^(?:enum|set)\((.+)\)$/i", $col->COLUMN_TYPE, $matches)) {
                return str_replace("'", '', $matches[1]);
            }
        }

        return null;
    }

    /**
     * 比較兩個 meta 的差異
     *
     * @return array 差異項目 ['type changed', 'length changed', ...]
     */
    protected function compareColumnMeta(array $schema, array $db): array
    {
        $diffs = [];

        if ($schema['type'] !== $db['type']) {
            // boolean → tinyint 視為相同
            if (!($schema['type'] === 'boolean' && $db['type'] === 'tinyint') &&
                !($schema['type'] === 'tinyint' && $db['type'] === 'boolean')) {
                $diffs[] = "type: {$db['type']} → {$schema['type']}";
            }
        }

        if ($schema['length'] !== $db['length'] && $schema['length'] !== null) {
            $diffs[] = "length: {$db['length']} → {$schema['length']}";
        }

        if ($schema['unsigned'] !== $db['unsigned']) {
            $diffs[] = $schema['unsigned'] ? 'add unsigned' : 'remove unsigned';
        }

        if ($schema['nullable'] !== $db['nullable']) {
            $diffs[] = $schema['nullable'] ? 'add nullable' : 'remove nullable';
        }

        // 比較預設值
        if ($schema['has_default'] !== $db['has_default'] ||
            ($schema['has_default'] && (string)$schema['default'] !== (string)$db['default'])) {
            $fromDefault = $db['has_default'] ? "'{$db['default']}'" : 'none';
            $toDefault = $schema['has_default'] ? "'{$schema['default']}'" : 'none';
            $diffs[] = "default: {$fromDefault} → {$toDefault}";
        }

        return $diffs;
    }

    /**
     * 比對欄位順序差異，直接修改 $changes 陣列
     *
     * 若欄位已有 modify_column，則加入 after 資訊；
     * 否則新增 reorder_column 變更。
     */
    protected function applyColumnOrderDiff(array $schemaColumns, array $dbColumns, array &$changes): void
    {
        // 取得兩邊都有的欄位，按 schema 定義順序
        $schemaOrder = [];
        foreach (array_keys($schemaColumns) as $colName) {
            if (isset($dbColumns[$colName])) {
                $schemaOrder[] = $colName;
            }
        }

        // DB 側的順序（已按 ORDINAL_POSITION 排列），只取共有欄位
        $dbOrder = [];
        foreach (array_keys($dbColumns) as $colName) {
            if (isset($schemaColumns[$colName])) {
                $dbOrder[] = $colName;
            }
        }

        // 順序相同則無需調整
        if ($schemaOrder === $dbOrder) {
            return;
        }

        // 建立 modify_column 索引（column => changes 陣列 index）
        $modifyIndex = [];
        foreach ($changes as $i => $change) {
            if ($change['action'] === 'modify_column') {
                $modifyIndex[$change['column']] = $i;
            }
        }

        // 逐一比對，找出需要移動的欄位
        $prevColumn = null;

        foreach ($schemaOrder as $colName) {
            $expectedPrev = $prevColumn;

            // 找出此欄位在 DB 順序中的前一欄
            $dbIdx = array_search($colName, $dbOrder);
            $actualPrev = $dbIdx > 0 ? $dbOrder[$dbIdx - 1] : null;

            if ($expectedPrev !== $actualPrev) {
                $afterValue = $prevColumn; // null 表示 FIRST

                if (isset($modifyIndex[$colName])) {
                    // 已有屬性修改，併入 after 資訊
                    $idx = $modifyIndex[$colName];
                    $changes[$idx]['after'] = $afterValue;
                    $changes[$idx]['diffs'][] = 'position changed';
                } else {
                    // 純順序調整
                    $changes[] = [
                        'action'     => 'reorder_column',
                        'column'     => $colName,
                        'definition' => $schemaColumns[$colName],
                        'after'      => $afterValue,
                        'diffs'      => ['position changed'],
                    ];
                }
            }

            $prevColumn = $colName;
        }
    }

    /**
     * 產生 MODIFY COLUMN ... AFTER SQL（僅調整順序）
     */
    protected function generateReorderColumnSql(string $table, array $change): string
    {
        $meta = $this->parser->parseColumnDefinition($change['definition']);
        $columnSql = $this->buildColumnSql($change['column'], $meta);

        $after = $change['after'] ? " AFTER `{$change['after']}`" : ' FIRST';

        return "ALTER TABLE `{$table}` MODIFY COLUMN {$columnSql}{$after}";
    }

    /**
     * 產生 CREATE TABLE SQL
     */
    protected function generateCreateTableSql(string $table): array
    {
        $schema = $this->parser->loadSchemaFile($table);
        if (!$schema) {
            return [];
        }

        $columnSqls = [];
        $indexSqls = [];
        $fkSqls = [];
        $primaryKeys = [];

        foreach ($schema['columns'] as $colName => $definition) {
            $meta = $this->parser->parseColumnDefinition($definition);
            $columnSql = $this->buildColumnSql($colName, $meta);
            $columnSqls[] = $columnSql;

            if ($meta['primary']) {
                $primaryKeys[] = $colName;
            }

            if ($meta['index']) {
                $indexSqls[] = "INDEX `idx_{$colName}` (`{$colName}`)";
            }
            if ($meta['unique']) {
                $indexSqls[] = "UNIQUE KEY `uq_{$colName}` (`{$colName}`)";
            }
            if ($meta['foreign']) {
                [$refTable, $refCol] = explode('.', $meta['foreign']);
                $fkSqls[] = "FOREIGN KEY (`{$colName}`) REFERENCES `{$refTable}` (`{$refCol}`) ON DELETE CASCADE";
            }
        }

        if (!empty($primaryKeys)) {
            $pkCols = implode('`, `', $primaryKeys);
            $columnSqls[] = "PRIMARY KEY (`{$pkCols}`)";
        }

        // 複合索引
        foreach ($schema['indexes'] ?? [] as $idxName => $columns) {
            $cols = implode('`, `', $columns);
            $indexSqls[] = "INDEX `{$idxName}` (`{$cols}`)";
        }

        // 複合唯一
        foreach ($schema['unique'] ?? [] as $idxName => $columns) {
            $cols = implode('`, `', $columns);
            $indexSqls[] = "UNIQUE KEY `{$idxName}` (`{$cols}`)";
        }

        $allParts = array_merge($columnSqls, $indexSqls, $fkSqls);
        $body = implode(",\n    ", $allParts);

        $comment = !empty($schema['comment']) ? " COMMENT='" . addslashes($schema['comment']) . "'" : '';
        $sql = "CREATE TABLE `{$table}` (\n    {$body}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci{$comment}";

        $sqls = [$sql];

        // 翻譯表
        if (!empty($schema['translations'])) {
            $sqls[] = $this->generateCreateTranslationTableSql($table, $schema['translations']);
        }

        return $sqls;
    }

    /**
     * 建構單一欄位 SQL 片段
     */
    protected function buildColumnSql(string $colName, array $meta): string
    {
        $type = strtoupper($meta['type']);

        // 長度
        if (!empty($meta['length'])) {
            $type .= "({$meta['length']})";
        }

        // unsigned
        if ($meta['unsigned']) {
            $type .= ' UNSIGNED';
        }

        // nullable
        $null = $meta['nullable'] ? 'NULL' : 'NOT NULL';

        // default
        $default = '';
        if ($meta['has_default']) {
            $val = $meta['default'];
            if (is_numeric($val)) {
                $default = "DEFAULT {$val}";
            } else {
                $default = "DEFAULT '" . addslashes($val) . "'";
            }
        }

        // auto_increment
        $extra = $meta['auto_increment'] ? 'AUTO_INCREMENT' : '';

        // comment
        $comment = !empty($meta['comment']) ? "COMMENT '" . addslashes($meta['comment']) . "'" : '';

        $parts = array_filter(["`{$colName}`", $type, $null, $default, $extra, $comment]);

        return implode(' ', $parts);
    }

    /**
     * 產生 ADD COLUMN SQL
     */
    protected function generateAddColumnSql(string $table, array $change): string
    {
        $meta = $this->parser->parseColumnDefinition($change['definition']);
        $columnSql = $this->buildColumnSql($change['column'], $meta);

        $after = !empty($change['after']) ? " AFTER `{$change['after']}`" : '';

        return "ALTER TABLE `{$table}` ADD COLUMN {$columnSql}{$after}";
    }

    /**
     * 產生 MODIFY COLUMN SQL
     */
    protected function generateModifyColumnSql(string $table, array $change): string
    {
        $meta = $this->parser->parseColumnDefinition($change['definition']);
        $columnSql = $this->buildColumnSql($change['column'], $meta);

        $after = '';
        if (array_key_exists('after', $change)) {
            $after = $change['after'] ? " AFTER `{$change['after']}`" : ' FIRST';
        }

        return "ALTER TABLE `{$table}` MODIFY COLUMN {$columnSql}{$after}";
    }

    /**
     * 產生建立翻譯表 SQL
     */
    protected function generateCreateTranslationTableSql(string $mainTable, array $translations): string
    {
        $singular = \Illuminate\Support\Str::singular($mainTable);
        $transTable = $singular . '_translations';
        $fkColumn = $singular . '_id';

        // 如果主表有前綴（如 cfg_terms），外鍵用去前綴的單數
        if (str_contains($singular, '_')) {
            $parts = explode('_', $singular);
            // 去掉前綴，取最後部分作為外鍵
            $fkColumn = end($parts) . '_id';
        }

        $columnSqls = [
            "`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT",
            "`{$fkColumn}` BIGINT UNSIGNED NOT NULL",
            "`locale` VARCHAR(10) NOT NULL",
        ];

        foreach ($translations as $colName => $definition) {
            $meta = $this->parser->parseColumnDefinition($definition);
            $columnSqls[] = $this->buildColumnSql($colName, $meta);
        }

        $columnSqls[] = "PRIMARY KEY (`id`)";
        $columnSqls[] = "UNIQUE KEY `uq_{$fkColumn}_locale` (`{$fkColumn}`, `locale`)";
        $columnSqls[] = "FOREIGN KEY (`{$fkColumn}`) REFERENCES `{$mainTable}` (`id`) ON DELETE CASCADE";

        $body = implode(",\n    ", $columnSqls);

        return "CREATE TABLE `{$transTable}` (\n    {$body}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    /**
     * 比對翻譯表差異
     */
    protected function diffTranslations(string $table, ?array $schema, ?string $connection): array
    {
        if (empty($schema['translations'])) {
            return [];
        }

        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $singular = \Illuminate\Support\Str::singular($table);
        $transTable = $singular . '_translations';

        // 翻譯表不存在
        $exists = $this->tableExists($database, $transTable, $connection);
        if (!$exists) {
            return [[
                'action' => 'create_translation_table',
                'table'  => $transTable,
                'sql'    => $this->generateCreateTranslationTableSql($table, $schema['translations']),
            ]];
        }

        // 翻譯表存在，比對欄位
        $changes = [];
        $dbStructure = $this->exporter->getTableStructure($transTable, $connection);
        $dbColumns = $this->indexColumnsByName($dbStructure['columns']);

        foreach ($schema['translations'] as $colName => $definition) {
            $schemaMeta = $this->parser->parseColumnDefinition($definition);

            if (!isset($dbColumns[$colName])) {
                $meta = $this->parser->parseColumnDefinition($definition);
                $columnSql = $this->buildColumnSql($colName, $meta);
                $changes[] = [
                    'action' => 'add_translation_column',
                    'column' => $colName,
                    'table'  => $transTable,
                    'sql'    => "ALTER TABLE `{$transTable}` ADD COLUMN {$columnSql}",
                ];
            } else {
                $dbMeta = $this->dbColumnToComparableMeta($dbColumns[$colName], [], []);
                $columnDiffs = $this->compareColumnMeta($schemaMeta, $dbMeta);
                if (!empty($columnDiffs)) {
                    $meta = $this->parser->parseColumnDefinition($definition);
                    $columnSql = $this->buildColumnSql($colName, $meta);
                    $changes[] = [
                        'action' => 'modify_translation_column',
                        'column' => $colName,
                        'table'  => $transTable,
                        'diffs'  => $columnDiffs,
                        'sql'    => "ALTER TABLE `{$transTable}` MODIFY COLUMN {$columnSql}",
                    ];
                }
            }
        }

        return $changes;
    }
}
