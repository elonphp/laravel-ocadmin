<?php

namespace App\Traits;

use App\Models\System\Database\MetaKey;
use Illuminate\Support\Facades\Schema;

trait HasMetas
{
    /**
     * 快取已載入的 metas
     * 結構：
     * - 無語系 (locale='')：[key => value]
     * - 有語系：[key => [locale => value, ...]]
     */
    protected array $metasCache = [];

    /**
     * 待儲存的 metas
     * 結構：[key => ['locale' => locale, 'value' => value], ...]
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

        $metas = $this->metas()
            ->join('meta_keys', 'meta_keys.id', '=', "{$metaTable}.meta_key_id")
            ->select("{$metaTable}.meta_value", "{$metaTable}.locale", 'meta_keys.name')
            ->get();

        $this->metasCache = [];

        foreach ($metas as $meta) {
            $key = $meta->name;
            $locale = $meta->locale ?? '';
            $value = $this->castMetaValue($meta->meta_value);

            if ($locale === '') {
                // 無語系：直接存值
                $this->metasCache[$key] = $value;
            } else {
                // 有語系：按 locale 分組
                if (!isset($this->metasCache[$key]) || !is_array($this->metasCache[$key])) {
                    $this->metasCache[$key] = [];
                }
                $this->metasCache[$key][$locale] = $value;
            }
        }

        $this->metasLoaded = true;
    }

    /**
     * 覆寫 getAttribute - 支援動態 meta 屬性
     * 魔術方法取值時，無語系直接返回，有語系返回當前語系的值
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
            $pending = $this->pendingMetas[$key];
            if ($pending['locale'] === '') {
                return $pending['value'];
            }
            // 有語系的 pending，檢查是否為當前語系
            if ($pending['locale'] === app()->getLocale()) {
                return $pending['value'];
            }
        }

        // 檢查 metas cache
        if (array_key_exists($key, $this->metasCache)) {
            $cached = $this->metasCache[$key];

            // 無語系：直接是值
            if (!is_array($cached)) {
                return $cached;
            }

            // 有語系：返回當前語系的值（有 fallback）
            $locale = app()->getLocale();
            $defaultLocale = config('localization.default_locale', 'zh_Hant');

            return $cached[$locale]
                ?? $cached[$defaultLocale]
                ?? array_values($cached)[0]
                ?? null;
        }

        return $value;
    }

    /**
     * 覆寫 setAttribute - 支援動態 meta 屬性
     * 魔術方法設值時，預設為無語系 (locale='')
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
            $this->pendingMetas[$key] = [
                'locale' => '',  // 魔術方法預設無語系
                'value' => $value,
            ];
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

        foreach ($this->pendingMetas as $key => $data) {
            $this->setMeta($key, $data['value'], $data['locale']);

            // 更新快取
            if ($data['locale'] === '') {
                $this->metasCache[$key] = $data['value'];
            } else {
                if (!isset($this->metasCache[$key]) || !is_array($this->metasCache[$key])) {
                    $this->metasCache[$key] = [];
                }
                $this->metasCache[$key][$data['locale']] = $data['value'];
            }
        }

        $this->pendingMetas = [];
    }

    /**
     * 取得 meta 值（無語系）
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        // 先檢查 pending（無語系）
        if (array_key_exists($key, $this->pendingMetas) && $this->pendingMetas[$key]['locale'] === '') {
            return $this->pendingMetas[$key]['value'];
        }

        // 再檢查 cache
        if (array_key_exists($key, $this->metasCache)) {
            $cached = $this->metasCache[$key];
            // 只返回無語系的值
            if (!is_array($cached)) {
                return $cached;
            }
        }

        // 最後查資料庫
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $default;
        }

        $meta = $this->metas()->where('meta_key_id', $keyId)->where('locale', '')->first();
        $value = $meta ? $this->castMetaValue($meta->meta_value) : $default;

        // 存入 cache
        if ($value !== $default) {
            $this->metasCache[$key] = $value;
        }

        return $value;
    }

    /**
     * 取得有語系的 meta 值（含 fallback）
     *
     * @param string $key
     * @param string|null $locale 預設為當前語系
     * @param mixed $default
     * @return mixed
     */
    public function getLocalizedMeta(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $defaultLocale = config('localization.default_locale', 'zh_Hant');

        // 先檢查 pending
        if (array_key_exists($key, $this->pendingMetas) && $this->pendingMetas[$key]['locale'] === $locale) {
            return $this->pendingMetas[$key]['value'];
        }

        // 再檢查 cache
        if (array_key_exists($key, $this->metasCache) && is_array($this->metasCache[$key])) {
            $cached = $this->metasCache[$key];
            return $cached[$locale] ?? $cached[$defaultLocale] ?? $default;
        }

        // 最後查資料庫
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $default;
        }

        // 嘗試取得指定語系
        $meta = $this->metas()->where('meta_key_id', $keyId)->where('locale', $locale)->first();

        // Fallback 到預設語系
        if (!$meta && $locale !== $defaultLocale) {
            $meta = $this->metas()->where('meta_key_id', $keyId)->where('locale', $defaultLocale)->first();
        }

        $value = $meta ? $this->castMetaValue($meta->meta_value) : $default;

        // 存入 cache
        if ($meta) {
            if (!isset($this->metasCache[$key]) || !is_array($this->metasCache[$key])) {
                $this->metasCache[$key] = [];
            }
            $this->metasCache[$key][$meta->locale] = $this->castMetaValue($meta->meta_value);
        }

