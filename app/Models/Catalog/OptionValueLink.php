<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValueLink extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_option_value_links';

    protected $fillable = [
        'parent_option_value_id',
        'child_option_value_id',
    ];

    public function parentValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class, 'parent_option_value_id');
    }

    public function childValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class, 'child_option_value_id');
    }
}
