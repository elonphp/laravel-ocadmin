<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValueTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'clg_option_value_translations';

    protected $fillable = [
        'option_value_id',
        'locale',
        'name',
    ];

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class);
    }
}
