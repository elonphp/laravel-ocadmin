<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * HasZeavTable Trait
 *
 * 為 Model 提供 EAV + ZEAV 快取表功能
 *
 * 使用方式：
 * 1. Model 加入 use HasZeavTable;
 * 2. 設定 $translation_mode = 3;
 * 3. 設定 $translation_keys = ['name', 'description'];
 *
 * 資料表關係：
 * - users (主表)
 * - user_metas (EAV 真實資料)
 * - zeav_users (快取表)
 */
trait HasZeavTable
{
    /**
     * 取得 metas 關聯（EAV 真實資料）
     */
    public function metas(): HasMany
    {
        return $this->hasMany($this->getMetaModelClass(), $this->getMetaForeignKey());
    }

    /**
     * 取得當前語系的 zeav profile
     */
    public function zeavProfile(): HasOne
    {
        return $this->hasOne($this->getZeavModelClass(), $this->getMetaForeignKey())
            ->where('locale', app()->getLocale());
    }

    /**
     * 取得所有語系的 zeav profiles
     */
    public function zeavProfiles(): HasMany
    {
        return $this->hasMany($this->getZeavModelClass(), $this->getMetaForeignKey());
    }

    /**
     * 取得單一 meta 值
     *
     * @param string $key meta key 名稱
     * @param string|null $locale 語系，null 使用當前語系，'' 表示非多語
     * @param mixed $default 預設值
     */
    public function getMeta(string $key, ?string $locale = null, $default = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $metaKeyId = $this->getMetaKeyId($key);

        if (!$metaKeyId) {
            return $default;
        }

        $meta = $this->metas()
            ->where('meta_key_id', $metaKeyId)
            ->where('locale', $locale)
            ->first();

        return $meta?->meta_value ?? $default;
    }

    /**
     * 取得多個 meta 值（當前語系）
     *
     * @param array|null $keys 指定 keys，null 表示全部
     * @param string|null $locale 語系
     */
    public function getMetas(?array $keys = null, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $translationKeys = $this->translation_keys ?? [];

        $query = $this->metas()->where('locale', $locale);

        if ($keys !== null) {
            $metaKeyIds = array_filter(array_map(
                fn($key) => $this->getMetaKeyId($key),
                $keys
            ));
            $query->whereIn('meta_key_id', $metaKeyIds);
        }

        $metas = $query->get();
        $result = [];

        foreach ($metas as $meta) {
            $keyName = $this->getMetaKeyName($meta->meta_key_id);
            if ($keyName) {
                $result[$keyName] = $meta->meta_value;
            }
        }

        return $result;
    }

    /**
     * 設定單一 meta 值
     *
     * @param string $key meta key 名稱
     * @param mixed $value 值
     * @param string|null $locale 語系，null 使用當前語系，'' 表示非多語
     */
    public function setMeta(string $key, $value, ?string $locale = null): static
    {
        $locale = $locale ?? app()->getLocale();
        $metaKeyId = $this->getMetaKeyId($key);

        if (!$metaKeyId) {
            return $this;
        }

        $foreignKey = $this->getMetaForeignKey();

        // Upsert to metas table
        $this->metas()->updateOrCreate(
            [
                $foreignKey => $this->id,
                'meta_key_id' => $metaKeyId,
                'locale' => $locale,
            ],
            [
                'meta_value' => $value,
            ]
        );

        return $this;
    }

    /**
     * 批次設定 meta 值
     *
     * @param array $data ['key' => 'value'] 或 ['key' => ['zh-TW' => '值', 'en' => 'value']]
     * @param string|null $locale 預設語系（當 value 不是陣列時使用）
     */
    public function setMetas(array $data, ?string $locale = null): static
    {
        $locale = $locale ?? app()->getLocale();
        $translationKeys = $this->translation_keys ?? [];

        foreach ($data as $key => $value) {
            // 檢查是否為有效的 translation key
            if (!in_array($key, $translationKeys)) {
                continue;
            }

            // 多語格式：['zh-TW' => '值', 'en' => 'value']
            if (is_array($value)) {
                foreach ($value as $loc => $val) {
                    $this->setMeta($key, $val, $loc);
                }
            }
            // 單一值格式
            else {
                $this->setMeta($key, $value, $locale);
            }
        }

        // 同步到 zeav 快取表
        $this->syncToZeav();

        return $this;
    }

