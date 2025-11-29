<?php

namespace App\Models\Localization;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'native_name',
        'iso_code_2',
        'iso_code_3',
        'address_format',
        'postcode_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'postcode_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 填充預設值（表單未傳的欄位給予預設值）
     */
    public static function withDefaults(array $data): array
    {
        return array_merge([
            'postcode_required' => false,
            'is_active' => true,
            'sort_order' => 0,
        ], $data);
    }

    /**
     * 只取啟用的國家
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 依排序欄位排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