        return $value;
    }

    /**
     * 取得某個 key 的所有語系值
     *
     * @param string $key
     * @return array [locale => value, ...]
     */
    public function getAllLocalesForMeta(string $key): array
    {
        if (array_key_exists($key, $this->metasCache) && is_array($this->metasCache[$key])) {
            return $this->metasCache[$key];
        }

        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return [];
        }

        $metas = $this->metas()
            ->where('meta_key_id', $keyId)
            ->where('locale', '!=', '')
            ->pluck('meta_value', 'locale')
            ->map(fn($v) => $this->castMetaValue($v))
            ->toArray();

        if (!empty($metas)) {
            $this->metasCache[$key] = $metas;
        }

        return $metas;
    }

    /**
     * 設定 meta 值（立即儲存）
     *
     * @param string $key
     * @param mixed $value
     * @param string $locale 空字串表示無語系
     */
    public function setMeta(string $key, mixed $value, string $locale = ''): void
    {
        $metaKey = MetaKey::firstOrCreate(
            ['name' => $key],
            ['table_name' => $this->getTable()]
        );

        MetaKey::clearCache();

        $this->metas()->updateOrCreate(
            ['meta_key_id' => $metaKey->id, 'locale' => $locale],
            ['meta_value' => $this->serializeMetaValue($value)]
        );

        // 更新快取
        if ($locale === '') {
            $this->metasCache[$key] = $value;
        } else {
            if (!isset($this->metasCache[$key]) || !is_array($this->metasCache[$key])) {
                $this->metasCache[$key] = [];
            }
            $this->metasCache[$key][$locale] = $value;
        }
    }

    /**
     * 設定有語系的 meta 值
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $locale 預設為當前語系
     */
    public function setLocalizedMeta(string $key, mixed $value, ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();
        $this->setMeta($key, $value, $locale);
    }

    /**
     * 批次設定多個 meta（無語系）
     */
    public function setMetas(array $metas): void
    {
        foreach ($metas as $key => $value) {
            if ($value !== null) {
                $this->setMeta($key, $value, '');
            }
        }
    }

    /**
     * 批次設定多個有語系的 meta
     *
     * @param array $metas [key => value, ...]
     * @param string|null $locale
     */
    public function setLocalizedMetas(array $metas, ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();
        foreach ($metas as $key => $value) {
            if ($value !== null) {
                $this->setMeta($key, $value, $locale);
            }
        }
    }

    /**
     * 刪除 meta
     *
     * @param string $key
     * @param string|null $locale null = 刪除所有語系，'' = 只刪無語系，其他 = 刪指定語系
     */
    public function deleteMeta(string $key, ?string $locale = null): bool
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        $query = $this->metas()->where('meta_key_id', $keyId);

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        // 更新快取
        if ($locale === null) {
            // 刪除所有
            unset($this->metasCache[$key]);
        } elseif ($locale === '') {
            // 刪除無語系
            if (isset($this->metasCache[$key]) && !is_array($this->metasCache[$key])) {
                unset($this->metasCache[$key]);
            }
        } else {
            // 刪除指定語系
            if (isset($this->metasCache[$key]) && is_array($this->metasCache[$key])) {
                unset($this->metasCache[$key][$locale]);
            }
        }

        unset($this->pendingMetas[$key]);

        return $query->delete() > 0;
    }

    /**
     * 取得所有 meta（key-value 陣列）
     * 無語系：[key => value]
     * 有語系：[key => [locale => value, ...]]
     */
    public function getAllMetas(): array
    {
        $this->loadMetasToCache();

        // 合併 pending metas
        $result = $this->metasCache;

        foreach ($this->pendingMetas as $key => $data) {
            if ($data['locale'] === '') {
                $result[$key] = $data['value'];
            } else {
                if (!isset($result[$key]) || !is_array($result[$key])) {
                    $result[$key] = [];
                }
                $result[$key][$data['locale']] = $data['value'];
            }
        }

        return $result;
    }

    /**
     * 檢查 meta 是否存在
     *
     * @param string $key
     * @param string|null $locale null = 任何語系，'' = 只檢查無語系
     */
    public function hasMeta(string $key, ?string $locale = null): bool
    {
        // 檢查 pending
        if (array_key_exists($key, $this->pendingMetas)) {
            if ($locale === null) {
                return true;
            }
            return $this->pendingMetas[$key]['locale'] === $locale;
        }

        // 檢查 cache
        if (array_key_exists($key, $this->metasCache)) {
            if ($locale === null) {
                return true;
            }
            if ($locale === '' && !is_array($this->metasCache[$key])) {
                return true;
            }
            if ($locale !== '' && is_array($this->metasCache[$key])) {
                return isset($this->metasCache[$key][$locale]);
            }
        }

        // 查資料庫
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        $query = $this->metas()->where('meta_key_id', $keyId);

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        return $query->exists();
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
     * Scope: 依 meta 值篩選（無語系）
     */
    public function scopeWhereMeta($query, string $key, mixed $value)
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('metas', function ($q) use ($keyId, $value) {
            $q->where('meta_key_id', $keyId)
              ->where('locale', '')
              ->where('meta_value', $value);
        });
    }

    /**
     * Scope: 依有語系的 meta 值篩選
     */
    public function scopeWhereLocalizedMeta($query, string $key, mixed $value, ?string $locale = null)
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $query->whereRaw('1 = 0');
        }

        $locale = $locale ?? app()->getLocale();

        return $query->whereHas('metas', function ($q) use ($keyId, $value, $locale) {
            $q->where('meta_key_id', $keyId)
              ->where('locale', $locale)
              ->where('meta_value', $value);
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
