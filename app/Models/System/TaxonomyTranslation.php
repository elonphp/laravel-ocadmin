<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'sys_taxonomy_translations';

    protected $fillable = [
        'taxonomy_id',
        'locale',
        'name',
    ];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
