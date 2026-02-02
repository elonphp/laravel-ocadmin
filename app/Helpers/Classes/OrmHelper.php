<?php

namespace App\Helpers\Classes;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ORM 查詢輔助類別
 *
 * 提供 Eloquent 查詢的常用操作：過濾、排序、分頁、資料轉換等。
 */
class OrmHelper
{
    /**
     * 準備查詢：套用 select、過濾、排序
     */
    public static function prepare(EloquentBuilder $query, array &$params = []): void
    {
        self::select($query, $params);
        self::applyFilters($query, $params);
        self::sortOrder($query, $params);
    }

    /**
     * 選擇本表欄位（不包括關聯欄位）
     */
    public static function select(EloquentBuilder $query, array &$params): void
    {
        if (empty($params['select'])) {
            return;
        }

        $model = $query->getModel();
        $table = self::getTableWithPrefix($model);
        $tableColumns = self::getTableColumns($table);

        // 取交集，確保只選擇存在的欄位
        $select = array_intersect($params['select'], $tableColumns);

        $query->select(array_map(fn($field) => "{$table}.{$field}", $select));
    }

    /**
     * 套用查詢過濾條件
     *
     * 支援參數格式：
     * - filter_xxx: 模糊搜尋
     * - equal_xxx: 完全相等
     */
    public static function applyFilters(EloquentBuilder $query, array &$params = []): void
    {
        $model = $query->getModel();
        $table = self::getTableWithPrefix($model);
        $tableColumns = self::getTableColumns($table);

        // is_active 預設過濾
        if (in_array('is_active', $tableColumns)) {
            if (!isset($params['equal_is_active'])) {
                $params['equal_is_active'] = 1;
            } elseif ($params['equal_is_active'] === '*') {
                unset($params['equal_is_active']);
            } else {
                $params['equal_is_active'] = (int) $params['equal_is_active'];
            }
        }

        // 建構查詢
        foreach ($params as $key => $value) {
            if (!str_starts_with($key, 'filter_') && !str_starts_with($key, 'equal_')) {
                continue;
            }

            $column = preg_replace('/^(filter_|equal_)/', '', $key);

            if (in_array($column, $tableColumns)) {
                self::filterOrEqualColumn($query, $key, $value);
            }
        }
    }

    /**
     * 套用單一欄位的過濾或等於條件
     *
     * filter_ 支援運算符：
     * - 無運算符：REGEXP 模糊搜尋，空格視為萬用字元
     * - =：空值或 null
     * - =value：完全相等
     * - <>：非空
     * - <>value：不等於
     * - <value：小於
     * - >value：大於
     * - *value：結尾符合
     * - value*：開頭符合
     */
    public static function filterOrEqualColumn(EloquentBuilder $query, string $key, mixed $value): void
    {
        $column = preg_replace('/^(filter_|equal_)/', '', $key);

        if (str_starts_with($key, 'equal_')) {
            $value = trim((string) $value);
            $query->where($column, $value);
            return;
        }

        // filter_ 處理
        $value = trim((string) $value);
        if (strlen($value) === 0) {
            return;
        }

        // 跳脫特殊字元
        $escapeChars = ['(', ')', '+'];
        foreach ($escapeChars as $char) {
            $value = str_replace($char, '\\' . $char, $value);
        }

        // *foo* 格式：移除前後星號
        if (str_starts_with($value, '*') && str_ends_with($value, '*') && strlen($value) > 2) {
            $value = substr($value, 1, -1);
        }

        $operators = ['=', '<', '>', '*'];
        $hasOperator = false;
        foreach ($operators as $op) {
            if (str_starts_with($value, $op) || str_ends_with($value, '*')) {
                $hasOperator = true;
                break;
            }
        }

        // 無運算符：REGEXP 搜尋
        if (!$hasOperator) {
            $regexValue = str_replace(' ', '(.*)', $value);
            $query->where($column, 'REGEXP', $regexValue);
            return;
        }

        // = 空值或 null
        if ($value === '=') {
            $query->where(function ($q) use ($column) {
                $q->whereNull($column)->orWhere($column, '=', '');
            });
            return;
        }

        // =value 完全相等
        if (str_starts_with($value, '=') && strlen($value) > 1) {
            $query->where($column, '=', substr($value, 1));
            return;
        }

        // <> 非空非 null
        if ($value === '<>') {
            $query->where(function ($q) use ($column) {
                $q->whereNotNull($column)->where($column, '<>', '');
            });
            return;
        }

        // <>value 不等於
        if (str_starts_with($value, '<>') && strlen($value) > 2) {
            $query->where($column, '<>', substr($value, 2));
            return;
        }

        // <value 小於
        if (str_starts_with($value, '<') && strlen($value) > 1) {
            $query->where($column, '<', substr($value, 1));
            return;
        }

        // >value 大於
        if (str_starts_with($value, '>') && strlen($value) > 1) {
            $query->where($column, '>', substr($value, 1));
            return;
        }

        // *value 結尾符合
        if (str_starts_with($value, '*') && !str_ends_with($value, '*')) {
            $regexValue = '(.*)' . str_replace(' ', '(.*)', substr($value, 1)) . '$';
            $query->where($column, 'REGEXP', $regexValue);
            return;
        }

        // value* 開頭符合
        if (!str_starts_with($value, '*') && str_ends_with($value, '*')) {
            $regexValue = '^' . str_replace(' ', '(.*)', substr($value, 0, -1)) . '(.*)';
            $query->where($column, 'REGEXP', $regexValue);
            return;
        }
    }

