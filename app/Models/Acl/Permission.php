<?php

namespace App\Models\Acl;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function translations()
    {
        return $this->hasMany(PermissionTranslation::class);
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
