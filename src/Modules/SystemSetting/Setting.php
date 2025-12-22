<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemSetting;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'locale',
        'group',
        'code',
        'content',
        'type',
        'note',
    ];

    protected $casts = [
        'type' => SettingType::class,
    ];

    /**
     * 取得解析後的設定值
     */
    public function getParsedContentAttribute(): mixed
    {
        return match ($this->type) {
            SettingType::Bool => filter_var($this->content, FILTER_VALIDATE_BOOLEAN),
            SettingType::Int => (int) $this->content,
            SettingType::Float => (float) $this->content,
            SettingType::Json => json_decode($this->content, true),
            SettingType::Serialized => @unserialize($this->content),
            SettingType::Array => array_map('trim', explode(',', $this->content ?? '')),
            SettingType::Line => array_filter(array_map('trim', explode("\n", $this->content ?? ''))),
            default => $this->content,
        };
    }

    /**
     * 依代碼取得設定值
     */
    public static function getValue(string $code, ?string $locale = null, mixed $default = null): mixed
    {
        $query = static::where('code', $code);

        if ($locale) {
            $query->where('locale', $locale);
        } else {
            $query->where(function ($q) {
                $q->whereNull('locale')->orWhere('locale', '');
            });
        }

        $setting = $query->first();

        return $setting ? $setting->parsed_content : $default;
    }
}
