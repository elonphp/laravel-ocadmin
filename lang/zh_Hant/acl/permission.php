<?php

return [
    // Heading
    'heading_title' => '權限管理',

    // Text
    'text_list' => '權限列表',
    'text_add' => '新增權限',
    'text_edit' => '編輯權限',
    'text_system' => '系統管理',

    // Column（欄位標籤，列表與表單共用）
    'column_name' => '權限代碼',
    'column_display_name' => '顯示名稱',
    'column_guard_name' => 'Guard',
    'column_note' => '備註',
    'column_search' => '關鍵字搜尋',

    // Placeholder
    'placeholder_name' => '如 mss.department.list',
    'placeholder_display_name' => '請輸入顯示名稱',
    'placeholder_note' => '備註說明',
    'placeholder_search' => '搜尋代碼、名稱、備註',

    // Help
    'help_name' => '三段式格式：{module}.{resource}.{action}，僅限小寫英文、數字、底線、點',
    'help_guard_name' => '預設為 web，通常不需修改',

    // Success
    'text_success_add' => '權限新增成功！',
    'text_success_edit' => '權限更新成功！',

    // Error
    'error_has_roles' => '此權限已指派給角色，請先移除角色指派',
    'error_select_delete' => '請選擇要刪除的項目',
    'error_batch_has_roles' => '部分權限已指派給角色，請先移除角色指派',
];
