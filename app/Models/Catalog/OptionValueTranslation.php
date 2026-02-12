<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValueTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_option_value_translations';

    protected $fillable = [
        'option_value_id',
        'locale',
        'name',
        'short_name',
    ];

    protected function shortName(): Attribute
    {
        return Attribute::get(fn ($value) => $value ?: $this->attributes['name'] ?? null);
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class);
    }
}
