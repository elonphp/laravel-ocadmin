<?php

/**
 * 國家管理 - 語言檔
 */

return [
    // 頁面標題
    'heading_title' => '國家管理',
    'text_add'      => '新增國家',
    'text_edit'     => '編輯國家',
    'text_list'     => '國家列表',
    'text_filter'   => '篩選條件',

    // 麵包屑
    'text_localization' => '本地化',

    // 欄位標籤
    'column_name'              => '國家名稱',
    'column_native_name'       => '本地名稱',
    'column_iso_code_2'        => 'ISO 代碼 (2)',
    'column_iso_code_3'        => 'ISO 代碼 (3)',
    'column_action'            => '操作',

    // 表單欄位
    'entry_name'              => '國家名稱',
    'entry_native_name'       => '本地名稱',
    'entry_iso_code_2'        => 'ISO 代碼 (2)',
    'entry_iso_code_3'        => 'ISO 代碼 (3)',
    'entry_address_format'    => '地址格式',
    'entry_postcode_required' => '郵遞區號必填',

    // 輔助說明
    'help_native_name'     => '如：中華民國、日本国',
    'help_iso_code_2'      => 'ISO 3166-1 alpha-2 代碼',
    'help_iso_code_3'      => 'ISO 3166-1 alpha-3 代碼',
    'help_address_format'  => '可用變數：{firstname}, {lastname}, {company}, {address_1}, {address_2}, {city}, {postcode}, {zone}, {country}',

    // Placeholder
    'placeholder_name'         => '請輸入國家名稱',
    'placeholder_native_name'  => '如：中華民國、日本国',
    'placeholder_iso_code_2'   => '如：TW',
    'placeholder_iso_code_3'   => '如：TWN',
    'placeholder_address_format' => '請輸入地址格式範本',

    // 訊息（覆蓋 common）
    'text_add_success'    => '國家新增成功！',
    'text_edit_success'   => '國家更新成功！',
    'text_delete_success' => '國家刪除成功！',

    // 錯誤訊息
    'error_name'       => '國家名稱為必填，且至少 2 個字元',
    'error_iso_code_2' => 'ISO 代碼 (2) 必須為 2 個字元',
    'error_iso_code_3' => 'ISO 代碼 (3) 必須為 3 個字元',
];
