<?php

namespace App\Models\Identity;

use App\Models\System\Localization\Country;
use App\Models\System\Localization\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'phone',
        'country_code',
        'state_id',
        'city_id',
        'postcode',
        'address_1',
        'address_2',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * 地址類型常數
     */
    public const TYPE_SHIPPING = 'shipping';
    public const TYPE_BILLING = 'billing';

    /**
     * 關聯：使用者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 關聯：國家
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code_2');
    }

    /**
     * 關聯：第一級行政區劃（州/省/縣市）
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'state_id');
    }

    /**
     * 關聯：第二級行政區劃（市/區/鄉鎮）
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'city_id');
    }

    /**
     * 取得完整地址字串
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->postcode,
            $this->country?->name,
            $this->state?->name,
            $this->city?->name,
            $this->address_1,
            $this->address_2,
        ]);

        return implode(' ', $parts);
    }
}
