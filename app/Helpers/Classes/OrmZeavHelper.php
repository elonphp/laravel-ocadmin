<?php

namespace App\Helpers\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * OrmZeavHelper - ZEAV 模式查詢輔助類別
 *
 * translation_mode = 3 時使用
 * 自動 JOIN zeav_xxx 快取表進行查詢、篩選、排序
 *
 * 資料表關係：
 * - users (主表)
 * - user_metas (EAV 真實資料)
 * - zeav_users (快取表，可由 user_metas 重建)
 */
class OrmZeavHelper
{
    /**
     * 準備查詢（自動 JOIN zeav 快取表）
     */
    public static function prepare(Builder $query, array &$params): Builder
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $tableColumns = OrmHelper::getTableColumns($table);
        $translationKeys = $model->translation_keys ?? [];
        $locale = $params['locale'] ?? app()->getLocale();

        // 是否需要 JOIN zeav 表
        $needsZeav = self::needsZeavJoin($params, $translationKeys);

        if ($needsZeav && !empty($translationKeys)) {
            self::joinZeav($query, $table, $locale);
        }

        // 處理篩選條件
        self::applyFilters($query, $params, $table, $tableColumns, $translationKeys, $needsZeav);

        // 預設 is_active = 1
        self::applyIsActiveFilter($query, $params, $table, $tableColumns);

