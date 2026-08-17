<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_product_options';

    protected $fillable = [
        'product_id',
        'option_id',
        'value',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function productOptionValues(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }
}
