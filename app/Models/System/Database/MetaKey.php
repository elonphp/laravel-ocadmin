<?php

namespace App\Models\System\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MetaKey extends Model
{
    protected $table = 'meta_keys';

    /**
     * 使用 smallIncrements 主鍵
     */
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'table_name',
        'is_translation',
        'data_type',
        'precision',
        'description',
    ];

    protected $casts = [
        'is_translation' => 'boolean',
    ];

    /**
     * 可用的資料類型
     */
    public const DATA_TYPES = [
        // 字串
        'char'       => 'CHAR',
        'varchar'    => 'VARCHAR',
        'tinytext'   => 'TINYTEXT',
        'text'       => 'TEXT',
        'mediumtext' => 'MEDIUMTEXT',
        'longtext'   => 'LONGTEXT',
        // 整數
        'tinyint'    => 'TINYINT',
        'smallint'   => 'SMALLINT',
        'mediumint'  => 'MEDIUMINT',
        'int'        => 'INT',
        'bigint'     => 'BIGINT',
        // 浮點數
        'decimal'    => 'DECIMAL',
        'float'      => 'FLOAT',
        'double'     => 'DOUBLE',
        // 日期時間
        'date'       => 'DATE',
        'time'       => 'TIME',
        'datetime'   => 'DATETIME',
        'timestamp'  => 'TIMESTAMP',
        'year'       => 'YEAR',
        // 二進位
        'binary'     => 'BINARY',
        'varbinary'  => 'VARBINARY',
        'tinyblob'   => 'TINYBLOB',
        'blob'       => 'BLOB',
        'mediumblob' => 'MEDIUMBLOB',
        'longblob'   => 'LONGBLOB',
        // 其他
        'json'       => 'JSON',
        'enum'       => 'ENUM',
        'set'        => 'SET',
    ];

    /**
     * 記憶體快取（單次請求內共用）
     */
    protected static ?Collection $cachedKeys = null;

    /**
     * 填充預設值（表單未傳的欄位給予預設值）
     */
    public static function withDefaults(array $data): array
    {
        return array_merge([
            'table_name' => null,
            'is_translation' => false,
            'data_type' => 'varchar',
            'precision' => null,
            'description' => null,
        ], $data);
    }

    /**
     * 取得快取的 meta_keys（單次請求內共用）
     */
    public static function getCached(): Collection
    {
        if (self::$cachedKeys === null) {
            self::$cachedKeys = cache()->remember('meta_keys:all', 3600, function () {
                return self::all()->keyBy('name');
            });
        }
        return self::$cachedKeys;
    }

    /**
     * 檢查欄位是否為 meta 欄位
     */
    public static function isMeta(string $field, ?string $tableName = null): bool
    {
        $key = self::getCached()->get($field);
        if (!$key) {
            return false;
        }
        // 如果 table_name 是 null，則為共用欄位
        // 如果有指定 table_name，則必須匹配
        return $key->table_name === null || $key->table_name === $tableName;
    }

    /**
     * 檢查是否為翻譯欄位
     */
    public static function isTranslation(string $field): bool
    {
        return self::getCached()->get($field)?->is_translation ?? false;
    }

    /**
     * 依名稱取得 ID (meta_key_id)
     */
    public static function getId(string $name): ?int
    {
        return self::getCached()->get($name)?->id;
    }

    /**
     * 依 ID 取得名稱
     */
    public static function getName(int $id): ?string
    {
        return self::getCached()->firstWhere('id', $id)?->name;
    }

    /**
     * 取得欄位的完整資訊
     */
    public static function getByName(string $name): ?self
    {
        return self::getCached()->get($name);
    }

    /**
     * 取得特定表的可用 keys（共用 + 專屬）
     */
    public static function getForTable(string $tableName): Collection
    {
        return self::getCached()->filter(function ($key) use ($tableName) {
            return $key->table_name === null || $key->table_name === $tableName;
        });
    }

    /**
     * 清除快取
     */
    public static function clearCache(): void
    {
        self::$cachedKeys = null;
        cache()->forget('meta_keys:all');
    }

    /**
     * 取得所有不同的 table_name（用於下拉選單）
     */
    public static function getDistinctTableNames(): Collection
    {
        return self::query()
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name')
            ->sort()
            ->values();
    }

    /**
     * Scope: 共用欄位（table_name 為 null）
     */
    public function scopeShared($query)
    {
        return $query->whereNull('table_name');
    }

    /**
     * Scope: 特定表專屬
     */
    public function scopeForTable($query, string $tableName)
    {
        return $query->where(function ($q) use ($tableName) {
            $q->whereNull('table_name')
              ->orWhere('table_name', $tableName);
        });
    }

    /**
     * Scope: 翻譯欄位
     */
    public function scopeTranslations($query)
    {
        return $query->where('is_translation', true);
    }

    /**
     * Scope: 非翻譯欄位
     */
    public function scopeNonTranslations($query)
    {
        return $query->where('is_translation', false);
    }

    /**
     * Boot: 在儲存後清除快取
     */
    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
