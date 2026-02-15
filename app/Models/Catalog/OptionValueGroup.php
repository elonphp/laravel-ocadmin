<?php

namespace App\Models\Catalog;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionValueGroup extends Model
{
    use HasTranslation;

    protected $table = 'clg_option_value_groups';

    protected $fillable = [
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public array $translatedAttributes = ['name', 'description'];

    public function levels(): HasMany
    {
        return $this->hasMany(OptionValueGroupLevel::class, 'option_value_group_id')->orderBy('level');
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(
            Option::class,
            'clg_option_value_group_levels',
            'option_value_group_id',
            'option_id'
        )->withPivot('level')->orderByPivot('level');
    }
}
