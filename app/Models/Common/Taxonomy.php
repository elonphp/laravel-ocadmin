<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class Taxonomy extends Model
{
    protected $table = 'taxonomies';

    protected $fillable = [
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['name'];

    // ========== 關聯 ==========

    /**
     * 所有翻譯
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TaxonomyTranslation::class);
    }

    /**
     * 當前語系翻譯
     */
    public function translation(): HasOne
    {
        return $this->hasOne(TaxonomyTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * 所有項目
     */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('sort_order');
    }

    /**
     * 頂層項目（無父層）
     */
    public function rootTerms(): HasMany
    {
        return $this->hasMany(Term::class)
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    // ========== Accessors ==========

    /**
     * 取得當前語系名稱
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn() => $this->translation?->name ?? $this->code);
    }

    // ========== 查詢方法 ==========

    /**
     * 依 code 取得 Taxonomy
     */
    public static function findByCode(string $code): ?self
    {
        return cache()->remember("taxonomy:{$code}", 3600, function () use ($code) {
            return static::where('code', $code)->first();
        });
    }

    /**
     * 取得所有啟用的 Taxonomies
     */
    public static function getActive(): Collection
    {
        return cache()->remember('taxonomies:active:' . app()->getLocale(), 3600, function () {
            return static::where('is_active', true)
                ->with('translation')
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * 清除快取
     */
    public static function clearCache(?string $code = null): void
    {
        if ($code) {
            cache()->forget("taxonomy:{$code}");
        }

        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);
        foreach ($locales as $locale) {
            cache()->forget("taxonomies:active:{$locale}");
        }
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ========== 翻譯輔助方法 ==========

    /**
     * 取得指定語系的翻譯
     */
    public function getTranslation(string $locale): ?TaxonomyTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * 設定翻譯
     */
    public function setTranslation(string $locale, string $name): TaxonomyTranslation
    {
        return $this->translations()->updateOrCreate(
            ['locale' => $locale],
            ['name' => $name]
        );
    }

    /**
     * 批次設定翻譯
     * 支援兩種格式：
     * - ['zh_Hant' => '名稱'] (簡單格式)
     * - ['zh_Hant' => ['name' => '名稱']] (表單格式)
     */
    public function setTranslations(array $translations): void
    {
        foreach ($translations as $locale => $data) {
            // 支援兩種格式
            $name = is_array($data) ? ($data['name'] ?? '') : $data;

            if (!empty($name)) {
                $this->setTranslation($locale, $name);
            }
        }
    }
}
