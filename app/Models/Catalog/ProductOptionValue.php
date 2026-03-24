<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionValue extends Model
{
    public $timestamps = false;

    protected $table = 'ctl_product_option_values';

    protected $fillable = [
        'product_option_id',
        'product_id',
        'option_id',
        'option_value_id',
        'quantity',
        'subtract',
        'price',
        'price_prefix',
        'weight',
        'weight_prefix',
    ];

    protected $casts = [
        'quantity'  => 'integer',
        'subtract'  => 'boolean',
        'price'     => 'decimal:4',
        'weight'    => 'decimal:8',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class);
    }
}
