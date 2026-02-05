<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'taxonomy_translations';

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
