<?php
/**
 * 一次性抽取腳本：從 opencart4x.sql 抽出 oc_product + oc_product_description，
 * 產出 database/seeders/data/opencart_products.php fixture（PHP array 形式）。
 *
 * 用法：php database/seeders/data/_extract_opencart_products.php
 * 跑完一次後產出 fixture 即可刪除本腳本。
 */

$sqlPath = 'D:/Codes/PHP/Opencart/opencart4x/htdocs/opencart4x.sql';
$outPath = __DIR__ . '/opencart_products.php';

if (!is_file($sqlPath)) {
    fwrite(STDERR, "找不到 SQL dump: $sqlPath\n");
    exit(1);
}

$sql = file_get_contents($sqlPath);
if ($sql === false) {
    fwrite(STDERR, "無法讀取 SQL dump\n");
    exit(1);
}

/**
 * 解析 MySQL dump 內單引號字串：值可能含跳脫的 \', \", \\, \r, \n, \t
 * 對 mysqldump 預設格式有效。
 */
function parseSqlString(string $raw): string
{
    // 預期 $raw 已是去掉外層單引號的內容
    $out = '';
    $len = strlen($raw);
    for ($i = 0; $i < $len; $i++) {
        $c = $raw[$i];
        if ($c === '\\' && $i + 1 < $len) {
            $next = $raw[$i + 1];
            $i++;
            switch ($next) {
                case 'n': $out .= "\n"; break;
                case 'r': $out .= "\r"; break;
                case 't': $out .= "\t"; break;
                case '\\': $out .= '\\'; break;
                case '\'': $out .= '\''; break;
                case '"': $out .= '"'; break;
                case '0': $out .= "\0"; break;
                case 'Z': $out .= chr(26); break;
                default: $out .= $next; break;
            }
        } else {
            $out .= $c;
        }
    }
    return $out;
}

/**
 * 解析一筆 VALUES (...) tuple 為陣列。處理 NULL / 數字 / 'string'（含 escape）
 */
function parseTuple(string $tuple): array
{
    $vals = [];
    $i = 0;
    $len = strlen($tuple);
    while ($i < $len) {
        // skip whitespace + 逗號
        while ($i < $len && (ctype_space($tuple[$i]) || $tuple[$i] === ',')) {
            $i++;
        }
        if ($i >= $len) break;

        $c = $tuple[$i];
        if ($c === '\'') {
            // 找對應的結束單引號（考慮 escape）
            $i++;
            $start = $i;
            $raw = '';
            while ($i < $len) {
                if ($tuple[$i] === '\\' && $i + 1 < $len) {
                    $raw .= $tuple[$i] . $tuple[$i + 1];
                    $i += 2;
                    continue;
                }
                if ($tuple[$i] === '\'') {
                    break;
                }
                $raw .= $tuple[$i];
                $i++;
            }
            $vals[] = parseSqlString($raw);
            $i++; // skip closing quote
        } elseif (substr($tuple, $i, 4) === 'NULL') {
            $vals[] = null;
            $i += 4;
        } else {
            // 數字或裸字
            $start = $i;
            while ($i < $len && $tuple[$i] !== ',' && !ctype_space($tuple[$i])) {
                $i++;
            }
            $token = substr($tuple, $start, $i - $start);
            $vals[] = is_numeric($token) ? (str_contains($token, '.') ? (float)$token : (int)$token) : $token;
        }
    }
    return $vals;
}

// ============================================================
// 1. 抽 oc_product
// ============================================================
$productCols = ['product_id', 'master_id', 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'variant', 'override',
                'quantity', 'stock_status_id', 'image', 'manufacturer_id', 'shipping', 'price', 'points', 'tax_class_id',
                'date_available', 'weight', 'weight_class_id', 'length', 'width', 'height', 'length_class_id',
                'subtract', 'minimum', 'rating', 'sort_order', 'status', 'date_added', 'date_modified'];

$products = [];
preg_match_all("/INSERT INTO `oc_product` VALUES \\((.+?)\\);\r?\n/s", $sql, $matches);
foreach ($matches[1] as $tuple) {
    $vals = parseTuple($tuple);
    if (count($vals) !== count($productCols)) {
        fwrite(STDERR, "Product tuple 欄數不符 (got " . count($vals) . "): $tuple\n");
        continue;
    }
    $row = array_combine($productCols, $vals);
    $products[$row['product_id']] = $row;
}
echo "抽到 " . count($products) . " 筆 product\n";

// ============================================================
// 2. 抽 oc_product_description（只取 language_id=1）
// ============================================================
$descCols = ['product_id', 'language_id', 'name', 'description', 'tag', 'meta_title', 'meta_description', 'meta_keyword'];

$descriptions = [];
preg_match_all("/INSERT INTO `oc_product_description` VALUES \\((.+?)\\);\r?\n/s", $sql, $matches);
foreach ($matches[1] as $tuple) {
    $vals = parseTuple($tuple);
    if (count($vals) !== count($descCols)) {
        fwrite(STDERR, "Description tuple 欄數不符: $tuple\n");
        continue;
    }
    $row = array_combine($descCols, $vals);
    if ($row['language_id'] !== 1) continue;
    $descriptions[$row['product_id']] = $row;
}
echo "抽到 " . count($descriptions) . " 筆 description (language_id=1)\n";

// ============================================================
// 3. 合併輸出
// ============================================================
$out = [];
foreach ($products as $pid => $p) {
    $d = $descriptions[$pid] ?? null;
    // description 是 entity-encoded HTML，這裡 decode 一次（&lt;p&gt; → <p>）
    $description = $d ? html_entity_decode($d['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    $name = $d ? html_entity_decode($d['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    $metaTitle = $d ? trim(html_entity_decode($d['meta_title'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
    $metaDesc = $d ? html_entity_decode($d['meta_description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    $metaKey = $d ? html_entity_decode($d['meta_keyword'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';

    $out[] = [
        'oc_id'            => $p['product_id'],
        'model'            => $p['model'],
        'image'            => $p['image'] !== '' ? $p['image'] : null,
        'price'            => (float)$p['price'],
        'quantity'         => (int)$p['quantity'],
        'minimum'          => (int)$p['minimum'],
        'subtract'         => (bool)$p['subtract'],
        'shipping'         => (bool)$p['shipping'],
        'status'           => (bool)$p['status'],
        'sort_order'       => (int)$p['sort_order'],
        'date_added'       => $p['date_added'],
        'date_modified'    => $p['date_modified'],
        'name'             => $name,
        'description'      => $description,
        'meta_title'       => $metaTitle !== '' ? $metaTitle : null,
        'meta_description' => $metaDesc !== '' ? $metaDesc : null,
        'meta_keyword'     => $metaKey !== '' ? $metaKey : null,
    ];
}

// 依 oc_id 排序輸出
usort($out, fn($a, $b) => $a['oc_id'] <=> $b['oc_id']);

// 輸出 PHP fixture（用 var_export）
$header = "<?php\n/**\n * 由 _extract_opencart_products.php 從 opencart4x.sql 自動抽取產生。\n * 來源時間：" . date('Y-m-d H:i:s') . "\n * 不要手改本檔；要更新 → 重跑抽取腳本。\n */\n\nreturn ";
$content = $header . var_export($out, true) . ";\n";

file_put_contents($outPath, $content);
echo "已寫入 " . $outPath . " (" . count($out) . " 筆 product)\n";
echo "檔案大小：" . number_format(filesize($outPath)) . " bytes\n";
