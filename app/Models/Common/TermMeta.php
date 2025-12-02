<?php

namespace App\Models\Common;

use App\Models\System\Database\MetaKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermMeta extends Model
{
    protected $table = 'term_metas';

    public $timestamps = false;

    protected $fillable = [
        'term_id',
        'key_id',
        'locale',
        'value',
    ];

    /**
     * 所屬 Term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * 所屬 MetaKey
     */
    public function metaKey(): BelongsTo
    {
        return $this->belongsTo(MetaKey::class, 'key_id');
    }
}
