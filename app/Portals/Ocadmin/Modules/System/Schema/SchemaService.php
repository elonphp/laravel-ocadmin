<?php

namespace App\Portals\Ocadmin\Modules\System\Schema;

use Illuminate\Support\Facades\DB;

/**
 * Schema Service（後台 UI 即時變更資料表結構）
 *
 * 職責：
 *  - 讀 DB：列出表、讀取單表結構
 *  - 寫 DB：依據 UI 提交內容產生 ALTER SQL 並執行
 *
 * 不處理 schema 檔、不做差異比對；所見即所得。
 */
class SchemaService
{
    /**
     * 排除的 Laravel 框架表（列表不顯示）
     */
    protected array $excludedTables = [
        'cache', 'cache_locks',
        'failed_jobs', 'job_batches', 'jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
    ];

    /**
     * 取得表清單（排除框架表）
     */
    public function getTableList(?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        // 兩個獨立查詢 + PHP 合併，避免對每張表執行相關子查詢
        $tables = DB::connection($connection)->select("
            SELECT TABLE_NAME, TABLE_COMMENT
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [$database]);

        $counts = DB::connection($connection)->select("
            SELECT TABLE_NAME, COUNT(*) AS cnt
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
            GROUP BY TABLE_NAME
        ", [$database]);

        $countMap = [];
        foreach ($counts as $c) {
            $countMap[$c->TABLE_NAME] = (int) $c->cnt;
        }

        $result = [];
        foreach ($tables as $t) {
            if (in_array($t->TABLE_NAME, $this->excludedTables)) {
                continue;
            }
            $result[] = [
                'name'         => $t->TABLE_NAME,
                'comment'      => $t->TABLE_COMMENT ?: null,
                'column_count' => $countMap[$t->TABLE_NAME] ?? 0,
            ];
        }
        return $result;
    }

    /**
     * 取得單表結構，轉為 UI 友善的 meta 陣列
     */
    public function getTableStructure(string $table, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $commentRow = DB::connection($connection)->selectOne("
            SELECT TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [$database, $table]);
        $comment = $commentRow ? ($commentRow->TABLE_COMMENT ?: null) : null;

        $raw = DB::connection($connection)->select("
            SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE,
                CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE,
                IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT, EXTRA, COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$database, $table]);

        $columns = [];
        foreach ($raw as $c) {
            $default = $c->COLUMN_DEFAULT;
            // MariaDB 的 nullable 欄位會回報字串 "NULL"，視為無預設
            if (($c->IS_NULLABLE === 'YES') && $default === 'NULL') {
                $default = null;
            }

            $columns[] = [
                'name'           => $c->COLUMN_NAME,
                'type'           => $c->DATA_TYPE,
                'length'         => $this->resolveLength($c),
                'unsigned'       => str_contains($c->COLUMN_TYPE, 'unsigned'),
                'nullable'       => $c->IS_NULLABLE === 'YES',
                'default'        => $default,
                'auto_increment' => str_contains($c->EXTRA, 'auto_increment'),
                'primary'        => $c->COLUMN_KEY === 'PRI',
                'comment'        => $c->COLUMN_COMMENT ?: null,
            ];
        }

        return [
            'comment' => $comment,
            'columns' => $columns,
        ];
    }

    /**
     * 依 UI 提交的欄位清單產生 ALTER SQL（不執行）
     *
     * 每筆 $submitted 欄位格式：
     *   original_name: 原名（空 = 新增欄位）
     *   name         : 新名
     *   type         : 類型
     *   length       : 長度（可空）
     *   unsigned     : 0/1
     *   nullable     : 0/1
     *   default      : 預設值字串（空白 = 無 DEFAULT）
     *   auto_increment: 0/1
     *   comment      : 備註（可空）
     *   _delete      : 1 表示刪除（僅對既有欄位生效）
     */
    public function buildAlterSql(string $table, array $submitted, ?string $connection = null): array
    {
        $sqls = [];

        // 載入 DB 目前結構，供比對用
        $current = $this->getTableStructure($table, $connection);
        $currentByName = [];
        foreach ($current['columns'] as $c) {
            $currentByName[$c['name']] = $c;
        }

        foreach ($submitted as $col) {
            $originalName = trim((string) ($col['original_name'] ?? ''));
            $newName      = trim((string) ($col['name'] ?? ''));
            $isDelete     = !empty($col['_delete']);

            // 刪除（僅對既有欄位生效）
            if ($isDelete) {
                if ($originalName === '') {
                    continue;
                }
                $sqls[] = "ALTER TABLE `{$table}` DROP COLUMN `{$originalName}`";
                continue;
            }

            // 跳過完全空白列
            if ($originalName === '' && $newName === '') {
                continue;
            }

            // 新增欄位（原名空 + 新名有值）
            if ($originalName === '') {
                $colSql = $this->buildColumnSql($newName, $col);
                $sqls[] = "ALTER TABLE `{$table}` ADD COLUMN {$colSql}";
                continue;
            }

            // 既有欄位
            if ($newName === '') {
                continue;
            }

            $original = $currentByName[$originalName] ?? null;
            $isRename = $originalName !== $newName;
            $attrChanged = $original ? $this->attributesChanged($original, $col) : true;

            if (!$isRename && !$attrChanged) {
                continue; // 完全沒變 → 不輸出 SQL
            }

            $colSql = $this->buildColumnSql($newName, $col);
            if ($isRename) {
                // 改名（CHANGE 同時處理改名與屬性，即便屬性未變也無害）
                $sqls[] = "ALTER TABLE `{$table}` CHANGE `{$originalName}` {$colSql}";
            } else {
                // 僅屬性變更
                $sqls[] = "ALTER TABLE `{$table}` MODIFY COLUMN {$colSql}";
            }
        }

        return $sqls;
    }

