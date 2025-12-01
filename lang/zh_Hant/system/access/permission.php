<?php

/**
 * 權限管理 - 語言檔
 */

return [
    // 頁面標題
    'heading_title' => '權限管理',
    'text_add'      => '新增權限',
    'text_edit'     => '編輯權限',
    'text_list'     => '權限列表',
    'text_filter'   => '篩選條件',

    // 麵包屑
    'text_access' => '訪問控制',

    // 欄位標籤（列表）
    'column_name'       => '權限代號',
    'column_title'      => '顯示名稱',
    'column_type'       => '類型',
    'column_parent'     => '父層',
    'column_sort_order' => '排序',
    'column_action'     => '操作',

    // 表單欄位
    'entry_name'        => '權限代號',
    'entry_guard_name'  => 'Guard 名稱',
    'entry_title'       => '顯示名稱',
    'entry_description' => '說明',
    'entry_type'        => '類型',
    'entry_parent'      => '父層權限',
    'entry_sort_order'  => '排序',

    // 輔助說明
    'help_name'       => '權限代號，如：system.setting.view、catalog.product.edit',
    'help_guard_name' => '預設為 web，通常不需修改',
    'help_type'       => 'menu = 選單權限（用於選單顯示）、action = 功能權限（用於功能操作）',

    // Placeholder
    'placeholder_name'        => '請輸入權限代號',
    'placeholder_title'       => '請輸入顯示名稱',
    'placeholder_description' => '請輸入權限說明',
    'placeholder_search'      => '搜尋代號或名稱',

    // 類型
    'text_type_menu'   => '選單',
    'text_type_action' => '功能',

    // 選項
    'text_none'      => '無（頂層）',
    'text_top_level' => '頂層',
    'text_search'    => '搜尋',

    // 訊息（覆蓋 common）
    'text_add_success'    => '權限新增成功！',
    'text_edit_success'   => '權限更新成功！',
    'text_delete_success' => '權限刪除成功！',
    'text_confirm_delete' => '確定要刪除選取的權限嗎？',

    // 錯誤訊息
    'error_name'            => '權限代號為必填，且不可重複',
    'error_guard_name'      => 'Guard 名稱為必填',
    'error_type'            => '請選擇類型',
    'error_parent_self'     => '不可將自己設為父層',
    'error_has_children'    => '此權限有子權限，無法刪除',
    'error_select_required' => '請選擇要刪除的項目',
    'error_delete_failed'   => '刪除失敗',
];
