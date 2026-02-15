<?php

namespace App\Models\Catalog;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionValue extends Model
{
    use HasTranslation;

    protected $table = 'clg_option_values';

    protected $fillable = [
        'option_id',
        'code',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['name'];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function parentLinks(): HasMany
    {
        return $this->hasMany(OptionValueLink::class, 'parent_option_value_id');
    }

    public function childLinks(): HasMany
    {
        return $this->hasMany(OptionValueLink::class, 'child_option_value_id');
    }
}
