<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'term_translations';

    protected $fillable = [
        'term_id',
        'locale',
        'name',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
