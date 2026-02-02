<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

/**
 * 多語翻譯 Trait
 *
 * 為 Model 提供翻譯功能，採用「主表 + 翻譯表」模式。
 *
 * 使用方式：
 * 1. Model 使用此 trait
 * 2. 定義 $translatedAttributes 屬性（可翻譯的欄位）
 * 3. 建立對應的翻譯 Model（如 ProductTranslation）
 *
 * 命名慣例：
 * - 主表 Model: Product
 * - 翻譯 Model: ProductTranslation（可透過 $translationModel 覆寫）
 * - 外鍵: product_id（可透過 $translationForeignKey 覆寫）
 * - 語系欄位: locale（可透過 $localeKey 覆寫）
 */
trait HasTranslation
{
    /**
     * 覆寫 toArray，自動包含翻譯欄位
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // 加入翻譯欄位
        foreach ($this->getTranslatedAttributes() as $attribute) {
            if (!array_key_exists($attribute, $array)) {
                $array[$attribute] = $this->getTranslatedAttribute($attribute);
            }
        }

        return $array;
    }

    /**
     * 取得所有翻譯（HasMany 關聯）
     */
    public function translations(): HasMany
    {
        return $this->hasMany(
            $this->getTranslationModelName(),
            $this->getTranslationForeignKey()
        );
    }

    /**
     * 取得當前語系的翻譯（HasOne 關聯，用於 eager loading）
     */
    public function translation(): HasOne
    {
        return $this->hasOne(
            $this->getTranslationModelName(),
            $this->getTranslationForeignKey()
        )->where($this->getLocaleKey(), $this->getCurrentLocale());
    }

    /**
     * 取得指定語系的翻譯
     *
     * @param string|null $locale 語系代碼，null 為當前語系
     * @param bool $withFallback 找不到時是否使用備用語系
     */
    public function translate(?string $locale = null, bool $withFallback = false): ?Model
    {
        $locale = $locale ?? $this->getCurrentLocale();

        $translation = $this->getTranslationByLocale($locale);

        if ($translation === null && $withFallback) {
            $translation = $this->getTranslationByLocale($this->getFallbackLocale());
        }

        return $translation;
    }

    /**
     * 取得翻譯，找不到時使用備用語系
     */
    public function translateOrDefault(?string $locale = null): ?Model
    {
        return $this->translate($locale, true);
    }

    /**
     * 取得翻譯，找不到時建立新實例
     */
    public function translateOrNew(?string $locale = null): Model
    {
        $locale = $locale ?? $this->getCurrentLocale();

        $translation = $this->getTranslationByLocale($locale);

        if ($translation === null) {
            $translation = $this->newTranslation($locale);
        }

        return $translation;
    }

