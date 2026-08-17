<?php

namespace App\Models\Catalog;

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
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
