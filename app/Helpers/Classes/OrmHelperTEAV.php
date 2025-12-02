<?php

namespace App\Helpers\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * OrmHelperTEAV - EAV 模式查詢輔助類別
 *
 * 支援兩種模式：
 * - mode=2：純 EAV，翻譯欄位用 whereHas 查詢 xxx_metas
 * - mode=3：EAV + sysdata，翻譯欄位透過跨庫 JOIN sysdata.xxx_translations 查詢
 *
 * 核心功能：
 * 1. 自動識別主表欄位和 EAV 欄位
 * 2. 支援排序、篩選、分頁
 * 3. 沿用 OrmHelper 的 filter_/equal_ 語法
 */
class OrmHelperTEAV
{
    /**
     * 取得 sysdata 資料庫名稱
     */
    protected static function getSysDatabase(): string
    {
        return config('database.connections.sysdata.database');
    }

    /**
     * 準備查詢（自動處理主表欄位和翻譯欄位）
     */
    public static function prepare(Builder $query, array &$params): Builder
    {
        $model = $query->getModel();
        $mode = $model->translation_mode ?? OrmHelper::TRANSLATION_MODE_EAV_SYSDATA;
        $table = $model->getTable();
        $tableColumns = OrmHelper::getTableColumns($table);
        $translationKeys = $model->translation_keys ?? [];
        $locale = $params['locale'] ?? app()->getLocale();

        // 是否需要處理翻譯欄位
        $needsTranslation = false;
        $translationFilters = [];

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') continue;
            if (!str_starts_with($key, 'filter_') && !str_starts_with($key, 'equal_')) continue;

            $column = preg_replace('/^(filter_|equal_)/', '', $key);

            // 主表欄位：直接處理
            if (in_array($column, $tableColumns) && !in_array($column, $translationKeys)) {
                OrmHelper::filterOrEqualColumn($query, $key, $value);
            }
            // 翻譯欄位：收集起來
            elseif (in_array($column, $translationKeys)) {
                $needsTranslation = true;
                $translationFilters[$key] = $value;
            }
        }

        // 根據 mode 處理翻譯欄位
        if ($mode === OrmHelper::TRANSLATION_MODE_EAV) {
            // mode=2：純 EAV，用 whereHas 查詢 metas
            self::applyEavFilters($query, $model, $translationFilters, $locale);
        } else {
            // mode=3：EAV + sysdata，JOIN translations 表
            if ($needsTranslation || self::needsTranslationSort($params, $translationKeys)) {
                self::joinTranslations($query, $table, $locale);

                // 處理翻譯欄位的篩選條件
                foreach ($translationFilters as $key => $value) {
                    $column = preg_replace('/^(filter_|equal_)/', '', $key);
                    self::applyTranslationFilter($query, $key, "pt.{$column}", $value);
                }
            }
        }

        // 預設 is_active = 1
        if (!isset($params['equal_is_active']) && in_array('is_active', $tableColumns)) {
            $query->where("{$table}.is_active", 1);
        } elseif (($params['equal_is_active'] ?? '') === '*') {
            // 顯示全部，不加條件
        } elseif (isset($params['equal_is_active'])) {
            $query->where("{$table}.is_active", (int) $params['equal_is_active']);
        }

