<?php

namespace App\Models\Order;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderProduct extends Model
{
    public $timestamps = false;

    protected $table = 'ord_order_products';

    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'model',
        'quantity',
        'price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price'    => 'decimal:4',
            'total'    => 'decimal:4',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderProductOptions(): HasMany
    {
        return $this->hasMany(OrderProductOption::class);
    }
}
