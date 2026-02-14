<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValue;
use App\Models\Catalog\OptionValueLink;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * 汽車選項 Seeder
     *
     * 選項群組：brand(廠牌) → model(車型) → trim(車款) → displacement(排氣量)
     * 四大車廠：Ford, Mitsubishi, Toyota, Honda
     */
    public function run(): void
    {
        // 清除舊資料（依序處理外鍵約束）
        OptionValueLink::query()->delete();
        OptionValue::query()->delete();
        Option::query()->delete();

        // ── 選項群組 ──

        $groups = [
            ['key' => 'brand',        'type' => 'select', 'sort_order' => 1, 'trans' => ['zh_Hant' => '廠牌',   'en' => 'Brand']],
            ['key' => 'model',        'type' => 'select', 'sort_order' => 2, 'trans' => ['zh_Hant' => '車型',   'en' => 'Model']],
            ['key' => 'trim',         'type' => 'select', 'sort_order' => 3, 'trans' => ['zh_Hant' => '車款',   'en' => 'Trim']],
            ['key' => 'displacement', 'type' => 'select', 'sort_order' => 4, 'trans' => ['zh_Hant' => '排氣量', 'en' => 'Displacement']],
        ];

        $optionMap = [];

        foreach ($groups as $g) {
            $key = $g['key'];
            $trans = $g['trans'];

            $option = Option::create([
                'code' => $key,
                'type' => $g['type'],
                'sort_order' => $g['sort_order'],
            ]);
            $option->saveTranslations(array_map(fn ($name) => ['name' => $name], $trans));
            $optionMap[$key] = $option;
        }

        // ── 選項值 ──

        $values = [
            // 廠牌
            ['option' => 'brand', 'key' => 'ford',       'sort_order' => 1, 'trans' => ['zh_Hant' => '福特',   'en' => 'Ford']],
            ['option' => 'brand', 'key' => 'mitsubishi', 'sort_order' => 2, 'trans' => ['zh_Hant' => '三菱',   'en' => 'Mitsubishi']],
            ['option' => 'brand', 'key' => 'toyota',     'sort_order' => 3, 'trans' => ['zh_Hant' => '豐田',   'en' => 'Toyota']],
            ['option' => 'brand', 'key' => 'honda',      'sort_order' => 4, 'trans' => ['zh_Hant' => '本田',   'en' => 'Honda']],

            // 車型 — Ford
            ['option' => 'model', 'key' => 'focus',         'sort_order' => 1, 'trans' => ['zh_Hant' => 'Focus',         'en' => 'Focus']],
            ['option' => 'model', 'key' => 'kuga',          'sort_order' => 2, 'trans' => ['zh_Hant' => 'Kuga',          'en' => 'Kuga']],
            // 車型 — Mitsubishi
            ['option' => 'model', 'key' => 'outlander',     'sort_order' => 3, 'trans' => ['zh_Hant' => 'Outlander',     'en' => 'Outlander']],
            ['option' => 'model', 'key' => 'eclipse-cross', 'sort_order' => 4, 'trans' => ['zh_Hant' => 'Eclipse Cross', 'en' => 'Eclipse Cross']],
            // 車型 — Toyota
            ['option' => 'model', 'key' => 'corolla-cross', 'sort_order' => 5, 'trans' => ['zh_Hant' => 'Corolla Cross', 'en' => 'Corolla Cross']],
            ['option' => 'model', 'key' => 'rav4',          'sort_order' => 6, 'trans' => ['zh_Hant' => 'RAV4',          'en' => 'RAV4']],
            // 車型 — Honda
            ['option' => 'model', 'key' => 'cr-v',          'sort_order' => 7, 'trans' => ['zh_Hant' => 'CR-V',          'en' => 'CR-V']],
            ['option' => 'model', 'key' => 'civic',         'sort_order' => 8, 'trans' => ['zh_Hant' => 'Civic',         'en' => 'Civic']],

            // 車款 — Focus
            ['option' => 'trim', 'key' => 'focus-st-line',  'sort_order' => 1,  'trans' => ['zh_Hant' => 'ST-Line',  'en' => 'ST-Line']],
            ['option' => 'trim', 'key' => 'focus-titanium', 'sort_order' => 2,  'trans' => ['zh_Hant' => 'Titanium', 'en' => 'Titanium']],
            // 車款 — Kuga
            ['option' => 'trim', 'key' => 'kuga-st-line',   'sort_order' => 3,  'trans' => ['zh_Hant' => 'ST-Line X', 'en' => 'ST-Line X']],
            ['option' => 'trim', 'key' => 'kuga-vignale',   'sort_order' => 4,  'trans' => ['zh_Hant' => 'Vignale',   'en' => 'Vignale']],
            // 車款 — Outlander
            ['option' => 'trim', 'key' => 'outlander-elite',    'sort_order' => 5, 'trans' => ['zh_Hant' => '精英型', 'en' => 'Elite']],
            ['option' => 'trim', 'key' => 'outlander-flagship', 'sort_order' => 6, 'trans' => ['zh_Hant' => '旗艦型', 'en' => 'Flagship']],
            // 車款 — Eclipse Cross
            ['option' => 'trim', 'key' => 'eclipse-s-awc',    'sort_order' => 7,  'trans' => ['zh_Hant' => 'S-AWC',  'en' => 'S-AWC']],
            ['option' => 'trim', 'key' => 'eclipse-premium',  'sort_order' => 8,  'trans' => ['zh_Hant' => '菁英型', 'en' => 'Premium']],
            // 車款 — Corolla Cross
            ['option' => 'trim', 'key' => 'corolla-hybrid',   'sort_order' => 9,  'trans' => ['zh_Hant' => 'Hybrid 旗艦', 'en' => 'Hybrid']],
            ['option' => 'trim', 'key' => 'corolla-gasoline', 'sort_order' => 10, 'trans' => ['zh_Hant' => '汽油豪華',    'en' => 'Gasoline Premium']],
            // 車款 — RAV4
            ['option' => 'trim', 'key' => 'rav4-adventure', 'sort_order' => 11, 'trans' => ['zh_Hant' => 'Adventure',     'en' => 'Adventure']],
            ['option' => 'trim', 'key' => 'rav4-hybrid',    'sort_order' => 12, 'trans' => ['zh_Hant' => 'Hybrid 旗艦',  'en' => 'Hybrid']],
            // 車款 — CR-V
            ['option' => 'trim', 'key' => 'crv-vti-s', 'sort_order' => 13, 'trans' => ['zh_Hant' => 'VTi-S', 'en' => 'VTi-S']],
            ['option' => 'trim', 'key' => 'crv-s',     'sort_order' => 14, 'trans' => ['zh_Hant' => 'S',     'en' => 'S']],
            // 車款 — Civic
            ['option' => 'trim', 'key' => 'civic-vti-s', 'sort_order' => 15, 'trans' => ['zh_Hant' => 'VTi-S', 'en' => 'VTi-S']],
            ['option' => 'trim', 'key' => 'civic-rs',    'sort_order' => 16, 'trans' => ['zh_Hant' => 'RS',    'en' => 'RS']],

            // 排氣量
            ['option' => 'displacement', 'key' => '1500cc', 'sort_order' => 1, 'trans' => ['zh_Hant' => '1.5L', 'en' => '1.5L']],
            ['option' => 'displacement', 'key' => '1800cc', 'sort_order' => 2, 'trans' => ['zh_Hant' => '1.8L', 'en' => '1.8L']],
            ['option' => 'displacement', 'key' => '2000cc', 'sort_order' => 3, 'trans' => ['zh_Hant' => '2.0L', 'en' => '2.0L']],
            ['option' => 'displacement', 'key' => '2500cc', 'sort_order' => 4, 'trans' => ['zh_Hant' => '2.5L', 'en' => '2.5L']],
        ];

        $valueMap = [];

        foreach ($values as $v) {
            $option = $optionMap[$v['option']];
            $trans = $v['trans'];
            $key = $v['key'];

            $optionValue = $option->optionValues()->create([
                'code' => $key,
                'sort_order' => $v['sort_order'],
            ]);
            $optionValue->saveTranslations(array_map(fn ($name) => ['name' => $name], $trans));
            $valueMap[$key] = $optionValue;
        }

        // ── 選項值連動關係 ──

        $links = [
            // 廠牌 → 車型
            ['ford',       'focus'],
            ['ford',       'kuga'],
            ['mitsubishi', 'outlander'],
            ['mitsubishi', 'eclipse-cross'],
            ['toyota',     'corolla-cross'],
            ['toyota',     'rav4'],
            ['honda',      'cr-v'],
            ['honda',      'civic'],

            // 車型 → 車款
            ['focus',         'focus-st-line'],
            ['focus',         'focus-titanium'],
            ['kuga',          'kuga-st-line'],
            ['kuga',          'kuga-vignale'],
            ['outlander',     'outlander-elite'],
            ['outlander',     'outlander-flagship'],
            ['eclipse-cross', 'eclipse-s-awc'],
            ['eclipse-cross', 'eclipse-premium'],
            ['corolla-cross', 'corolla-hybrid'],
            ['corolla-cross', 'corolla-gasoline'],
            ['rav4',          'rav4-adventure'],
            ['rav4',          'rav4-hybrid'],
            ['cr-v',          'crv-vti-s'],
            ['cr-v',          'crv-s'],
            ['civic',         'civic-vti-s'],
            ['civic',         'civic-rs'],

            // 車款 → 排氣量
            ['focus-st-line',       '1500cc'],
            ['focus-titanium',      '2000cc'],
            ['kuga-st-line',        '1500cc'],
            ['kuga-vignale',        '2000cc'],
            ['outlander-elite',     '2500cc'],
            ['outlander-flagship',  '2500cc'],
            ['eclipse-s-awc',       '1500cc'],
            ['eclipse-premium',     '1500cc'],
            ['corolla-hybrid',      '1800cc'],
            ['corolla-gasoline',    '2000cc'],
            ['rav4-adventure',      '2000cc'],
            ['rav4-hybrid',         '2500cc'],
            ['crv-vti-s',           '1500cc'],
            ['crv-s',               '1500cc'],
            ['civic-vti-s',         '1500cc'],
            ['civic-rs',            '2000cc'],
        ];

        foreach ($links as [$parentKey, $childKey]) {
            if (isset($valueMap[$parentKey], $valueMap[$childKey])) {
                OptionValueLink::create([
                    'parent_option_value_id' => $valueMap[$parentKey]->id,
                    'child_option_value_id'  => $valueMap[$childKey]->id,
                ]);
            }
        }
    }
}
