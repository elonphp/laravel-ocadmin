<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // 電商範例
            [
                'type' => 'select',
                'sort_order' => 0,
                'translations' => [
                    'zh_Hant' => ['name' => '顏色'],
                    'en' => ['name' => 'Color'],
                ],
                'values' => [
                    ['sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '黃色'], 'en' => ['name' => 'Yellow']]],
                    ['sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '紅色'], 'en' => ['name' => 'Red']]],
                    ['sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '綠色'], 'en' => ['name' => 'Green']]],
                ],
            ],
            [
                'type' => 'radio',
                'sort_order' => 1,
                'translations' => [
                    'zh_Hant' => ['name' => '尺寸'],
                    'en' => ['name' => 'Size'],
                ],
                'values' => [
                    ['sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '大'], 'en' => ['name' => 'Large']]],
                    ['sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '中'], 'en' => ['name' => 'Medium']]],
                    ['sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '小'], 'en' => ['name' => 'Small']]],
                ],
            ],
            // 飲料店範例
            [
                'type' => 'select',
                'sort_order' => 2,
                'translations' => [
                    'zh_Hant' => ['name' => '冰塊'],
                    'en' => ['name' => 'Ice Level'],
                ],
                'values' => [
                    ['sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '正常冰'], 'en' => ['name' => 'Regular Ice']]],
                    ['sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '少冰'], 'en' => ['name' => 'Less Ice']]],
                    ['sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '微冰'], 'en' => ['name' => 'Light Ice']]],
                    ['sort_order' => 3, 'translations' => ['zh_Hant' => ['name' => '去冰'], 'en' => ['name' => 'No Ice']]],
                    ['sort_order' => 4, 'translations' => ['zh_Hant' => ['name' => '熱'], 'en' => ['name' => 'Hot']]],
                ],
            ],
            [
                'type' => 'select',
                'sort_order' => 3,
                'translations' => [
                    'zh_Hant' => ['name' => '甜度'],
                    'en' => ['name' => 'Sweetness'],
                ],
                'values' => [
                    ['sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '正常糖'], 'en' => ['name' => 'Regular Sugar']]],
                    ['sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '少糖'], 'en' => ['name' => 'Less Sugar']]],
                    ['sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '半糖'], 'en' => ['name' => 'Half Sugar']]],
                    ['sort_order' => 3, 'translations' => ['zh_Hant' => ['name' => '微糖'], 'en' => ['name' => 'Light Sugar']]],
                    ['sort_order' => 4, 'translations' => ['zh_Hant' => ['name' => '無糖'], 'en' => ['name' => 'No Sugar']]],
                ],
            ],
        ];

        foreach ($options as $data) {
            $option = Option::create([
                'type' => $data['type'],
                'sort_order' => $data['sort_order'],
            ]);

            $option->saveTranslations($data['translations']);

            foreach ($data['values'] as $valueData) {
                $optionValue = $option->optionValues()->create([
                    'sort_order' => $valueData['sort_order'],
                ]);

                $optionValue->saveTranslations($valueData['translations']);
            }
        }
    }
}
