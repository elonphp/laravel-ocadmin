<?php

return [
    // Heading
    'heading_title' => '角色管理',

    // Text
    'text_list' => '角色列表',
    'text_add' => '新增角色',
    'text_edit' => '編輯角色',
    'text_system' => '系統管理',

    // Column（欄位標籤，列表與表單共用）
    'column_name' => '角色代碼',
    'column_display_name' => '顯示名稱',
    'column_guard_name' => 'Guard',
    'column_note' => '備註',
    'column_sort_order' => '排序',
    'column_is_active' => '狀態',
    'column_search' => '關鍵字搜尋',

    // Placeholder
    'placeholder_name' => '如 order_operator',
    'placeholder_display_name' => '請輸入顯示名稱',
    'placeholder_note' => '備註說明',
    'placeholder_search' => '搜尋代碼、名稱、備註',

    // Help
    'help_name' => '僅限小寫英文、數字、底線',
    'help_guard_name' => '預設為 web，通常不需修改',

    // Tab
    'tab_permission' => '權限指派',

    // Permission Group（權限群組中文名稱）
    'permission_group' => [
        'catalog' => [
            'product' => '商品管理',
            'option'  => '選項管理',
        ],
        'order' => [
            'order' => '訂單管理',
        ],
        'finance' => [
            'payment' => '付款管理',
            'refund'  => '退款管理',
        ],
    ],

    // Success
    'text_success_add' => '角色新增成功！',
    'text_success_edit' => '角色更新成功！',

    // Error
    'error_has_users' => '此角色已指派給使用者，請先移除使用者指派',
    'error_select_delete' => '請選擇要刪除的項目',
    'error_batch_has_users' => '部分角色已指派給使用者，請先移除使用者指派',
];
