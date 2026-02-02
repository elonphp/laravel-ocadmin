<?php

namespace App\Models\Acl;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'sort_order',
        'is_active',
    ];

    public function translations()
    {
        return $this->hasMany(RoleTranslation::class);
    }

    public function translation(?string $locale = null)
    {
        return $this->translations()->where('locale', $locale ?? app()->getLocale())->first();
    }

    public function getDisplayNameAttribute(): ?string
    {
        return $this->translation()?->display_name ?? $this->name;
    }
}
