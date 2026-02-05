<?php

namespace App\Models\System;

use App\Enums\System\SettingType;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'locale',
        'group',
        'code',
        'value',
        'type',
        'note',
    ];

    protected $casts = [
        'type' => SettingType::class,
    ];

    /**
     * 取得解析後的設定值
     */
    public function getParsedValueAttribute(): mixed
    {
        return match ($this->type) {
            SettingType::Bool => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            SettingType::Int => (int) $this->value,
            SettingType::Float => (float) $this->value,
            SettingType::Json => json_decode($this->value, true),
            SettingType::Serialized => unserialize($this->value),
            SettingType::Array => array_map('trim', explode(',', $this->value ?? '')),
            SettingType::Line => array_filter(array_map('trim', explode("\n", $this->value ?? ''))),
            default => $this->value,
        };
    }
}
