<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use Illuminate\Database\Seeder;

/**
 * OpenCart 4 demo option / option_value 移植
 *
 * 來源：D:\Codes\PHP\Opencart\opencart4x\htdocs\opencart4x.sql（oc_option / oc_option_description / oc_option_value / oc_option_value_description）
 * 目標：ctl_options / ctl_option_translations / ctl_option_values / ctl_option_value_translations
 *
 * 邊界：
 * - 11 個 option（oc_id 1, 2, 4-12，跳過不存在的 3）；14 個 option_value
 * - oc_option.validation 欄位丟掉（本系統 schema 無；OpenCart dump 全 NULL）
 * - sort_order 偏移到 11-21 區段，避開既有 OptionSeeder（0-5）
 * - code 加 oc_ prefix 區隔既有（避免撞 OptionSeeder 的 color/size 等）
 * - 翻譯：en 保留 OpenCart dump 原文，zh_Hant 直譯
 *
 * oc_id → code 對照（給 OpencartProductOptionSeeder 使用）：
 *   1  → oc_size_radio       11 → oc_size
 *   2  → oc_checkbox_demo    12 → oc_delivery_date
 *   4  → oc_text_demo
 *   5  → oc_color
 *   6  → oc_textarea_demo
 *   7  → oc_file_demo
 *   8  → oc_date_demo
 *   9  → oc_time_demo
 *   10 → oc_datetime_demo
 *
 * oc_option_value_id → code 對照：
 *   32 → oc_radio_small    31 → oc_radio_medium    43 → oc_radio_large
 *   23 → oc_checkbox_1     24 → oc_checkbox_2      44 → oc_checkbox_3     45 → oc_checkbox_4
 *   39 → oc_red            40 → oc_blue            41 → oc_green          42 → oc_yellow
 *   46 → oc_small          47 → oc_medium          48 → oc_large
 */
class OpencartOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // oc_id=1, type=radio, sort=1
            [
                'code' => 'oc_size_radio',
                'type' => 'radio',
                'sort_order' => 11,
                'translations' => [
                    'en' => ['name' => 'Radio'],
                    'zh_Hant' => ['name' => '單選'],
                ],
                'values' => [
                    // oc_id=32, sort=1
                    ['code' => 'oc_radio_small', 'sort_order' => 1, 'translations' => [
                        'en' => ['name' => 'Small'], 'zh_Hant' => ['name' => '小'],
                    ]],
                    // oc_id=31, sort=2
                    ['code' => 'oc_radio_medium', 'sort_order' => 2, 'translations' => [
                        'en' => ['name' => 'Medium'], 'zh_Hant' => ['name' => '中'],
                    ]],
                    // oc_id=43, sort=3
                    ['code' => 'oc_radio_large', 'sort_order' => 3, 'translations' => [
                        'en' => ['name' => 'Large'], 'zh_Hant' => ['name' => '大'],
                    ]],
                ],
            ],

            // oc_id=2, type=checkbox, sort=2
            [
                'code' => 'oc_checkbox_demo',
                'type' => 'checkbox',
                'sort_order' => 12,
                'translations' => [
                    'en' => ['name' => 'Checkbox'],
                    'zh_Hant' => ['name' => '複選'],
                ],
                'values' => [
                    // oc_id=23, sort=1
                    ['code' => 'oc_checkbox_1', 'sort_order' => 1, 'translations' => [
                        'en' => ['name' => 'Checkbox 1'], 'zh_Hant' => ['name' => '複選 1'],
                    ]],
                    // oc_id=24, sort=2
                    ['code' => 'oc_checkbox_2', 'sort_order' => 2, 'translations' => [
                        'en' => ['name' => 'Checkbox 2'], 'zh_Hant' => ['name' => '複選 2'],
                    ]],
                    // oc_id=44, sort=3
                    ['code' => 'oc_checkbox_3', 'sort_order' => 3, 'translations' => [
                        'en' => ['name' => 'Checkbox 3'], 'zh_Hant' => ['name' => '複選 3'],
                    ]],
                    // oc_id=45, sort=4
                    ['code' => 'oc_checkbox_4', 'sort_order' => 4, 'translations' => [
                        'en' => ['name' => 'Checkbox 4'], 'zh_Hant' => ['name' => '複選 4'],
                    ]],
                ],
            ],

            // oc_id=4, type=text, sort=3，無 values
            [
                'code' => 'oc_text_demo',
                'type' => 'text',
                'sort_order' => 13,
                'translations' => [
                    'en' => ['name' => 'Text'],
                    'zh_Hant' => ['name' => '文字'],
                ],
                'values' => [],
            ],

            // oc_id=5, type=select, sort=4（OpenCart name 為 "Select" 但 values 是顏色，故 code 命名為 oc_color）
            [
                'code' => 'oc_color',
                'type' => 'select',
                'sort_order' => 14,
                'translations' => [
                    'en' => ['name' => 'Select'],
                    'zh_Hant' => ['name' => '下拉選單'],
                ],
                'values' => [
                    // oc_id=39, sort=1
                    ['code' => 'oc_red', 'sort_order' => 1, 'translations' => [
                        'en' => ['name' => 'Red'], 'zh_Hant' => ['name' => '紅色'],
                    ]],
                    // oc_id=40, sort=2
                    ['code' => 'oc_blue', 'sort_order' => 2, 'translations' => [
                        'en' => ['name' => 'Blue'], 'zh_Hant' => ['name' => '藍色'],
                    ]],
                    // oc_id=41, sort=3
                    ['code' => 'oc_green', 'sort_order' => 3, 'translations' => [
                        'en' => ['name' => 'Green'], 'zh_Hant' => ['name' => '綠色'],
                    ]],
                    // oc_id=42, sort=4
                    ['code' => 'oc_yellow', 'sort_order' => 4, 'translations' => [
                        'en' => ['name' => 'Yellow'], 'zh_Hant' => ['name' => '黃色'],
                    ]],
                ],
            ],

            // oc_id=6, type=textarea, sort=5，無 values
            [
                'code' => 'oc_textarea_demo',
                'type' => 'textarea',
                'sort_order' => 15,
                'translations' => [
                    'en' => ['name' => 'Textarea'],
                    'zh_Hant' => ['name' => '多行文字'],
                ],
                'values' => [],
            ],

            // oc_id=7, type=file, sort=6，無 values
            [
                'code' => 'oc_file_demo',
                'type' => 'file',
                'sort_order' => 16,
                'translations' => [
                    'en' => ['name' => 'File'],
                    'zh_Hant' => ['name' => '檔案'],
                ],
                'values' => [],
            ],

            // oc_id=8, type=date, sort=7，無 values
            [
                'code' => 'oc_date_demo',
                'type' => 'date',
                'sort_order' => 17,
                'translations' => [
                    'en' => ['name' => 'Date'],
                    'zh_Hant' => ['name' => '日期'],
                ],
                'values' => [],
            ],

            // oc_id=9, type=time, sort=8，無 values
            [
                'code' => 'oc_time_demo',
                'type' => 'time',
                'sort_order' => 18,
                'translations' => [
                    'en' => ['name' => 'Time'],
                    'zh_Hant' => ['name' => '時間'],
                ],
                'values' => [],
            ],

            // oc_id=10, type=datetime, sort=9（OpenCart dump name 為 "Date &amp; Time"，已解碼為 "Date & Time"）
            [
                'code' => 'oc_datetime_demo',
                'type' => 'datetime',
                'sort_order' => 19,
                'translations' => [
                    'en' => ['name' => 'Date & Time'],
                    'zh_Hant' => ['name' => '日期與時間'],
                ],
                'values' => [],
            ],

            // oc_id=11, type=select, sort=10
            [
                'code' => 'oc_size',
                'type' => 'select',
                'sort_order' => 20,
                'translations' => [
                    'en' => ['name' => 'Size'],
                    'zh_Hant' => ['name' => '尺寸'],
                ],
                'values' => [
                    // oc_id=46, sort=1
                    ['code' => 'oc_small', 'sort_order' => 1, 'translations' => [
                        'en' => ['name' => 'Small'], 'zh_Hant' => ['name' => '小'],
                    ]],
                    // oc_id=47, sort=2
                    ['code' => 'oc_medium', 'sort_order' => 2, 'translations' => [
                        'en' => ['name' => 'Medium'], 'zh_Hant' => ['name' => '中'],
                    ]],
                    // oc_id=48, sort=3
                    ['code' => 'oc_large', 'sort_order' => 3, 'translations' => [
                        'en' => ['name' => 'Large'], 'zh_Hant' => ['name' => '大'],
                    ]],
                ],
            ],

            // oc_id=12, type=date, sort=11，無 values
            [
                'code' => 'oc_delivery_date',
                'type' => 'date',
                'sort_order' => 21,
                'translations' => [
                    'en' => ['name' => 'Delivery Date'],
                    'zh_Hant' => ['name' => '交貨日期'],
                ],
                'values' => [],
            ],
        ];

        foreach ($options as $data) {
            $option = Option::create([
                'code' => $data['code'],
                'type' => $data['type'],
                'sort_order' => $data['sort_order'],
            ]);

            $option->saveTranslations($data['translations']);

            foreach ($data['values'] as $valueData) {
                $optionValue = $option->optionValues()->create([
                    'code' => $valueData['code'],
                    'sort_order' => $valueData['sort_order'],
                ]);

                $optionValue->saveTranslations($valueData['translations']);
            }
        }
    }
}