    /**
     * 檢查指定語系的翻譯是否存在
     */
    public function hasTranslation(?string $locale = null): bool
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return $this->getTranslationByLocale($locale) !== null;
    }

    /**
     * 取得翻譯屬性值（自動 fallback）
     */
    public function getTranslatedAttribute(string $attribute, ?string $locale = null): mixed
    {
        $translation = $this->translate($locale, true);

        return $translation?->{$attribute};
    }

    /**
     * 取得所有翻譯為陣列
     *
     * @return array ['zh_Hant' => [...], 'en' => [...]]
     */
    public function getTranslationsArray(): array
    {
        $result = [];

        foreach ($this->translations as $translation) {
            $locale = $translation->{$this->getLocaleKey()};
            $result[$locale] = $translation->toArray();
        }

        return $result;
    }

    /**
     * 刪除指定語系的翻譯
     */
    public function deleteTranslation(string $locale): bool
    {
        $deleted = $this->translations()
            ->where($this->getLocaleKey(), $locale)
            ->delete();

        if ($deleted) {
            $this->load('translations');
        }

        return $deleted > 0;
    }

    /**
     * 刪除多個或全部翻譯
     *
     * @param string|array|null $locales 語系代碼，null 刪除全部
     */
    public function deleteTranslations(string|array|null $locales = null): int
    {
        $query = $this->translations();

        if ($locales !== null) {
            $locales = (array) $locales;
            $query->whereIn($this->getLocaleKey(), $locales);
        }

        $deleted = $query->delete();

        $this->load('translations');

        return $deleted;
    }

    /**
     * 複製模型及其所有翻譯
     *
     * @param array|null $except 排除的欄位
     */
    public function replicateWithTranslations(?array $except = null): static
    {
        $replica = $this->replicate($except);
        $replica->save();

        foreach ($this->translations as $translation) {
            $newTranslation = $translation->replicate();
            $newTranslation->{$this->getTranslationForeignKey()} = $replica->id;
            $newTranslation->save();
        }

        $replica->load('translations');

        return $replica;
    }

    /**
     * 儲存翻譯
     *
     * @param array $translations ['zh_Hant' => ['name' => '...'], 'en' => ['name' => '...']]
     */
    public function saveTranslations(array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            $this->saveTranslation($locale, $attributes);
        }

        $this->load('translations');
    }

    /**
     * 儲存單一語系的翻譯
     */
    public function saveTranslation(string $locale, array $attributes): Model
    {
        $translation = $this->translateOrNew($locale);

        foreach ($attributes as $key => $value) {
            $translation->{$key} = $value;
        }

        $translation->{$this->getLocaleKey()} = $locale;
        $translation->{$this->getTranslationForeignKey()} = $this->id;
        $translation->save();

        return $translation;
    }

    // ========== Query Scopes ==========

    /**
     * 篩選有指定語系翻譯的記錄
     */
    public function scopeTranslatedIn($query, ?string $locale = null)
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return $query->whereHas('translations', function ($q) use ($locale) {
            $q->where($this->getLocaleKey(), $locale);
        });
    }

    /**
     * 篩選有任何翻譯的記錄
     */
    public function scopeTranslated($query)
    {
        return $query->has('translations');
    }

    /**
     * 依翻譯欄位查詢
     */
    public function scopeWhereTranslation($query, string $column, mixed $value, ?string $locale = null)
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return $query->whereHas('translations', function ($q) use ($column, $value, $locale) {
            $q->where($this->getLocaleKey(), $locale)
              ->where($column, $value);
        });
    }

    /**
     * 依翻譯欄位模糊查詢
     */
    public function scopeWhereTranslationLike($query, string $column, string $value, ?string $locale = null)
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return $query->whereHas('translations', function ($q) use ($column, $value, $locale) {
            $q->where($this->getLocaleKey(), $locale)
              ->where($column, 'LIKE', $value);
        });
    }

    // ========== Attribute Access ==========

    /**
     * 覆寫 getAttribute，自動處理可翻譯欄位
     *
     * 優先順序：
     * 1. 如果 Model 有自定義 accessor（如 getDisplayNameAttribute），使用它
     * 2. 如果欄位在 $translatedAttributes 中，自動回傳翻譯值
     * 3. 否則使用 Laravel 預設行為
     */
    public function getAttribute($key)
    {
        // 如果有自定義 accessor，讓 Laravel 處理
        if ($this->hasGetMutator($key) || $this->hasAttributeGetMutator($key)) {
            return parent::getAttribute($key);
        }

        // 如果是可翻譯欄位，自動回傳翻譯值
        if (in_array($key, $this->getTranslatedAttributes())) {
            return $this->getTranslatedAttribute($key);
        }

        // 其他情況使用 Laravel 預設行為
        return parent::getAttribute($key);
    }

    // ========== Helper Methods ==========

    /**
     * 從已載入的關聯中取得翻譯
     */
    protected function getTranslationByLocale(string $locale): ?Model
    {
        // 優先從已載入的關聯中取得
        if ($this->relationLoaded('translations')) {
            return $this->translations
                ->where($this->getLocaleKey(), $locale)
                ->first();
        }

        // 否則查詢資料庫
        return $this->translations()
            ->where($this->getLocaleKey(), $locale)
            ->first();
    }

    /**
     * 建立新的翻譯實例（未儲存）
     */
    protected function newTranslation(string $locale): Model
    {
        $modelName = $this->getTranslationModelName();
        $translation = new $modelName();
        $translation->{$this->getLocaleKey()} = $locale;
        $translation->{$this->getTranslationForeignKey()} = $this->id;

        return $translation;
    }

    /**
     * 取得翻譯 Model 類別名稱
     */
    public function getTranslationModelName(): string
    {
        return $this->translationModel ?? get_class($this) . 'Translation';
    }

    /**
     * 取得翻譯表的外鍵名稱
     */
    public function getTranslationForeignKey(): string
    {
        return $this->translationForeignKey ?? $this->getForeignKey();
    }

    /**
     * 取得語系欄位名稱
     */
    public function getLocaleKey(): string
    {
        return $this->localeKey ?? 'locale';
    }

    /**
     * 取得當前語系
     */
    protected function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * 取得備用語系
     */
    protected function getFallbackLocale(): string
    {
        return $this->fallbackLocale ?? config('app.fallback_locale', 'en');
    }

    /**
     * 取得可翻譯的欄位列表
     */
    public function getTranslatedAttributes(): array
    {
        return $this->translatedAttributes ?? [];
    }
}
