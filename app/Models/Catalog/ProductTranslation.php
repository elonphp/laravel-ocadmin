<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_product_translations';

    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'description',
        'meta_title',
        'meta_keyword',
        'meta_description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
