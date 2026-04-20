<?php

namespace App\Models\Acl;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasTranslation;

class Permission extends SpatiePermission
{
    use HasTranslation;

    protected $fillable = [
        'name',
        'guard_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