    /**
     * 比對原有 DB 欄位屬性 vs UI 提交值，判斷是否有實質變更
     */
    protected function attributesChanged(array $original, array $submitted): bool
    {
        // type
        if (strtolower((string) $original['type']) !== strtolower((string) ($submitted['type'] ?? ''))) {
            return true;
        }

        // length（null 視為空字串；未指定時以預設長度比對）
        $origType = strtolower((string) ($original['type'] ?? ''));
        $subType  = strtolower((string) ($submitted['type'] ?? ''));
        $origLen  = trim((string) ($original['length'] ?? ''));
        $subLen   = trim((string) ($submitted['length'] ?? ''));
        if ($origLen === '' && isset($this->typeDefaultLength[$origType])) {
            $origLen = $this->typeDefaultLength[$origType];
        }
        if ($subLen === '' && isset($this->typeDefaultLength[$subType])) {
            $subLen = $this->typeDefaultLength[$subType];
        }
        if ($origLen !== $subLen) {
            return true;
        }

        // 布林類
        if ((bool) $original['unsigned']       !== !empty($submitted['unsigned']))       return true;
        if ((bool) $original['nullable']       !== !empty($submitted['nullable']))       return true;
        if ((bool) $original['auto_increment'] !== !empty($submitted['auto_increment'])) return true;

        // default（null 與 '' 視為相同）
        $origDefault = $original['default'] ?? null;
        $subDefault  = $submitted['default'] ?? '';
        $origEmpty = ($origDefault === null || $origDefault === '');
        $subEmpty  = ($subDefault === '' || $subDefault === null);
        if ($origEmpty !== $subEmpty) {
            return true;
        }
        if (!$origEmpty && (string) $origDefault !== (string) $subDefault) {
            return true;
        }

        // comment
        $origComment = (string) ($original['comment'] ?? '');
        $subComment  = (string) ($submitted['comment'] ?? '');
        if ($origComment !== $subComment) {
            return true;
        }

        return false;
    }

    /**
     * 執行 ALTER（transaction 包覆）
     *
     * @return array ['executed' => string[]]
     */
    public function applyAlter(string $table, array $submitted, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $sqls = $this->buildAlterSql($table, $submitted, $connection);

        if (empty($sqls)) {
            return ['executed' => []];
        }

        // 注意：MySQL DDL 會觸發隱含 commit，無法放在 transaction 裡。
        // 逐條執行；若某條失敗，前面已成功的變更已 commit 無法回滾。
        $executed = [];
        foreach ($sqls as $sql) {
            DB::connection($connection)->statement($sql);
            $executed[] = $sql;
        }

        return ['executed' => $executed];
    }

    // ── 以下為內部 helper ─────────────────────────

    protected function resolveLength(object $c): ?string
    {
        if (in_array($c->DATA_TYPE, ['varchar', 'char', 'varbinary', 'binary'])) {
            return $c->CHARACTER_MAXIMUM_LENGTH ? (string) $c->CHARACTER_MAXIMUM_LENGTH : null;
        }
        if (in_array($c->DATA_TYPE, ['decimal', 'float', 'double']) && $c->NUMERIC_PRECISION !== null) {
            return $c->NUMERIC_PRECISION . ',' . ($c->NUMERIC_SCALE ?? 0);
        }
        return null;
    }

    /**
     * 需要長度/精度的型別，未指定時自動補預設
     */
    protected array $typeDefaultLength = [
        'varchar'   => '255',
        'char'      => '1',
        'varbinary' => '255',
        'binary'    => '1',
        'decimal'   => '10,2',
    ];

    /**
     * 組出單欄 DDL 片段（不含 ALTER 外圍）
     */
    protected function buildColumnSql(string $name, array $meta): string
    {
        $rawType = strtolower((string) ($meta['type'] ?? 'varchar'));
        $type = strtoupper($rawType);
        $length = trim((string) ($meta['length'] ?? ''));

        // 需要長度卻未給 → 補預設
        if ($length === '' && isset($this->typeDefaultLength[$rawType])) {
            $length = $this->typeDefaultLength[$rawType];
        }

        if ($length !== '') {
            $type .= "({$length})";
        }
        if (!empty($meta['unsigned'])) {
            $type .= ' UNSIGNED';
        }

        $null = !empty($meta['nullable']) ? 'NULL' : 'NOT NULL';

        // DEFAULT 規則：留空不輸出 DEFAULT 子句
        $default = '';
        $defaultVal = $meta['default'] ?? null;
        if ($defaultVal !== null && $defaultVal !== '') {
            if (is_numeric($defaultVal)) {
                $default = "DEFAULT {$defaultVal}";
            } else {
                $default = "DEFAULT '" . addslashes((string) $defaultVal) . "'";
            }
        }

        $extra = !empty($meta['auto_increment']) ? 'AUTO_INCREMENT' : '';
        $comment = !empty($meta['comment'])
            ? "COMMENT '" . addslashes((string) $meta['comment']) . "'"
            : '';

        $parts = array_filter(["`{$name}`", $type, $null, $default, $extra, $comment]);
        return implode(' ', $parts);
    }
}
