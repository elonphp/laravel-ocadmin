<?php

namespace App\Models\System\Localization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'country_code',
        'parent_id',
        'level',
        'name',
        'native_name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'level' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 設定 country_code 時自動轉大寫
     */
    public function setCountryCodeAttribute($value): void
    {
        $this->attributes['country_code'] = strtoupper($value);
    }

    /**
     * 填充預設值（表單未傳的欄位給予預設值）
     */
    public static function withDefaults(array $data): array
    {
        return array_merge([
            'is_active' => true,
            'sort_order' => 0,
        ], $data);
    }

    /**
     * 關聯：所屬國家
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code_2');
    }

    /**
     * 關聯：上層行政區域
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'parent_id');
    }

    /**
     * 關聯：下層行政區域
     */
    public function children(): HasMany
    {
        return $this->hasMany(Division::class, 'parent_id');
    }

    /**
     * 只取啟用的行政區域
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
