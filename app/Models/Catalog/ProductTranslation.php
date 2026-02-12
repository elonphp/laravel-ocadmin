<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'short_name',
        'description',
        'meta_title',
        'meta_keyword',
        'meta_description',
    ];

    protected function shortName(): Attribute
    {
        return Attribute::get(fn ($value) => $value ?: $this->attributes['name'] ?? null);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
