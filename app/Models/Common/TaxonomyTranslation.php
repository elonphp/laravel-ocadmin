<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTranslation extends Model
{
    protected $table = 'taxonomy_translations';

    public $timestamps = false;

    protected $fillable = [
        'taxonomy_id',
        'locale',
        'name',
    ];

    /**
     * 所屬 Taxonomy
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
