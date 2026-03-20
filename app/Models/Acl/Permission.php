<?php

namespace App\Models\Acl;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Permission extends SpatiePermission
{
    use HasTranslation;

    protected $fillable = [
        'parent_id',
        'name',
        'guard_name',
        'type',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    /**
     * 可翻譯的欄位
     */
    protected array $translatedAttributes = [
        'display_name',
        'note',
    ];

    /**
     * 翻譯 Model 類別
     */
    protected string $translationModel = PermissionTranslation::class;

    /**
     * 預設載入翻譯
     */
    protected $with = ['translation'];

    public function getDisplayNameAttribute(): ?string
    {
        return $this->getTranslatedAttribute('display_name') ?? $this->name;
    }

    // ── 樹狀結構 ──

    /**
     * 父層權限
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * 子層權限
     */
    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 取得所有祖先權限（遞迴向上）
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    // ── 類型判斷 ──

    /**
     * 是否為選單項目
     */
    public function isMenu(): bool
    {
        return $this->type === 'menu';
    }

    /**
     * 是否為功能權限
     */
    public function isAction(): bool
    {
        return $this->type === 'action';
    }

    /**
     * 是否為頂層權限
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    // ── Scope ──

    public function scopeMenuOnly($query)
    {
        return $query->where('type', 'menu');
    }

    public function scopeActionOnly($query)
    {
        return $query->where('type', 'action');
    }

    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_id');
    }
}
