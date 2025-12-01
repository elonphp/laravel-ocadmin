<?php

/**
 * 使用者管理（訪問控制）- 語言檔
 */

return [
    // 頁面標題
    'heading_title' => '使用者管理',
    'text_add'      => '新增使用者',
    'text_edit'     => '編輯使用者',
    'text_list'     => '使用者列表',
    'text_filter'   => '篩選條件',

    // 麵包屑
    'text_access' => '訪問控制',

    // 欄位標籤（列表）
    'column_username' => '帳號',
    'column_email'    => 'Email',
    'column_name'     => '姓名',
    'column_roles'    => '角色',
    'column_action'   => '操作',

    // 表單欄位
    'entry_user'  => '使用者',
    'entry_roles' => '角色配置',

    // 輔助說明
    'help_search_user' => '輸入 Email、帳號、姓名或手機搜尋，僅顯示尚未有後台權限的使用者',

    // Placeholder
    'placeholder_search'      => '搜尋帳號、Email 或姓名',
    'placeholder_search_user' => '輸入 Email、帳號或姓名搜尋...',

    // 選項
    'text_search' => '搜尋',

    // 角色相關
    'text_staff_required' => '必選，後台准入角色',
    'text_no_other_roles' => '尚未建立其他角色',

    // 按鈕
    'button_remove' => '移除',

    // 訊息
    'text_add_success'    => '使用者已加入後台訪問控制！',
    'text_edit_success'   => '使用者角色更新成功！',
    'text_remove_success' => '使用者已從後台訪問控制移除！',
    'text_confirm_remove' => '確定要移除選取的使用者的後台權限嗎？移除後這些使用者將無法登入後台。',

    // 錯誤訊息
    'error_already_staff'   => '此使用者已有後台訪問權限',
    'error_not_staff'       => '此使用者沒有後台訪問權限',
    'error_select_required' => '請選擇要移除的項目',
    'error_remove_failed'   => '移除失敗',
];