    /**
     * 同步資料到 zeav 快取表
     *
     * @param string|null $locale 指定語系，null 表示同步所有語系
     */
    public function syncToZeav(?string $locale = null): void
    {
        $zeavTable = $this->getZeavTableName();
        $foreignKey = $this->getMetaForeignKey();
        $translationKeys = $this->translation_keys ?? [];

        if (empty($translationKeys)) {
            return;
        }

        // 取得所有 locale
        $locales = $locale
            ? [$locale]
            : $this->metas()->distinct()->pluck('locale')->toArray();

        foreach ($locales as $loc) {
            $metas = $this->getMetas(null, $loc);

            if (empty($metas)) {
                continue;
            }

            // 準備 zeav 資料
            $zeavData = [
                $foreignKey => $this->id,
                'locale' => $loc,
            ];

            foreach ($translationKeys as $key) {
                $zeavData[$key] = $metas[$key] ?? null;
            }

            // Upsert to zeav table
            DB::table($zeavTable)->updateOrInsert(
                [
                    $foreignKey => $this->id,
                    'locale' => $loc,
                ],
                $zeavData
            );
        }
    }

    /**
     * 刪除 zeav 快取（通常在刪除主資料時呼叫）
     */
    public function deleteZeav(): void
    {
        $zeavTable = $this->getZeavTableName();
        $foreignKey = $this->getMetaForeignKey();

        DB::table($zeavTable)->where($foreignKey, $this->id)->delete();
    }

    /**
     * 重建單筆 zeav 快取
     */
    public function rebuildZeav(): void
    {
        $this->deleteZeav();
        $this->syncToZeav();
    }

    /**
     * 取得 meta_key_id
     */
    protected function getMetaKeyId(string $key): ?int
    {
        // 優先使用 Model 定義的 meta_keys 對照表
        if (isset($this->meta_keys[$key])) {
            return $this->meta_keys[$key];
        }

        // 否則查詢資料庫
        return DB::table('meta_keys')
            ->where('name', $key)
            ->value('id');
    }

    /**
     * 取得 meta_key 名稱
     */
    protected function getMetaKeyName(int $keyId): ?string
    {
        // 優先使用 Model 定義的 meta_keys 對照表
        if (isset($this->meta_keys)) {
            $flipped = array_flip($this->meta_keys);
            if (isset($flipped[$keyId])) {
                return $flipped[$keyId];
            }
        }

        // 否則查詢資料庫
        return DB::table('meta_keys')
            ->where('id', $keyId)
            ->value('name');
    }

    /**
     * 取得 metas 表名稱
     * users → user_metas
     */
    protected function getMetaTableName(): string
    {
        return Str::singular($this->getTable()) . '_metas';
    }

    /**
     * 取得 zeav 快取表名稱
     * users → zeav_users
     */
    protected function getZeavTableName(): string
    {
        return 'zeav_' . $this->getTable();
    }

    /**
     * 取得外鍵名稱
     * users → user_id
     */
    protected function getMetaForeignKey(): string
    {
        return Str::singular($this->getTable()) . '_id';
    }

    /**
     * 取得 Meta Model 類別名稱
     * 預設為 {ModelName}Meta，可在子類別覆寫
     */
    protected function getMetaModelClass(): string
    {
        return static::class . 'Meta';
    }

    /**
     * 取得 Zeav Model 類別名稱
     * 預設為 {ModelName}Zeav，可在子類別覆寫
     */
    protected function getZeavModelClass(): string
    {
        return static::class . 'Zeav';
    }

    /**
     * Boot trait - 註冊事件監聽
     */
    public static function bootHasZeavTable(): void
    {
        // 刪除主資料時，一併刪除 zeav 快取
        static::deleting(function ($model) {
            $model->deleteZeav();
        });
    }

    /**
     * 從請求資料中提取並儲存 metas
     *
     * @param array $data 請求資料
     * @param string|null $locale 預設語系
     */
    public function saveMetas(array $data, ?string $locale = null): static
    {
        $translationKeys = $this->translation_keys ?? [];
        $metaData = [];

        foreach ($translationKeys as $key) {
            if (array_key_exists($key, $data)) {
                $metaData[$key] = $data[$key];
            }
        }

        if (!empty($metaData)) {
            $this->setMetas($metaData, $locale);
        }

        return $this;
    }

    /**
     * 取得包含翻譯欄位的完整資料（供 API 或表單使用）
     *
     * @param string|null $locale 語系
     */
    public function toArrayWithMetas(?string $locale = null): array
    {
        $data = $this->toArray();
        $metas = $this->getMetas(null, $locale);

        return array_merge($data, $metas);
    }
}
