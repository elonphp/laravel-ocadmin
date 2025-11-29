<?php

namespace App\Enums\System;

enum SettingType: string
{
    case Text = 'text';             // 純文字，整段文字
    case Line = 'line';             // 多行，一行一個項目
    case Json = 'json';             // JSON 格式
    case Serialized = 'serialized'; // 序列化格式
    case Bool = 'bool';             // 是 / 否
    case Int = 'int';               // 整數
    case Float = 'float';           // 小數
    case Array = 'array';           // 逗號分隔

    /**
     * 取得所有值（供 migration 使用）
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
            self::Text => '純文字',
            self::Line => '多行文字',
            self::Json => 'JSON',
            self::Serialized => '序列化',
            self::Bool => '布林值',
            self::Int => '整數',
            self::Float => '小數',
            self::Array => '陣列',
        };
    }
}
