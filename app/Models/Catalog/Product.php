<?php

namespace App\Models\Catalog;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasTranslation;

    protected $table = 'ctl_products';

    protected $fillable = [
        'model',
        'image',
        'price',
        'quantity',
        'minimum',
        'subtract',
        'shipping',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price'      => 'decimal:4',
        'quantity'    => 'integer',
        'minimum'    => 'integer',
        'subtract'   => 'boolean',
        'shipping'   => 'boolean',
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['name', 'description', 'meta_title', 'meta_keyword', 'meta_description'];

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }
}
