<?php

namespace Database\Seeders;

use App\Models\Catalog\Product;
use Illuminate\Database\Seeder;

/**
 * OpenCart 4 demo product 移植
 *
 * 來源：opencart4x.sql 的 oc_product + oc_product_description（共 19 筆 product，oc_id 28-36 / 40-49）
 * 資料 fixture：database/seeders/data/opencart_products.php（由 _extract_opencart_products.php 一次性抽取產出）
 * 目標：ctl_products + ctl_product_translations
 *
 * 邊界 / 丟棄欄位（OpenCart 有但本系統 schema 無 / 不要的）：
 * - oc_product 不移植：master_id / sku / upc / ean / jan / isbn / mpn / location / variant / override
 *                    / stock_status_id / manufacturer_id / tax_class_id / weight* / length* / width / height
 *                    / *_class_id / date_available / points / rating
 * - oc_product_description 不移植：tag
 * - description 是 entity-encoded HTML，已在 fixture 抽取時 html_entity_decode 一次（&lt;p&gt; → <p>）
 * - created_at / updated_at 沿用 OpenCart 的 date_added / date_modified（呈現原始時間軸）
 * - zh_Hant 翻譯：name 對 brand 類產品保留英文；description / meta_* 不譯，runtime fallback 到 en
 *
 * oc_product_id → model 自然鍵（給 OpencartProductOptionSeeder 反查 Product::where('model', ...)）
 *   28→'Product 1'  29→'Product 2'  30→'Product 3'  31→'Product 4'  32→'Product 5'
 *   33→'Product 6'  34→'Product 7'  35→'Product 8'  36→'Product 9'  40→'product 11'
 *   41→'Product 14' 42→'Product 15' 43→'Product 16' 44→'Product 17' 45→'Product 18'
 *   46→'Product 19' 47→'Product 21' 48→'product 20' 49→'SAM1'
 */
class OpencartProductSeeder extends Seeder
{
    /**
     * 對 "Product N" 系列產品提供 zh_Hant name 翻譯；
     * brand 類（HTC / iPod / MacBook 等）保留英文不譯。
     */
    private array $zhNameOverride = [
        'Product 8' => '商品 8',
    ];

    public function run(): void
    {
        $rows = require __DIR__ . '/data/opencart_products.php';

        foreach ($rows as $row) {
            $product = new Product();
            $product->fill([
                'model'      => $row['model'],
                'image'      => $row['image'],
                'price'      => $row['price'],
                'quantity'   => $row['quantity'],
                'minimum'    => $row['minimum'],
                'subtract'   => $row['subtract'],
                'shipping'   => $row['shipping'],
                'status'     => $row['status'],
                'sort_order' => $row['sort_order'],
            ]);
            // 顯式覆寫 timestamps（在 save 前 set，Eloquent 不會 overwrite）
            $product->created_at = $row['date_added'];
            $product->updated_at = $row['date_modified'];
            $product->save();

            $product->saveTranslations([
                'en' => [
                    'name'             => $row['name'],
                    'description'      => $row['description'],
                    'meta_title'       => $row['meta_title'],
                    'meta_description' => $row['meta_description'],
                    'meta_keyword'     => $row['meta_keyword'],
                ],
                'zh_Hant' => [
                    'name' => $this->zhNameOverride[$row['name']] ?? $row['name'],
                ],
            ]);
        }
    }
}
