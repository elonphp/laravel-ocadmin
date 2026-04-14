<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasTranslation;

    protected $table = 'sys_menus';

    protected $fillable = [
        'portal',
        'group',
        'parent_id',
        'permission_name',
        'route_name',
        'href',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected string $translationModel = MenuTranslation::class;

    protected array $translatedAttributes = ['display_name'];

    protected $with = ['translation'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ========== Relationships ==========

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * 所有子選單（不限啟用狀態），後台樹狀管理用
     */
    public function allChildren(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('sort_order');
    }

    // ========== Helpers ==========

    /**
     * 產生 URL：route_name 優先，其次 href，都沒有回傳空字串
     */
    public function resolveUrl(): string
    {
        if ($this->route_name) {
            return route($this->route_name);
        }

        return $this->href ?? '';
    }

    /**
     * 轉為前端 sidebar 所需的 array 格式
     */
    public function toMenuItem(): array
    {
        return [
            'id'         => 'menu-' . $this->id,
            'icon'       => $this->icon ?? '',
            'name'       => $this->display_name,
            'permission' => $this->permission_name,
            'href'       => $this->resolveUrl(),
            'children'   => $this->children->map(fn ($child) => $child->toMenuItem())->toArray(),
        ];
    }
}
