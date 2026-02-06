<?php

namespace App\Enums\Common;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.gender.' . $this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * 供前端下拉選單使用（含空白預設項）
     * 註：$placeholder 是選單的第一項 "請選擇性別"，先加到 $options 陣列。
     * 然後再將每個性別 加入 $options 陣列，最後回傳完整的選項陣列。
     */
    public static function options(?string $placeholder = null): array
    {
        $options = [];

        if ($placeholder !== null) {
            $options[] = ['value' => '', 'label' => $placeholder];
        }

        foreach (self::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => $case->label()];
        }

        return $options;
    }
}