    /**
     * 套用排序
     */
    public static function sortOrder(EloquentBuilder $query, array $params): void
    {
        $model = $query->getModel();
        $table = self::getTableWithPrefix($model);
        $tableColumns = self::getTableColumns($table);

        $sort = $params['sort'] ?? null;
        $order = strtoupper($params['order'] ?? 'DESC');

        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // 預設用 id 排序
        if (empty($sort) && in_array('id', $tableColumns)) {
            $sort = 'id';
        }

        if (!empty($sort) && in_array($sort, $tableColumns)) {
            $query->orderBy("{$table}.{$sort}", $order);
        }
    }

    /**
     * 取得查詢結果
     *
     * @param array $params 支援參數：
     *   - first: 只取第一筆
     *   - pluck: 只取特定欄位值
     *   - keyBy: 以特定欄位為 key
     *   - limit: 每頁筆數（0=不限制）
     *   - pagination: 是否分頁（預設 true）
     */
    public static function getResult(EloquentBuilder $query, array $params, bool $debug = false): mixed
    {
        if ($debug) {
            self::showSqlContent($query);
        }

        // 取第一筆
        if (!empty($params['first'])) {
            if (empty($params['pluck'])) {
                return $query->first();
            }
            return $query->pluck($params['pluck'])->first();
        }

        // 分頁設定
        $limit = (int) ($params['limit'] ?? config('settings.config_admin_pagination_limit', 10));
        $pagination = $params['pagination'] ?? true;

        // 取得結果
        if ($pagination && $limit > 0) {
            $result = $query->paginate($limit);
        } elseif ($pagination && $limit === 0) {
            $result = $query->paginate($query->count() ?: 1);
        } elseif (!$pagination && $limit > 0) {
            $result = $query->limit($limit)->get();
        } else {
            $result = $query->get();
        }

        // Pluck
        if (!empty($params['pluck'])) {
            $result = $result->pluck($params['pluck']);
        }

        // KeyBy
        if (!empty($params['keyBy'])) {
            $result = $result->keyBy($params['keyBy']);
        }

        return $result;
    }

    /**
     * 查找或建立新 Model
     */
    public static function findIdOrFailOrNew(EloquentBuilder $query, ?int $id = null): Model
    {
        if (!empty($id)) {
            return $query->findOrFail($id);
        }
        return $query->getModel()->newInstance();
    }

