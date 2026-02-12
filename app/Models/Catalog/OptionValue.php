<?php

namespace App\Models\Catalog;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValue extends Model
{
    use HasTranslation;

    protected $table = 'ctl_option_values';

    protected $fillable = [
        'option_id',
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
}
