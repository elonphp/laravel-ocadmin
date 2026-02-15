<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValueGroupLevel extends Model
{
    public $timestamps = false;

    protected $table = 'clg_option_value_group_levels';

    protected $fillable = [
        'option_value_group_id',
        'option_id',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(OptionValueGroup::class, 'option_value_group_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
