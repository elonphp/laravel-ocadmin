<?php

namespace App\Models\Common;

use App\Models\System\Database\MetaKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class Term extends Model
{
    protected $table = 'terms';

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['name'];

    /**
     * Meta 快取
     */
    protected array $metaCache = [];

    // ========== 關聯 ==========

    /**
     * 所屬 Taxonomy
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * 父層 Term
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 子層 Terms
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 所有翻譯
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TermTranslation::class);
    }

    /**
     * 當前語系翻譯
     */
    public function translation(): HasOne
    {
        return $this->hasOne(TermTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * 所有 Metas
     */
    public function metas(): HasMany
    {
        return $this->hasMany(TermMeta::class);
    }

    // ========== Accessors ==========

    /**
     * 取得當前語系名稱
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn() => $this->translation?->name ?? $this->code);
    }

    // ========== Meta 相關方法 ==========

    /**
     * 載入所有 meta 到快取
     */
    protected function loadMetaCache(): void
    {
        if (empty($this->metaCache) && $this->exists) {
            $this->metaCache = $this->metas()
                ->with('metaKey')
                ->get()
                ->pluck('value', 'metaKey.name')
                ->toArray();
        }
    }

    /**
     * 取得 meta 值
     */
    public function getMeta(string $key, $default = null)
    {
        $this->loadMetaCache();
        return $this->metaCache[$key] ?? $default;
    }

    /**
     * 設定 meta 值
     */
    public function setMeta(string $key, $value): void
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            throw new \InvalidArgumentException("Meta key '{$key}' not found");
        }

        $this->metas()->updateOrCreate(
            ['key_id' => $keyId],
            ['value' => $value]
        );

        $this->metaCache[$key] = $value;
    }

    /**
     * 批次設定 meta
     */
    public function setMetas(array $metas): void
    {
        foreach ($metas as $key => $value) {
            if ($value !== null && $value !== '') {
                $this->setMeta($key, $value);
            }
        }
    }

    /**
     * 刪除 meta
     */
    public function deleteMeta(string $key): void
    {
        $keyId = MetaKey::getId($key);
        if ($keyId) {
            $this->metas()->where('key_id', $keyId)->delete();
            unset($this->metaCache[$key]);
        }
    }

    /**
     * 取得所有 meta
     */
    public function getAllMetas(): array
    {
        $this->loadMetaCache();
        return $this->metaCache;
    }

    /**
     * 動態取得 meta 屬性
     */
    public function __get($key)
    {
        // 先嘗試取得原生屬性
        $value = parent::__get($key);
        if ($value !== null) {
            return $value;
        }

        // 嘗試從 meta 取得
        if ($this->exists && !in_array($key, ['name', 'translation', 'translations', 'taxonomy', 'parent', 'children', 'metas'])) {
            return $this->getMeta($key);
        }

        return null;
    }

    // ========== 查詢方法 ==========

    /**
     * 依 taxonomy code 取得所有項目（含快取）
     */
    public static function getByTaxonomy(string $taxonomyCode): Collection
    {
        $cacheKey = "terms:{$taxonomyCode}:" . app()->getLocale();

        return cache()->remember($cacheKey, 3600, function () use ($taxonomyCode) {
            return static::whereHas('taxonomy', fn($q) => $q->where('code', $taxonomyCode))
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children', 'metas.metaKey', 'translation'])
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * 依 taxonomy code + term code 取得單一項目
     */
    public static function getByCode(string $taxonomyCode, string $code): ?self
    {
        return static::whereHas('taxonomy', fn($q) => $q->where('code', $taxonomyCode))
            ->where('code', $code)
            ->with(['metas.metaKey', 'translation'])
            ->first();
    }

    /**
     * 清除快取
     */
    public static function clearCache(string $taxonomyCode): void
    {
        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);
        foreach ($locales as $locale) {
            cache()->forget("terms:{$taxonomyCode}:{$locale}");
        }
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // ========== 翻譯輔助方法 ==========

    /**
     * 取得指定語系的翻譯
     */
    public function getTranslation(string $locale): ?TermTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * 設定翻譯
     */
    public function setTranslation(string $locale, string $name, ?string $shortName = null): TermTranslation
    {
        $data = ['name' => $name];
        if ($shortName !== null) {
            $data['short_name'] = $shortName;
        }

        return $this->translations()->updateOrCreate(
            ['locale' => $locale],
            $data
        );
    }

    /**
     * 批次設定翻譯
     */
    public function setTranslations(array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (is_array($data)) {
                $name = $data['name'] ?? '';
                $shortName = $data['short_name'] ?? null;
            } else {
                $name = $data;
                $shortName = null;
            }

            if (!empty($name)) {
                $this->setTranslation($locale, $name, $shortName);
            }
        }
    }

    // ========== 輔助方法 ==========

    /**
     * 取得完整路徑（含父層）
     */
    public function getFullPath(string $separator = ' > '): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode($separator, $path);
    }

    /**
     * 取得層級深度
     */
    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
