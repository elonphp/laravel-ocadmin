<?php

namespace App\Models\Order;

use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProductOption extends Model
{
    public $timestamps = false;

    protected $table = 'ord_order_product_options';

    protected $fillable = [
        'order_product_id',
        'option_id',
        'option_value_id',
        'name',
        'value',
        'type',
        'price',
        'price_prefix',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
        ];
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
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
