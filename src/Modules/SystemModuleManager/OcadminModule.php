<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemModuleManager;

use Illuminate\Database\Eloquent\Model;

class OcadminModule extends Model
{
    protected $table = 'ocadmin_modules';

    protected $fillable = [
        'name',
        'alias',
        'source',
        'version',
        'enabled',
        'installed_at',
        'config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'installed_at' => 'datetime',
        'config' => 'array',
    ];

    /**
     * Scope for enabled modules.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for package modules.
     */
    public function scopePackage($query)
    {
        return $query->where('source', 'package');
    }

    /**
     * Scope for custom modules.
     */
    public function scopeCustom($query)
    {
        return $query->where('source', 'custom');
    }
}
