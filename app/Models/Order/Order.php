<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'ord_orders';

    protected $fillable = [
        'user_id',
        'order_no',
        'status',
        'currency_code',
        'subtotal',
        'total',
        'comment',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'payment_method',
        'shipping_method',
    ];

    protected function casts(): array
    {
        return [
            'status'   => 'integer',
            'subtotal' => 'decimal:4',
            'total'    => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }
}
