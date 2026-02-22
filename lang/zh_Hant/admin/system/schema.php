<?php

return [
    'heading_title' => '資料表結構管理',

    // 文字
    'text_list'               => '資料表列表',
    'text_add'                => '新增資料表',
    'text_edit'               => '編輯資料表',
    'text_system'             => '系統管理',
    'text_synced'             => '同步',
    'text_diff'               => '有差異',
    'text_db_only'            => '僅DB',
    'text_schema_only'        => '僅Schema',
    'text_success_save'       => 'Schema 定義檔儲存成功！',
    'text_success_sync'       => '資料表同步成功！',
    'text_success_export'     => 'Schema 檔案匯出成功！',
    'text_success_export_all' => '已匯出 %s 個資料表的 Schema 檔案！',
    'text_diff_preview'       => '差異比對預覽',
    'text_no_changes'         => '結構一致，無需變更',
    'text_sql_preview'        => 'SQL 預覽',
    'text_confirm_sync'       => '確定要執行同步嗎？這將直接修改資料庫結構。',
    'text_confirm_export_all' => '確定要匯出全部資料表嗎？已有的 Schema 檔案將被覆蓋。',
    'text_pending_changes'    => 'Schema 定義已儲存，但變更尚未套用到資料表。',
    'text_no_pending'         => '資料表結構與 Schema 定義一致，無需套用。',

    // 欄位
    'column_table_name'        => '表名',
    'column_table_comment'     => '表備註',
    'column_comment'           => '備註',
    'column_column_count'      => '欄位數',
    'column_translation_count' => '翻譯欄位',
    'column_status'            => '狀態',
    'column_changes'           => '變更數',
    'column_column_name'       => '欄位名',
    'column_type'              => '類型',
    'column_length'            => '長度',
    'column_unsigned'          => 'Unsigned',
    'column_nullable'          => 'Null',
    'column_default'           => '預設值',
    'column_primary'           => 'PK',
    'column_auto_inc'          => '自增',
    'column_index'             => '索引',
    'column_unique'            => '唯一',
    'column_foreign'           => '外鍵',
    'column_index_name'        => '索引名稱',
    'column_index_type'        => '類型',
    'column_index_columns'     => '欄位',

    // Tab
    'tab_columns'      => '欄位定義',
    'tab_translations' => '翻譯欄位',
    'tab_indexes'      => '索引',

    // 按鈕
    'button_add_index'       => '新增索引',
    'button_add_column'      => '新增欄位',
    'button_add_translation' => '新增翻譯欄位',
    'button_diff'            => '比對預覽',
    'button_sync'            => '同步',
    'button_apply'           => '套用變更到資料表',
    'button_export'          => '匯出',
    'button_export_all'      => '匯出全部',

    // Help
    'help_table_name' => '小寫字母、數字及底線組成，如：sal_orders',
    'help_foreign'    => '格式：表名.欄位名，如：users.id',
    'help_length'     => 'varchar 為字元長度，decimal 為 精度,小數位',
    'help_unsigned'   => '無符號',
    'help_nullable'   => '允許空值',
    'help_primary'    => '主索引',
    'help_index_columns' => '逗號分隔欄位名，如：col1, col2',

    // 篩選
    'placeholder_search_table' => '搜尋表名',

    // 錯誤
    'error_table_name_required' => '表名為必填',
    'error_no_columns'          => '至少需要一個欄位定義',
];
