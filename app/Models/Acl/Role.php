<?php

namespace App\Models\Acl;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\HasTranslation;

class Role extends SpatieRole
{
    use HasTranslation;

    protected $fillable = [
        'name',
        'guard_name',
        'sort_order',
        'is_active',
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
    protected string $translationModel = RoleTranslation::class;

    /**
     * 預設載入翻譯
     */
    protected $with = ['translation'];

    public function getDisplayNameAttribute(): ?string
    {
        return $this->getTranslatedAttribute('display_name') ?? $this->name;
    }
}