        return $query;
    }

    /**
     * mode=2：純 EAV 模式，用 whereHas 查詢 metas
     */
    protected static function applyEavFilters(Builder $query, $model, array $filters, string $locale): void
    {
        if (empty($filters)) {
            return;
        }

        $metaKeys = $model->meta_keys ?? [];

        foreach ($filters as $key => $value) {
            $column = preg_replace('/^(filter_|equal_)/', '', $key);
            $metaKeyId = $metaKeys[$column] ?? null;

            if (!$metaKeyId) continue;

            $query->whereHas('metas', function ($q) use ($key, $value, $metaKeyId, $locale) {
                $q->where('meta_key_id', $metaKeyId)
                  ->where('locale', $locale);

                // 套用篩選條件到 meta_value
                self::applyTranslationFilter($q, $key, 'meta_value', $value);
            });
        }
    }

    /**
     * mode=3：JOIN sysdata translations 表
     */
    protected static function joinTranslations(Builder $query, string $table, string $locale): void
    {
        $sysDb = self::getSysDatabase();
        $translationTable = self::getTranslationTableName($table);
        $foreignKey = self::getForeignKeyName($table);

        $query->leftJoin("{$sysDb}.{$translationTable} as pt", function ($join) use ($table, $foreignKey, $locale) {
            $join->on("{$table}.id", '=', "pt.{$foreignKey}")
                 ->where('pt.locale', $locale);
        });

        // 確保 SELECT 主表所有欄位
        if (!$query->getQuery()->columns) {
            $query->select("{$table}.*");
        }
    }

    /**
     * 檢查是否需要 JOIN translations（用於排序）
     */
    protected static function needsTranslationSort(array $params, array $translationKeys): bool
    {
        $sort = $params['sort'] ?? 'id';
        return in_array($sort, $translationKeys);
    }

    /**
     * 套用翻譯欄位的篩選條件
     */
    protected static function applyTranslationFilter(Builder $query, string $key, string $column, $value): void
    {
        $value = trim($value);

        if (str_starts_with($key, 'equal_')) {
            // '*' 代表不限制此欄位
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

        // escapes Ex. phone number (123)456789 => \(123\)456789
        $arr = ['(', ')', '+'];
        foreach ($arr as $symble) {
            if (str_contains($value, $symble)) {
                $value = str_replace($symble, '\\' . $symble, $value);
            }
        }

        $operators = ['=', '<', '>', '*'];

        // *foo woo* => foo woo
        if (str_starts_with($value, '*') && str_ends_with($value, '*')) {
            $value = substr($value, 1, -1);
        }

        $hasOperator = false;
        foreach ($operators as $op) {
            if (str_starts_with($value, $op) || str_ends_with($value, '*')) {
                $hasOperator = true;
                break;
            }
        }

        if (!$hasOperator) {
            // 預設 REGEXP: 'foo woo' => 'foo(.*)woo'
            $value = str_replace(' ', '(.*)', $value);
            $query->where($column, 'REGEXP', $value);
            return;
        }

        // 處理運算子
        // '=' Empty or null
        if ($value === '=') {
            $query->where(function ($q) use ($column) {
                $q->whereNull($column)->orWhere($column, '');
            });
        }
        // '=foo woo' Completely Equal 'foo woo'
        elseif (str_starts_with($value, '=') && strlen($value) > 1) {
            $query->where($column, substr($value, 1));
        }
        // '<>' Not empty or not null
        elseif ($value === '<>') {
            $query->where(function ($q) use ($column) {
                $q->whereNotNull($column)->where($column, '<>', '');
            });
        }
        // '<>foo woo' Not equal 'foo woo'
        elseif (str_starts_with($value, '<>') && strlen($value) > 2) {
            $query->where($column, '<>', substr($value, 2));
        }
        // '<123' Smaller than 123
        elseif (str_starts_with($value, '<') && strlen($value) > 1) {
            $query->where($column, '<', substr($value, 1));
        }
        // '>123' bigger than 123
        elseif (str_starts_with($value, '>') && strlen($value) > 1) {
            $query->where($column, '>', substr($value, 1));
        }
        // '*foo woo' ends with
        elseif (str_starts_with($value, '*') && !str_ends_with($value, '*')) {
            $pattern = '(.*)' . str_replace(' ', '(.*)', substr($value, 1)) . '$';
            $query->where($column, 'REGEXP', $pattern);
        }
        // 'foo woo*' starts with
        elseif (!str_starts_with($value, '*') && str_ends_with($value, '*')) {
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
        $mode = $model->translation_mode ?? OrmHelper::TRANSLATION_MODE_EAV_SYSDATA;
        $table = $model->getTable();
        $translationKeys = $model->translation_keys ?? [];

        // 處理排序
        $sort = $params['sort'] ?? 'id';
        $order = strtoupper($params['order'] ?? 'desc');
        $locale = $params['locale'] ?? app()->getLocale();

        // 如果排序欄位是翻譯欄位
        if (in_array($sort, $translationKeys)) {
            if ($mode === OrmHelper::TRANSLATION_MODE_EAV) {
                // mode=2：純 EAV，用子查詢排序
                $metaKeys = $model->meta_keys ?? [];
                $metaKeyId = $metaKeys[$sort] ?? null;

                if ($metaKeyId) {
                    $metaTable = self::getMetaTableName($table);
                    $foreignKey = self::getForeignKeyName($table);

                    $query->orderByRaw(
                        "(SELECT meta_value FROM {$metaTable}
                          WHERE {$metaTable}.{$foreignKey} = {$table}.id
                          AND {$metaTable}.meta_key_id = ?
                          AND {$metaTable}.locale = ?
                          LIMIT 1) {$order}",
                        [$metaKeyId, $locale]
                    );
                }
            } else {
                // mode=3：使用已 JOIN 的 pt 別名
                $query->orderBy("pt.{$sort}", $order);
            }
        } else {
            $query->orderBy("{$table}.{$sort}", $order);
        }

        // 取得單筆
        if (isset($params['first']) && $params['first'] == true) {
            if (empty($params['pluck'])) {
                return $query->first();
            }
            return $query->pluck($params['pluck'])->first();
        }

        // 分頁設定
        if (isset($params['per_page'])) {
            $perPage = $params['per_page'];
        } elseif (isset($params['limit'])) {
            $perPage = (int) $params['limit'];
        } else {
            $perPage = (int) config('settings.config_admin_pagination_limit', 10);
        }

        $pagination = $params['pagination'] ?? true;

        // Get result
        if ($pagination == true && $perPage > 0) {
            $result = $query->paginate($perPage);
        } elseif ($pagination == true && $perPage == 0) {
            $result = $query->paginate($query->count());
        } elseif ($pagination == false && $perPage != 0) {
            $result = $query->limit($perPage)->get();
        } else {
            $result = $query->get();
        }

        // Pluck
        if (!empty($params['pluck'])) {
            $result = $result->pluck($params['pluck']);
        }

        if (!empty($params['keyBy'])) {
            $result = $result->keyBy($params['keyBy']);
        }

        return $result;
    }

    /**
     * 取得 translations 表名稱
     */
    protected static function getTranslationTableName(string $table): string
    {
        // products → product_translations
        return rtrim($table, 's') . '_translations';
    }

    /**
     * 取得 metas 表名稱
     */
    protected static function getMetaTableName(string $table): string
    {
        // products → product_metas
        return rtrim($table, 's') . '_metas';
    }

    /**
     * 取得外鍵名稱
     */
    protected static function getForeignKeyName(string $table): string
    {
        // products → product_id
        return rtrim($table, 's') . '_id';
    }

    /**
     * 顯示 SQL 內容（debug 用）
     */
    public static function showSqlContent(Builder $query, bool $exit = true): ?string
    {
        return OrmHelper::showSqlContent($query, $exit ? 1 : 0);
    }
}
