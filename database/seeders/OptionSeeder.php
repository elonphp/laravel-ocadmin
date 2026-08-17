<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // 汽車規格
            [
                'code' => 'brand',
                'type' => 'select',
                'sort_order' => 0,
                'translations' => [
                    'zh_Hant' => ['name' => '廠牌'],
                    'en' => ['name' => 'Brand'],
                ],
                'values' => [
                    ['code' => 'toyota', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => 'Toyota'], 'en' => ['name' => 'Toyota']]],
                    ['code' => 'honda', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => 'Honda'], 'en' => ['name' => 'Honda']]],
                    ['code' => 'ford', 'sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => 'Ford'], 'en' => ['name' => 'Ford']]],
                ],
            ],
            [
                'code' => 'model',
                'type' => 'select',
                'sort_order' => 1,
                'translations' => [
                    'zh_Hant' => ['name' => '車型'],
                    'en' => ['name' => 'Model'],
                ],
                'values' => [
                    ['code' => 'altis', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => 'Altis'], 'en' => ['name' => 'Altis']]],
                    ['code' => 'yaris', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => 'Yaris'], 'en' => ['name' => 'Yaris']]],
                    ['code' => 'civic', 'sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => 'Civic'], 'en' => ['name' => 'Civic']]],
                    ['code' => 'fit', 'sort_order' => 3, 'translations' => ['zh_Hant' => ['name' => 'Fit'], 'en' => ['name' => 'Fit']]],
                    ['code' => 'focus', 'sort_order' => 4, 'translations' => ['zh_Hant' => ['name' => 'Focus'], 'en' => ['name' => 'Focus']]],
                    ['code' => 'kuga', 'sort_order' => 5, 'translations' => ['zh_Hant' => ['name' => 'Kuga'], 'en' => ['name' => 'Kuga']]],
                ],
            ],
            [
                'code' => 'trim',
                'type' => 'select',
                'sort_order' => 2,
                'translations' => [
                    'zh_Hant' => ['name' => '車款'],
                    'en' => ['name' => 'Trim'],
                ],
                'values' => [
                    ['code' => 'flagship', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '旗艦型'], 'en' => ['name' => 'Flagship']]],
                    ['code' => 'luxury', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '豪華型'], 'en' => ['name' => 'Luxury']]],
                    ['code' => 'classic', 'sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '經典型'], 'en' => ['name' => 'Classic']]],
                ],
            ],

            // 窗簾配置
            [
                'code' => 'material',
                'type' => 'select',
                'sort_order' => 3,
                'translations' => [
                    'zh_Hant' => ['name' => '材質'],
                    'en' => ['name' => 'Material'],
                ],
                'values' => [
                    ['code' => 'wood', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '實木'], 'en' => ['name' => 'Wood']]],
                    ['code' => 'aluminum', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '鋁合金'], 'en' => ['name' => 'Aluminum']]],
                ],
            ],
            [
                'code' => 'frame',
                'type' => 'select',
                'sort_order' => 4,
                'translations' => [
                    'zh_Hant' => ['name' => '框型'],
                    'en' => ['name' => 'Frame'],
                ],
                'values' => [
                    ['code' => 'wood_blind', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '實木百葉框'], 'en' => ['name' => 'Wood Blind Frame']]],
                    ['code' => 'wood_roller', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '實木捲簾框'], 'en' => ['name' => 'Wood Roller Frame']]],
                    ['code' => 'alu_blind', 'sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '鋁合金百葉框'], 'en' => ['name' => 'Aluminum Blind Frame']]],
                    ['code' => 'alu_roller', 'sort_order' => 3, 'translations' => ['zh_Hant' => ['name' => '鋁合金捲簾框'], 'en' => ['name' => 'Aluminum Roller Frame']]],
                ],
            ],
            [
                'code' => 'color',
                'type' => 'select',
                'sort_order' => 5,
                'translations' => [
                    'zh_Hant' => ['name' => '顏色'],
                    'en' => ['name' => 'Color'],
                ],
                'values' => [
                    ['code' => 'wood_grain', 'sort_order' => 0, 'translations' => ['zh_Hant' => ['name' => '木紋色'], 'en' => ['name' => 'Wood Grain']]],
                    ['code' => 'walnut', 'sort_order' => 1, 'translations' => ['zh_Hant' => ['name' => '胡桃色'], 'en' => ['name' => 'Walnut']]],
                    ['code' => 'white', 'sort_order' => 2, 'translations' => ['zh_Hant' => ['name' => '白色'], 'en' => ['name' => 'White']]],
                    ['code' => 'silver', 'sort_order' => 3, 'translations' => ['zh_Hant' => ['name' => '銀色'], 'en' => ['name' => 'Silver']]],
                    ['code' => 'black', 'sort_order' => 4, 'translations' => ['zh_Hant' => ['name' => '黑色'], 'en' => ['name' => 'Black']]],
                ],
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