    /**
     * 儲存 Model
     *
     * @param string $modelClass Model 類別名稱
     * @param array $data 要儲存的資料
     * @param int|null $id 要更新的 ID（空值=新增）
     * @param array $params 額外參數：
     *   - operator_id: 操作者 ID（自動填入 created_by/updated_by 等欄位）
     *   - isFullUpdate: 完整更新（未傳入的欄位會被設為預設值）
     */
    public static function save(string $modelClass, array $data, ?int $id = null, array $params = []): Model
    {
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} not found");
        }

        // 取得或建立 Model
        if (empty($id)) {
            $row = new $modelClass();
        } else {
            $row = $modelClass::find($id);
            if (empty($row)) {
                throw new \Exception("{$modelClass} id={$id} not found");
            }
        }

        // 修改時移除 created_by 相關欄位
        if (!empty($id)) {
            unset($data['created_by'], $data['creator_id'], $data['created_by_id']);
        }

        // 移除時間戳欄位（由系統處理）
        unset($data['created_at'], $data['updated_at']);

        // 過濾不可儲存的欄位
        $savableColumns = self::getSavableColumns($row);
        $data = array_intersect_key($data, array_flip($savableColumns));

        // 取得欄位資訊
        $table = $row->getTable();
        $connection = $row->getConnectionName();
        $tableMeta = self::getTableColumnsMeta($table, $connection);
        $tableColumns = array_keys($tableMeta);

        // 處理操作者欄位
        if (!empty($params['operator_id'])) {
            $operatorId = $params['operator_id'];

            // 建立者欄位
            $creatorFields = ['created_by', 'created_by_id', 'creator_id'];
            foreach ($creatorFields as $field) {
                if (in_array($field, $tableColumns) && empty($id)) {
                    $row->$field = $operatorId;
                    break;
                }
            }

            // 修改者欄位
            $updaterFields = ['updated_by', 'updated_by_id', 'updater_id', 'modifier_id', 'modified_by', 'modified_by_id'];
            foreach ($updaterFields as $field) {
                if (in_array($field, $tableColumns)) {
                    $row->$field = $operatorId;
                    break;
                }
            }
        }

        // 套用資料
        $isFullUpdate = $params['isFullUpdate'] ?? false;
        if ($isFullUpdate) {
            self::applyFullUpdate($row, $data, $tableMeta);
        } else {
            self::applyPartialUpdate($row, $data, $tableMeta);
        }

        $row->save();

        return $row;
    }

    /**
     * 完整更新：未傳入的欄位會被設為預設值
     *
     * @param array $tableMeta getTableColumnsMeta() 的回傳值
     */
    protected static function applyFullUpdate(Model $row, array $data, array $tableMeta): void
    {
        $skipFields = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($tableMeta as $field => $meta) {
            if (in_array($field, $skipFields)) {
                continue;
            }

            $row->$field = array_key_exists($field, $data) ? $data[$field] : $meta['default'];
        }
    }

    /**
     * 部分更新：只更新傳入的欄位
     *
     * @param array $tableMeta getTableColumnsMeta() 的回傳值
     */
    protected static function applyPartialUpdate(Model $row, array $data, array $tableMeta): void
    {
        $skipFields = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($data as $field => $value) {
            if (array_key_exists($field, $tableMeta) && !in_array($field, $skipFields)) {
                $row->$field = $value;
            }
        }
    }

    /**
     * 取得資料表欄位的詳細資訊
     *
     * @return array<string, array> 結構如下：
     *   [
     *     '欄位名' => [
     *       'default'   => 預設值,
     *       'type'      => 資料類型 (varchar, int, decimal...),
     *       'length'    => 字串長度 (varchar, char),
     *       'precision' => 數字精度 (decimal, numeric),
     *       'scale'     => 小數位數 (decimal, numeric),
     *       'nullable'  => 是否可為 null (bool),
     *     ],
     *     ...
     *   ]
     */
    public static function getTableColumnsMeta(string $table, ?string $connection = null): array
    {
        $connection = $connection ?: config('database.default');
        $database = DB::connection($connection)->getDatabaseName();

        $columns = DB::connection($connection)->select("
            SELECT
                COLUMN_NAME as name,
                COLUMN_DEFAULT as default_value,
                DATA_TYPE as data_type,
                CHARACTER_MAXIMUM_LENGTH as char_length,
                NUMERIC_PRECISION as num_precision,
                NUMERIC_SCALE as num_scale,
                IS_NULLABLE as is_nullable
            FROM information_schema.columns
            WHERE table_schema = ? AND table_name = ?
            ORDER BY ORDINAL_POSITION
        ", [$database, $table]);

        $result = [];
        foreach ($columns as $col) {
            $result[$col->name] = [
                'default'   => $col->default_value,
                'type'      => $col->data_type,
                'length'    => $col->char_length ? (int) $col->char_length : null,
                'precision' => $col->num_precision ? (int) $col->num_precision : null,
                'scale'     => $col->num_scale ? (int) $col->num_scale : null,
                'nullable'  => $col->is_nullable === 'YES',
            ];
        }

        return $result;
    }

    // ========== 工具方法 ==========

    /**
     * 取得資料表全名（含前綴）
     */
    public static function getTableWithPrefix(Model $model): string
    {
        $connection = $model->getConnectionName();
        $prefix = config("database.connections.{$connection}.prefix", '');
        return $prefix . $model->getTable();
    }

    /**
     * 取得資料表欄位列表
     */
    public static function getTableColumns(string $table, ?string $connectionName = null): array
    {
        if (empty($connectionName)) {
            return Schema::getColumnListing($table);
        }
        return Schema::connection($connectionName)->getColumnListing($table);
    }

    /**
     * 取得 Model 可儲存的欄位（排除 guarded）
     */
    public static function getSavableColumns(Model $model): array
    {
        $table = $model->getTable();
        $tableColumns = Schema::getColumnListing($table);
        $fillable = $model->getFillable();
        $guarded = $model->getGuarded();

        // 沒有定義 fillable 則使用所有欄位
        if (empty($fillable)) {
            return array_diff($tableColumns, $guarded);
        }

        return array_diff($fillable, $guarded);
    }

    /**
     * 顯示 SQL 內容（調試用）
     */
    public static function showSqlContent(EloquentBuilder $query, bool $exit = true): ?string
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $output = [
            'statement' => empty($bindings)
                ? $sql
                : vsprintf(str_replace('?', "'%s'", $sql), $bindings),
            'original' => [
                'toSql' => $sql,
                'bindings' => $bindings,
            ],
        ];

        $html = "<pre>" . print_r($output, true) . "</pre>";

        if ($exit) {
            echo $html;
            exit;
        }

        return $html;
    }

    // ========== 資料轉換方法 ==========

    /**
     * 將資料集轉為乾淨的物件集合
     *
     * Eloquent Collection 包含很多內部屬性，用這個方法可以轉成純粹的 stdClass 集合。
     */
    public static function toCleanCollection(mixed $data): array|LengthAwarePaginator
    {
        if ($data instanceof LengthAwarePaginator) {
            return $data->setCollection(
                $data->getCollection()->map(fn($item) => self::toCleanObject($item))
            );
        }

        if (is_iterable($data)) {
            $result = [];
            foreach ($data as $row) {
                $result[] = self::toCleanObject($row);
            }
            return $result;
        }

        return [];
    }

    /**
     * 將單一 Model 轉為乾淨的 stdClass
     */
    public static function toCleanObject(mixed $input): \stdClass|string
    {
        if (is_string($input)) {
            return $input;
        }

        $object = new \stdClass();

        // 轉為陣列
        $data = is_object($input) && method_exists($input, 'toArray')
            ? $input->toArray()
            : (array) $input;

        // 排除 Eloquent 內部屬性
        $excludeKeys = [
            'incrementing', 'exists', 'wasRecentlyCreated', 'timestamps',
            'usesUniqueIds', 'preventsLazyLoading', 'guarded', 'fillable',
        ];

        foreach ($data as $key => $value) {
            if (!in_array($key, $excludeKeys)) {
                $object->{$key} = $value;
            }
        }

        return $object;
    }

    /**
     * 從資料中移除指定的 key
     */
    public static function removeKeys(mixed $rows, array $keysToRemove): mixed
    {
        $mapFunction = function ($row) use ($keysToRemove) {
            foreach ($keysToRemove as $key) {
                if (is_array($row)) {
                    unset($row[$key]);
                } elseif (is_object($row)) {
                    unset($row->$key);
                }
            }
            return $row;
        };

        if ($rows instanceof LengthAwarePaginator) {
            return $rows->setCollection($rows->getCollection()->map($mapFunction));
        }

        if ($rows instanceof \Illuminate\Support\Collection || $rows instanceof EloquentCollection) {
            return $rows->map($mapFunction);
        }

        if (is_array($rows)) {
            return array_map($mapFunction, $rows);
        }

        return $rows;
    }

    /**
     * 套用 Eloquent 的 include（選擇性載入關聯）
     *
     * 用於 API：?include=items:price,subtotal,customer:name,email
     */
    public static function applyEloquentIncludes(
        EloquentBuilder $query,
        array $includes,
        string $baseModelClass
    ): EloquentBuilder {
        foreach ($includes as $relation => $fields) {
            if (empty($fields)) {
                $query->with($relation);
                continue;
            }

            $query->with([
                $relation => function ($q) use ($fields, $relation, $baseModelClass) {
                    $relationInstance = (new $baseModelClass)->{$relation}();
                    $relatedModel = $relationInstance->getRelated();

                    // 確保包含主鍵
                    $primaryKey = $relatedModel->getKeyName();
                    if (!in_array($primaryKey, $fields)) {
                        $fields[] = $primaryKey;
                    }

                    // 確保包含外鍵
                    if (method_exists($relationInstance, 'getForeignKeyName')) {
                        $foreignKey = $relationInstance->getForeignKeyName();
                        if (!in_array($foreignKey, $fields)) {
                            $fields[] = $foreignKey;
                        }
                    }

                    $q->select($fields);
                }
            ]);
        }

        return $query;
    }
}
