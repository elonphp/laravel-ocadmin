<?php

namespace App\Traits;

use App\Models\System\Database\MetaKey;
use Illuminate\Support\Facades\Schema;

trait HasMetas
{
    /**
     * 快取已載入的 metas（key => value）
     */
    protected array $metasCache = [];

    /**
     * 待儲存的 metas（key => value）
     */
    protected array $pendingMetas = [];

    /**
     * 是否已載入 metas
     */
    protected bool $metasLoaded = false;

    /**
     * 本表欄位快取
     */
    protected static array $tableColumnsCache = [];

    /**
     * Boot trait - 自動預載 metas
     */
    public static function bootHasMetas(): void
    {
        // 從資料庫取出後自動載入 metas
        static::retrieved(function ($model) {
            $model->loadMetasToCache();
        });

        // 儲存後同步 pending metas
        static::saved(function ($model) {
            $model->savePendingMetas();
        });
    }

    /**
     * 初始化 trait
     */
    public function initializeHasMetas(): void
    {
        $this->metasCache = [];
        $this->pendingMetas = [];
        $this->metasLoaded = false;
    }

    /**
     * 載入所有 metas 到快取
     */
    public function loadMetasToCache(): void
    {
        if ($this->metasLoaded || !$this->exists) {
            return;
        }

        $metaTable = $this->metas()->getRelated()->getTable();

        $this->metasCache = $this->metas()
            ->join('meta_keys', 'meta_keys.id', '=', "{$metaTable}.key_id")
            ->pluck("{$metaTable}.value", 'meta_keys.name')
            ->map(fn($value) => $this->castMetaValue($value))
            ->toArray();

        $this->metasLoaded = true;
    }

    /**
     * 覆寫 getAttribute - 支援動態 meta 屬性
     */
    public function getAttribute($key)
    {
        // 先用原生 Eloquent 邏輯取值
        $value = parent::getAttribute($key);

        // 如果本表有此欄位，直接返回
        if ($this->isTableColumn($key)) {
            return $value;
        }

        // 檢查 pending metas
        if (array_key_exists($key, $this->pendingMetas)) {
            return $this->pendingMetas[$key];
        }

        // 檢查 metas cache
        if (array_key_exists($key, $this->metasCache)) {
            return $this->metasCache[$key];
        }

        return $value;
    }

    /**
     * 覆寫 setAttribute - 支援動態 meta 屬性
     */
    public function setAttribute($key, $value)
    {
        // 如果是本表欄位，用原生邏輯
        if ($this->isTableColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        // 檢查是否為有效的 meta key（該表可用的 key）
        $validKeys = MetaKey::getForTable($this->getTable())->pluck('name')->toArray();

        if (in_array($key, $validKeys)) {
            $this->pendingMetas[$key] = $value;
            return $this;
        }

        // 都不是，用原生邏輯（可能是關聯或其他）
        return parent::setAttribute($key, $value);
    }

    /**
     * 檢查是否為本表欄位
     */
    protected function isTableColumn(string $key): bool
    {
        $table = $this->getTable();

        if (!isset(static::$tableColumnsCache[$table])) {
            static::$tableColumnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($key, static::$tableColumnsCache[$table]);
    }

    /**
     * 儲存 pending metas
     */
    public function savePendingMetas(): void
    {
        if (empty($this->pendingMetas)) {
            return;
        }

        foreach ($this->pendingMetas as $key => $value) {
            $this->setMeta($key, $value);
            $this->metasCache[$key] = $value;
        }

        $this->pendingMetas = [];
    }

    /**
     * 取得單一 meta 值
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        // 先檢查 pending
        if (array_key_exists($key, $this->pendingMetas)) {
            return $this->pendingMetas[$key];
        }

        // 再檢查 cache
        if (array_key_exists($key, $this->metasCache)) {
            return $this->metasCache[$key];
        }

        // 最後查資料庫
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $default;
        }

        $meta = $this->metas()->where('key_id', $keyId)->first();
        $value = $meta ? $this->castMetaValue($meta->value) : $default;

        // 存入 cache
        $this->metasCache[$key] = $value;

        return $value;
    }

    /**
     * 設定單一 meta 值（立即儲存）
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metaKey = MetaKey::firstOrCreate(
            ['name' => $key],
            ['table_name' => $this->getTable()]
        );

        MetaKey::clearCache();

        $this->metas()->updateOrCreate(
            ['key_id' => $metaKey->id],
            ['value' => $this->serializeMetaValue($value)]
        );

        $this->metasCache[$key] = $value;
    }

    /**
     * 批次設定多個 meta
     */
    public function setMetas(array $metas): void
    {
        foreach ($metas as $key => $value) {
            if ($value !== null) {
                $this->setMeta($key, $value);
            }
        }
    }

    /**
     * 刪除 meta
     */
    public function deleteMeta(string $key): bool
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        unset($this->metasCache[$key]);
        unset($this->pendingMetas[$key]);

        return $this->metas()->where('key_id', $keyId)->delete() > 0;
    }

    /**
     * 取得所有 meta（key-value 陣列）
     */
    public function getAllMetas(): array
    {
        $this->loadMetasToCache();

        return array_merge($this->metasCache, $this->pendingMetas);
    }

    /**
     * 檢查 meta 是否存在
     */
    public function hasMeta(string $key): bool
    {
        if (array_key_exists($key, $this->pendingMetas)) {
            return true;
        }

        if (array_key_exists($key, $this->metasCache)) {
            return true;
        }

        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        return $this->metas()->where('key_id', $keyId)->exists();
    }

    /**
     * 序列化值（陣列/物件轉 JSON）
     */
    protected function serializeMetaValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * 反序列化值（嘗試 JSON 解碼）
     */
    protected function castMetaValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    /**
     * Scope: 依 meta 值篩選
     */
    public function scopeWhereMeta($query, string $key, mixed $value)
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('metas', function ($q) use ($keyId, $value) {
            $q->where('key_id', $keyId)->where('value', $value);
        });
    }

    /**
     * 重新載入 metas
     */
    public function reloadMetas(): void
    {
        $this->metasLoaded = false;
        $this->metasCache = [];
        $this->loadMetasToCache();
    }
}
