<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemSetting;

enum SettingType: string
{
    case Text = 'text';             // 純文字
    case Line = 'line';             // 多行，一行一個項目
    case Json = 'json';             // JSON 格式
    case Serialized = 'serialized'; // 序列化格式
    case Bool = 'bool';             // 是 / 否
    case Int = 'int';               // 整數
    case Float = 'float';           // 小數
    case Array = 'array';           // 逗號分隔

    /**
     * 取得所有值（供 validation 使用）
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * 取得標籤（供下拉選單使用）
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => __('system-setting::setting.type_text'),
            self::Line => __('system-setting::setting.type_line'),
            self::Json => __('system-setting::setting.type_json'),
            self::Serialized => __('system-setting::setting.type_serialized'),
            self::Bool => __('system-setting::setting.type_bool'),
            self::Int => __('system-setting::setting.type_int'),
            self::Float => __('system-setting::setting.type_float'),
            self::Array => __('system-setting::setting.type_array'),
        };
    }
}
