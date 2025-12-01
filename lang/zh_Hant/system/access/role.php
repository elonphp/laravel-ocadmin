<?php

/**
 * 角色管理 - 語言檔
 */

return [
    // 頁面標題
    'heading_title' => '角色管理',
    'text_add'      => '新增角色',
    'text_edit'     => '編輯角色',
    'text_list'     => '角色列表',
    'text_filter'   => '篩選條件',

    // 麵包屑
    'text_access' => '訪問控制',

    // 欄位標籤（列表）
    'column_name'              => '角色代號',
    'column_title'             => '顯示名稱',
    'column_permissions_count' => '權限數',
    'column_guard_name'        => 'Guard',
    'column_action'            => '操作',

    // 表單欄位
    'entry_name'        => '角色代號',
    'entry_guard_name'  => 'Guard 名稱',
    'entry_title'       => '顯示名稱',
    'entry_description' => '說明',

    // Tab
    'tab_general'     => '基本資料',
    'tab_permissions' => '權限設定',

    // 輔助說明
    'help_name'       => '角色代號，如：admin、editor、staff',
    'help_guard_name' => '預設為 web，通常不需修改',

    // Placeholder
    'placeholder_name'        => '請輸入角色代號',
    'placeholder_title'       => '請輸入顯示名稱',
    'placeholder_description' => '請輸入角色說明',
    'placeholder_search'      => '搜尋代號或名稱',

    // 類型
    'text_type_menu'   => '選單',
    'text_type_action' => '功能',

    // 選項
    'text_search' => '搜尋',

    // 按鈕
    'button_select_all'   => '全選',
    'button_deselect_all' => '取消全選',

    // 權限
    'text_no_permissions' => '尚未建立任何權限，請先至權限管理新增權限。',

    // 訊息（覆蓋 common）
    'text_add_success'    => '角色新增成功！',
    'text_edit_success'   => '角色更新成功！',
    'text_delete_success' => '角色刪除成功！',
    'text_confirm_delete' => '確定要刪除選取的角色嗎？',

    // 錯誤訊息
    'error_name'            => '角色代號為必填，且不可重複',
    'error_guard_name'      => 'Guard 名稱為必填',
    'error_has_users'       => '此角色有使用者使用中，無法刪除',
    'error_select_required' => '請選擇要刪除的項目',
    'error_delete_failed'   => '刪除失敗',
];
