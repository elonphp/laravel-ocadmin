<?php

namespace Database\Seeders;

use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValue;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductOption;
use App\Models\Catalog\ProductOptionValue;
use Illuminate\Database\Seeder;

/**
 * OpenCart 4 demo product_option + product_option_value 移植
 *
 * 來源：opencart4x.sql 的 oc_product_option（12 筆）+ oc_product_option_value（16 筆）
 * 目標：ctl_product_options + ctl_product_option_values
 *
 * 依賴：OpencartOptionSeeder（option / option_value）+ OpencartProductSeeder（product）已先跑
 *
 * 邊界 / 丟棄欄位：
 * - oc_product_option_value 不移植：points / points_prefix（user 政策：不收商品點數機制）
 *
 * FK 自然鍵反查：
 * - Product 用 model 欄位（Product 15 / Product 8 / Product 21 / Product 3）
 * - Option 用 code 欄位（oc_color / oc_size_radio / oc_size / oc_checkbox_demo / ...）
 * - OptionValue 用 code 欄位（oc_red / oc_blue / oc_small / oc_medium / ...）
 *
 * oc_product_option_id → 新 ID 對應在 run() 內動態建 map，供 product_option_value 使用
 */
class OpencartProductOptionSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. 預先載入 lookup map：減少 N+1 查詢
        // ============================================================
        $productByModel = Product::whereIn('model', ['Product 3', 'Product 8', 'Product 15', 'Product 21'])
            ->get()->keyBy('model');

        $optionByCode = Option::whereIn('code', [
            'oc_size_radio', 'oc_checkbox_demo', 'oc_text_demo', 'oc_color', 'oc_textarea_demo',
            'oc_file_demo', 'oc_date_demo', 'oc_time_demo', 'oc_datetime_demo', 'oc_size', 'oc_delivery_date',
        ])->get()->keyBy('code');

        $valueByCode = OptionValue::whereIn('code', [
            'oc_radio_small', 'oc_radio_medium', 'oc_radio_large',
            'oc_checkbox_1', 'oc_checkbox_2', 'oc_checkbox_3', 'oc_checkbox_4',
            'oc_red', 'oc_blue', 'oc_green', 'oc_yellow',
            'oc_small', 'oc_medium', 'oc_large',
        ])->get()->keyBy('code');

        // ============================================================
        // 2. ctl_product_options：12 筆
        //    oc_product_option_id → 新 ProductOption 實例的 map
        // ============================================================
        $productOptions = [
            // oc_id, product_model, option_code, value, required
            [208, 'Product 15', 'oc_text_demo',      'test',              true],
            [209, 'Product 15', 'oc_textarea_demo',  '',                  true],
            [217, 'Product 15', 'oc_color',          '',                  true],
            [218, 'Product 15', 'oc_size_radio',     '',                  true],
            [219, 'Product 15', 'oc_date_demo',      '2011-02-20',        true],
            [220, 'Product 15', 'oc_datetime_demo',  '2011-02-20 22:25',  true],
            [221, 'Product 15', 'oc_time_demo',      '22:25',             true],
            [222, 'Product 15', 'oc_file_demo',      '',                  true],
            [223, 'Product 15', 'oc_checkbox_demo',  '',                  true],
            [224, 'Product 8',  'oc_size',           '',                  true],
            [225, 'Product 21', 'oc_delivery_date',  '2011-04-22',        true],
            [226, 'Product 3',  'oc_color',          '',                  true],
        ];

        $poMap = []; // oc_product_option_id → new ProductOption->id
        foreach ($productOptions as [$ocId, $productModel, $optionCode, $value, $required]) {
            $product = $productByModel[$productModel] ?? null;
            $option = $optionByCode[$optionCode] ?? null;
            if (!$product || !$option) {
                throw new \RuntimeException("無法找到 product='$productModel' / option='$optionCode'（oc_id=$ocId）");
            }
            $po = ProductOption::create([
                'product_id' => $product->id,
                'option_id'  => $option->id,
                'value'      => $value,
                'required'   => $required,
            ]);
            $poMap[$ocId] = $po->id;
        }

        // ============================================================
        // 3. ctl_product_option_values：16 筆
        //    丟掉 points / points_prefix
        // ============================================================
        $productOptionValues = [
            // oc_pov_id, oc_po_id, product_model, option_code, value_code, quantity, subtract, price, price_prefix, weight, weight_prefix
            [1,  217, 'Product 15', 'oc_color',     'oc_green',         100,  false, 1.0000,  '+', 1.00000000,  '+'],
            [2,  217, 'Product 15', 'oc_color',     'oc_yellow',        200,  true,  2.0000,  '+', 2.00000000,  '+'],
            [3,  217, 'Product 15', 'oc_color',     'oc_blue',          300,  false, 3.0000,  '+', 3.00000000,  '+'],
            [4,  217, 'Product 15', 'oc_color',     'oc_red',           92,   true,  4.0000,  '+', 4.00000000,  '+'],
            [5,  218, 'Product 15', 'oc_size_radio','oc_radio_small',   96,   true,  10.0000, '+', 10.00000000, '+'],
            [6,  218, 'Product 15', 'oc_size_radio','oc_radio_medium',  146,  true,  20.0000, '+', 20.00000000, '+'],
            [7,  218, 'Product 15', 'oc_size_radio','oc_radio_large',   300,  true,  30.0000, '+', 30.00000000, '+'],
            [8,  223, 'Product 15', 'oc_checkbox_demo', 'oc_checkbox_1', 48,   true,  10.0000, '+', 10.00000000, '+'],
            [9,  223, 'Product 15', 'oc_checkbox_demo', 'oc_checkbox_2', 194,  true,  20.0000, '+', 20.00000000, '+'],
            [10, 223, 'Product 15', 'oc_checkbox_demo', 'oc_checkbox_3', 2696, true,  30.0000, '+', 30.00000000, '+'],
            [11, 223, 'Product 15', 'oc_checkbox_demo', 'oc_checkbox_4', 3998, true,  40.0000, '+', 40.00000000, '+'],
            [12, 224, 'Product 8',  'oc_size',      'oc_small',         0,    true,  5.0000,  '+', 0.00000000,  '+'],
            [13, 224, 'Product 8',  'oc_size',      'oc_medium',        10,   true,  10.0000, '+', 0.00000000,  '+'],
            [14, 224, 'Product 8',  'oc_size',      'oc_large',         15,   true,  15.0000, '+', 0.00000000,  '+'],
            [15, 226, 'Product 3',  'oc_color',     'oc_red',           2,    true,  0.0000,  '+', 0.00000000,  '+'],
            [16, 226, 'Product 3',  'oc_color',     'oc_blue',          5,    true,  0.0000,  '+', 0.00000000,  '+'],
        ];

        foreach ($productOptionValues as [$ocId, $ocPoId, $productModel, $optionCode, $valueCode, $quantity, $subtract, $price, $pricePrefix, $weight, $weightPrefix]) {
            $product = $productByModel[$productModel] ?? null;
            $option = $optionByCode[$optionCode] ?? null;
            $optionValue = $valueByCode[$valueCode] ?? null;
            $poId = $poMap[$ocPoId] ?? null;
            if (!$product || !$option || !$optionValue || !$poId) {
                throw new \RuntimeException("無法找到 lookup（oc_pov_id=$ocId）：product=$productModel / option=$optionCode / value=$valueCode / po_id=$ocPoId");
            }
            ProductOptionValue::create([
                'product_option_id' => $poId,
                'product_id'        => $product->id,
                'option_id'         => $option->id,
                'option_value_id'   => $optionValue->id,
                'quantity'          => $quantity,
                'subtract'          => $subtract,
                'price'             => $price,
                'price_prefix'      => $pricePrefix,
                'weight'            => $weight,
                'weight_prefix'     => $weightPrefix,
            ]);
        }
    }
}
