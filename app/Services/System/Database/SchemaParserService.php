<?php

namespace App\Services\System\Database;

/**
 * Schema 定義檔語法解析器
 *
 * 負責解析 database/schema/tables/*.php 中的欄位定義語法，
 * 以及從結構化陣列反向建構定義字串。
 *
 * 語法範例：
 *   'varchar:100|nullable|index|comment:訂單編號'
 *   'bigint|unsigned|auto_increment'
 *   'decimal:13,4|default:0'
 */
class SchemaParserService
{
    /**
     * 支援的欄位類型
     */
    protected array $supportedTypes = [
        // 整數
        'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
        // 浮點
        'decimal', 'float', 'double',
        // 字串
        'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext',
        // 日期
        'date', 'time', 'datetime', 'timestamp', 'year',
        // 其它
        'json', 'boolean', 'enum', 'set', 'binary', 'varbinary',
        'tinyblob', 'blob', 'mediumblob', 'longblob',
    ];

    /**
     * 解析欄位定義字串為結構化陣列
     *
     * @param string $definition 如 'varchar:100|nullable|index|comment:訂單編號'
     * @return array 結構化欄位資訊
     */
    public function parseColumnDefinition(string $definition): array
    {
        $parts = explode('|', $definition);
        $result = [
            'type'           => null,
            'length'         => null,
            'unsigned'       => false,
            'nullable'       => false,
            'default'        => null,
            'has_default'    => false,
            'auto_increment' => false,
            'primary'        => false,
            'index'          => false,
            'unique'         => false,
            'foreign'        => null,
            'comment'        => null,
            'after'          => null,
        ];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            // 帶參數的修飾符（key:value）
            if (str_contains($part, ':')) {
                [$key, $value] = explode(':', $part, 2);
                $key = trim($key);
                $value = trim($value);

                // 判斷是否為類型定義（type:length）
                if (in_array($key, $this->supportedTypes)) {
                    $result['type'] = $key;
                    $result['length'] = $value;
                } else {
                    // 修飾符
                    match ($key) {
                        'default' => (function () use (&$result, $value) {
                            $result['default'] = $value;
                            $result['has_default'] = true;
                        })(),
                        'foreign' => $result['foreign'] = $value,
                        'comment' => $result['comment'] = $value,
                        'after'   => $result['after'] = $value,
                        default   => null,
                    };
                }
            } else {
                // 無參數：類型或布林修飾符
                if (in_array($part, $this->supportedTypes)) {
                    $result['type'] = $part;
                } else {
                    match ($part) {
                        'unsigned'       => $result['unsigned'] = true,
                        'nullable'       => $result['nullable'] = true,
                        'auto_increment' => $result['auto_increment'] = true,
                        'primary'        => $result['primary'] = true,
                        'index'          => $result['index'] = true,
                        'unique'         => $result['unique'] = true,
                        default          => null,
                    };
                }
            }
        }

        return $result;
    }

    /**
     * 從結構化陣列建構欄位定義字串
     *
     * @param array $meta 結構化欄位資訊（parseColumnDefinition 的回傳格式）
     * @return string 如 'varchar:100|nullable|index'
     */
    public function buildColumnDefinition(array $meta): string
    {
        $parts = [];

        // 類型（含長度）
        if (!empty($meta['type'])) {
            $parts[] = !empty($meta['length'])
                ? $meta['type'] . ':' . $meta['length']
                : $meta['type'];
        }

        // 修飾符（按慣例順序）
        if (!empty($meta['unsigned'])) {
            $parts[] = 'unsigned';
        }
        if (!empty($meta['auto_increment'])) {
            $parts[] = 'auto_increment';
        }
        if (!empty($meta['primary'])) {
            $parts[] = 'primary';
        }
        if (!empty($meta['nullable'])) {
            $parts[] = 'nullable';
        }
        if ($meta['has_default'] ?? false) {
            $parts[] = 'default:' . ($meta['default'] ?? '');
        }
        if (!empty($meta['unique'])) {
            $parts[] = 'unique';
        }
        if (!empty($meta['index'])) {
            $parts[] = 'index';
        }
        if (!empty($meta['foreign'])) {
            $parts[] = 'foreign:' . $meta['foreign'];
        }
        if (!empty($meta['comment'])) {
            $parts[] = 'comment:' . $meta['comment'];
        }
        if (!empty($meta['after'])) {
            $parts[] = 'after:' . $meta['after'];
        }

        return implode('|', $parts);
    }

    /**
     * 讀取 schema 定義檔
     *
     * @param string $tableName 表名（如 'sal_orders'）
     * @return array|null 定義陣列，檔案不存在時回傳 null
     */
    public function loadSchemaFile(string $tableName): ?array
    {
        $path = $this->getSchemaFilePath($tableName);

        if (!file_exists($path)) {
            return null;
        }

        return require $path;
    }

    /**
     * 寫入 schema 定義檔
     *
     * @param string $tableName 表名
     * @param array $schema 定義陣列
     */
    public function saveSchemaFile(string $tableName, array $schema): void
    {
        $path = $this->getSchemaFilePath($tableName);
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = "<?php\n\nreturn " . $this->arrayToPhpString($schema) . ";\n";

        file_put_contents($path, $content);
    }

    /**
     * 取得所有 schema 定義檔的表名列表
     *
     * @return array 表名陣列
     */
    public function getSchemaTableNames(): array
    {
        $dir = $this->getSchemaDirectory();

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.php');
        $tables = [];

        foreach ($files as $file) {
            $tables[] = pathinfo($file, PATHINFO_FILENAME);
        }

        sort($tables);

        return $tables;
    }

    /**
     * 取得 schema 目錄路徑
     */
    public function getSchemaDirectory(): string
    {
        return database_path('schema/tables');
    }

    /**
     * 取得 schema 檔案路徑
     */
    public function getSchemaFilePath(string $tableName): string
    {
        return $this->getSchemaDirectory() . '/' . $tableName . '.php';
    }

    /**
     * 將陣列轉為格式化的 PHP 字串
     */
    protected function arrayToPhpString(array $array, int $indent = 0): string
    {
        $pad = str_repeat('    ', $indent);
        $padInner = str_repeat('    ', $indent + 1);
        $lines = ["["];

        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? '' : "'" . addslashes($key) . "' => ";

            if (is_array($value)) {
                $lines[] = $padInner . $keyStr . $this->arrayToPhpString($value, $indent + 1) . ',';
            } elseif (is_null($value)) {
                $lines[] = $padInner . $keyStr . 'null,';
            } elseif (is_bool($value)) {
                $lines[] = $padInner . $keyStr . ($value ? 'true' : 'false') . ',';
            } elseif (is_int($value) || is_float($value)) {
                $lines[] = $padInner . $keyStr . $value . ',';
            } else {
                $lines[] = $padInner . $keyStr . "'" . addslashes((string)$value) . "',";
            }
        }

        $lines[] = $pad . ']';

        return implode("\n", $lines);
    }
}
