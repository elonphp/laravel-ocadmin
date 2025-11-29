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
        'description',
    ];

    /**
     * 填充預設值（表單未傳的欄位給予預設值）
     */
    public static function withDefaults(array $data): array
    {
        return array_merge([
            'table_name' => null,
            'description' => null,
        ], $data);
    }

    /**
     * 快取所有 keys（避免重複查詢）
     */
    public static function getCached(): Collection
    {
        return cache()->remember('meta_keys:all', 3600, function () {
            return self::all();
        });
    }

    /**
     * 依名稱取得 ID
     */
    public static function getId(string $name): ?int
    {
        return self::getCached()->firstWhere('name', $name)?->id;
    }

    /**
     * 依 ID 取得名稱
     */
    public static function getName(int $id): ?string
    {
        return self::getCached()->firstWhere('id', $id)?->name;
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
}
