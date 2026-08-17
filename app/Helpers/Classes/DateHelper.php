<?php

namespace App\Helpers\Classes;

use Illuminate\Database\Eloquent\Builder;

/**
 * 日期相關工具
 *
 * 主要提供：
 *   - parse()              寬鬆解析多種日期字串為 Y-m-d
 *   - applySmartFilter()   把使用者輸入的「智慧日期」字串套到 Eloquent query
 *
 * 智慧日期語法（applySmartFilter / 篩選欄位 placeholder 用）：
 *   2026-04-25 / 20260425           單日
 *   20260401-20260430                區間
 *   2026-04-01-2026-04-30            區間（dash 分隔的長格式）
 *   >20260101 / >=20260101           大於 / 大於等於
 *   <20260101 / <=20260101           小於 / 小於等於
 *
 * 設計原則：
 *   - 解析失敗一律 silent no-op，不擲例外、不污染 query。需要嚴格校驗請在
 *     Validation 層處理。
 *   - 內部一律走 Eloquent whereDate / whereBetween，不字串拼 SQL（避免 injection）。
 */
class DateHelper
{
    /**
     * 解析寬鬆日期字串為 Y-m-d
     *
     * 接受：
     *   2026-04-25 / 2026/04/25       (4 位數年、有分隔符)
     *   26-04-25   / 26/04/25         (2 位數年、有分隔符 → 自動補 20YY)
     *   20260425                       (8 位數連續)
     *   260425                         (6 位數連續 → 自動補 20YY)
     *
     * @return string|null 失敗回 null（含日期不合法、月日越界）
     */
    public static function parse(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        $s = trim($input);
        if ($s === '') {
            return null;
        }

        // 統一分隔符
        $s = str_replace('/', '-', $s);

        $year = $month = $day = null;

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $s, $m)) {
            [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/^(\d{2})-(\d{1,2})-(\d{1,2})$/', $s, $m)) {
            [$year, $month, $day] = [(int) $m[1] + 2000, (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $s, $m)) {
            [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $s, $m)) {
            [$year, $month, $day] = [(int) $m[1] + 2000, (int) $m[2], (int) $m[3]];
        } else {
            return null;
        }

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * 把使用者輸入的智慧日期字串套到 query
     *
     * 語法見 class docblock。
     * 解析失敗則 silent no-op（不套任何條件）。
     */
    public static function applySmartFilter(Builder $query, string $column, ?string $input): void
    {
        if ($input === null) {
            return;
        }
        $s = trim($input);
        if ($s === '') {
            return;
        }

        // 運算子前綴：>= / <= / > / < / =（長前綴先試，避免 > 誤吞 >=）
        if (preg_match('/^(>=|<=|>|<|=)\s*(.+)$/', $s, $m)) {
            $date = self::parse($m[2]);
            if (! $date) {
                return;
            }
            $query->whereDate($column, $m[1], $date);
            return;
        }

        // 區間：兩個日期 token 用單一 dash 連接
        $datePart   = '(?:\d{4}-\d{1,2}-\d{1,2}|\d{2}-\d{1,2}-\d{1,2}|\d{8}|\d{6})';
        $normalized = str_replace('/', '-', $s);
        if (preg_match("/^({$datePart})-({$datePart})$/", $normalized, $m)) {
            $start = self::parse($m[1]);
            $end   = self::parse($m[2]);
            if ($start && $end) {
                $query->whereDate($column, '>=', $start)
                      ->whereDate($column, '<=', $end);
                return;
            }
        }

        // 單日
        $date = self::parse($s);
        if ($date) {
            $query->whereDate($column, $date);
        }
    }
}
