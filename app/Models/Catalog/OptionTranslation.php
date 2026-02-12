<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_option_translations';

    protected $fillable = [
        'option_id',
        'locale',
        'name',
        'short_name',
    ];

    protected function shortName(): Attribute
    {
        return Attribute::get(fn ($value) => $value ?: $this->attributes['name'] ?? null);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
