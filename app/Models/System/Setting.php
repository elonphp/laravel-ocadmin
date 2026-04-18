<?php

namespace App\Models\System;

use App\Enums\System\SettingType;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'sys_settings';

    protected $fillable = [
        'group',
        'code',
        'name',
        'name_translations',
        'value',
        'type',
        'is_autoload',
        'note',
    ];

    protected $casts = [
        'type'              => SettingType::class,
        'name_translations' => 'array',
        'is_autoload'       => 'boolean',
    ];

    /**
     * 取得當前語系的名稱，無翻譯則 fallback 到 name
     */
    public function getTranslatedNameAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->name_translations[$locale] ?? $this->name;
    }

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
