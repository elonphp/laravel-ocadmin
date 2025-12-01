<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermTranslation extends Model
{
    protected $table = 'term_translations';

    public $timestamps = false;

    protected $fillable = [
        'term_id',
        'locale',
        'name',
        'short_name',
    ];

    /**
     * 所屬 Term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