        return $query;
    }

    /**
     * 判斷是否需要 JOIN zeav 表
     */
    protected static function needsZeavJoin(array $params, array $translationKeys): bool
    {
        if (empty($translationKeys)) {
            return false;
        }

        // 檢查篩選條件是否包含翻譯欄位
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') continue;
            if (!str_starts_with($key, 'filter_') && !str_starts_with($key, 'equal_')) continue;

            $column = preg_replace('/^(filter_|equal_)/', '', $key);
            if (in_array($column, $translationKeys)) {
                return true;
            }
        }

        // 檢查排序是否為翻譯欄位
        $sort = $params['sort'] ?? 'id';
        if (in_array($sort, $translationKeys)) {
            return true;
        }

        return false;
    }

    /**
     * JOIN zeav 快取表
     */
    protected static function joinZeav(Builder $query, string $table, string $locale): void
    {
        $zeavTable = self::getZeavTableName($table);
        $foreignKey = self::getForeignKeyName($table);

        $query->leftJoin("{$zeavTable} as zt", function ($join) use ($table, $foreignKey, $locale) {
            $join->on("{$table}.id", '=', "zt.{$foreignKey}")
                 ->where('zt.locale', $locale);
        });

        // 確保 SELECT 主表所有欄位（避免欄位名衝突）
        if (!$query->getQuery()->columns) {
            $query->select("{$table}.*");
        }
    }

    /**
     * 套用篩選條件
     */
    protected static function applyFilters(
        Builder $query,
        array &$params,
        string $table,
        array $tableColumns,
        array $translationKeys,
        bool $zeavJoined
    ): void {
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') continue;
            if (!str_starts_with($key, 'filter_') && !str_starts_with($key, 'equal_')) continue;

            $column = preg_replace('/^(filter_|equal_)/', '', $key);

            // 主表欄位
            if (in_array($column, $tableColumns) && !in_array($column, $translationKeys)) {
                OrmHelper::filterOrEqualColumn($query, $key, $value);
            }
            // 翻譯欄位（zeav）
            elseif (in_array($column, $translationKeys) && $zeavJoined) {
                self::applyZeavFilter($query, $key, "zt.{$column}", $value);
            }
        }
    }

    /**
     * 套用 is_active 篩選
     */
    protected static function applyIsActiveFilter(
        Builder $query,
        array &$params,
        string $table,
        array $tableColumns
    ): void {
        if (!in_array('is_active', $tableColumns)) {
            return;
        }

        if (!isset($params['equal_is_active'])) {
            $query->where("{$table}.is_active", 1);
        } elseif ($params['equal_is_active'] === '*') {
            // 顯示全部，不加條件
        } else {
            $query->where("{$table}.is_active", (int) $params['equal_is_active']);
        }
    }

    /**
     * 套用 zeav 欄位的篩選條件
     */
    protected static function applyZeavFilter(Builder $query, string $key, string $column, $value): void
    {
        $value = trim($value);

        // equal_ 查詢
        if (str_starts_with($key, 'equal_')) {
            if ($value === '*') {
                return;
            }
            $query->where($column, $value);
            return;
        }

        // filter_ 查詢
        if (strlen($value) == 0) {
            return;
        }

        // 跳脫特殊字元
        $escapeChars = ['(', ')', '+'];
        foreach ($escapeChars as $char) {
            if (str_contains($value, $char)) {
                $value = str_replace($char, '\\' . $char, $value);
            }
        }

        // *foo woo* => foo woo
        if (str_starts_with($value, '*') && str_ends_with($value, '*')) {
            $value = substr($value, 1, -1);
        }

        // 檢查運算子
        $operators = ['=', '<', '>', '*'];
        $hasOperator = false;
        foreach ($operators as $op) {
            if (str_starts_with($value, $op) || str_ends_with($value, '*')) {
                $hasOperator = true;
                break;
            }
        }

        // 無運算子：預設 REGEXP
        if (!$hasOperator) {
            $value = str_replace(' ', '(.*)', $value);
            $query->where($column, 'REGEXP', $value);
            return;
        }

        // 處理各種運算子
        if ($value === '=') {
            // 空值或 null
            $query->where(fn($q) => $q->whereNull($column)->orWhere($column, ''));
        } elseif (str_starts_with($value, '=') && strlen($value) > 1) {
            // 完全相等
            $query->where($column, substr($value, 1));
        } elseif ($value === '<>') {
            // 非空
            $query->where(fn($q) => $q->whereNotNull($column)->where($column, '<>', ''));
        } elseif (str_starts_with($value, '<>') && strlen($value) > 2) {
            // 不等於
            $query->where($column, '<>', substr($value, 2));
        } elseif (str_starts_with($value, '<') && strlen($value) > 1) {
            // 小於
            $query->where($column, '<', substr($value, 1));
        } elseif (str_starts_with($value, '>') && strlen($value) > 1) {
            // 大於
            $query->where($column, '>', substr($value, 1));
        } elseif (str_starts_with($value, '*') && !str_ends_with($value, '*')) {
            // 結尾匹配
            $pattern = '(.*)' . str_replace(' ', '(.*)', substr($value, 1)) . '$';
            $query->where($column, 'REGEXP', $pattern);
        } elseif (!str_starts_with($value, '*') && str_ends_with($value, '*')) {
            // 開頭匹配
            $pattern = '^' . str_replace(' ', '(.*)', substr($value, 0, -1)) . '(.*)';
            $query->where($column, 'REGEXP', $pattern);
        }
    }

    /**
     * 取得結果（支援排序和分頁）
     */
    public static function getResult(Builder $query, array $params): mixed
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $translationKeys = $model->translation_keys ?? [];
        $locale = $params['locale'] ?? app()->getLocale();

        // 處理排序
        $sort = $params['sort'] ?? 'id';
        $order = strtoupper($params['order'] ?? 'desc');

        if (in_array($sort, $translationKeys)) {
            // 翻譯欄位：使用 zeav 別名
            $query->orderBy("zt.{$sort}", $order);
        } else {
            // 主表欄位
            $query->orderBy("{$table}.{$sort}", $order);
        }

        // 取得單筆
        if (!empty($params['first'])) {
            if (empty($params['pluck'])) {
                return $query->first();
            }
            return $query->pluck($params['pluck'])->first();
        }

        // 分頁設定
        $perPage = $params['per_page'] ?? $params['limit'] ?? config('settings.config_admin_pagination_limit', 10);
        $perPage = (int) $perPage;
        $pagination = $params['pagination'] ?? true;

        // 取得結果
        if ($pagination && $perPage > 0) {
            $result = $query->paginate($perPage);
        } elseif ($pagination && $perPage == 0) {
            $result = $query->paginate($query->count() ?: 1);
        } elseif (!$pagination && $perPage != 0) {
            $result = $query->limit($perPage)->get();
        } else {
            $result = $query->get();
        }

        // Pluck / KeyBy
        if (!empty($params['pluck'])) {
            $result = $result->pluck($params['pluck']);
        }
        if (!empty($params['keyBy'])) {
            $result = $result->keyBy($params['keyBy']);
        }

        return $result;
    }

    /**
     * 取得 zeav 快取表名稱
     * products → zeav_products
     */
    public static function getZeavTableName(string $table): string
    {
        return 'zeav_' . $table;
    }

    /**
     * 取得 metas 表名稱
     * products → product_metas
     */
    public static function getMetaTableName(string $table): string
    {
        return Str::singular($table) . '_metas';
    }

    /**
     * 取得外鍵名稱
     * products → product_id
     */
    public static function getForeignKeyName(string $table): string
    {
        return Str::singular($table) . '_id';
    }

    /**
     * 顯示 SQL 內容（debug 用）
     */
    public static function showSqlContent(Builder $query, bool $exit = true): ?string
    {
        return OrmHelper::showSqlContent($query, $exit ? 1 : 0);
    }
}
