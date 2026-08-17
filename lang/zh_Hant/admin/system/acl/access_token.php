<?php

return [
    'heading_title' => 'Access Token',
    'text_add'      => '新增 Access Token',
    'text_edit'     => '編輯 Access Token',
    'text_list'     => 'Access Token 列表',

    // 欄位
    'column_search'       => '關鍵字',
    'column_user'         => '使用者',
    'column_user_source'  => '使用者來源',
    'column_username'     => '帳號',
    'column_local_name'   => '顯示名稱',
    'column_abilities'    => '存取範圍',
    'column_last_used_at' => '最後使用',
    'column_expires_at'   => '到期日',

    // 文字
    'text_existing_user'  => '選擇現有使用者',
    'text_create_local'   => '建立本地帳號',
    'text_no_expiry'      => '不過期',
    'text_expired'        => '已過期',
    'text_active'         => '有效',
    'text_token_created'  => 'Access Token 已建立',
    'text_token_warning'  => '請立即複製此 Token，關閉後將無法再次檢視！',
    'text_copied'         => '已複製',
    'text_user_deleted'   => '(已刪除)',
    'text_confirm_revoke' => '確定要撤銷選取的 %s 筆 Access Token 嗎？撤銷後將無法使用。',

    // Placeholder
    'placeholder_search'      => '名稱或使用者',
    'placeholder_user_search' => '輸入使用者名稱搜尋',
    'placeholder_username'    => '例如：svc_api',
    'placeholder_local_name'  => '例如：API 服務帳號',
    'placeholder_name'        => '例如：portal_api、webhook_xxx',

    // Help
    'help_user_search' => '輸入名稱後自動搜尋',
    'help_username'    => '帳號名稱，必須唯一。users.username',
    'help_local_name'  => '使用者顯示名稱。users.name',
    'help_name'        => '用於識別此 Access Token 的名稱。personal_access_tokens.name',
    'help_abilities'   => '選擇此 Token 可存取的 Portal',
    'help_expires_at'    => '選填，空白表示不過期',
    'help_user_readonly' => '使用者綁定不可變更',

    // 按鈕
    'button_revoke' => '撤銷',
    'button_copy'   => '複製',

    // 成功/錯誤訊息
    'text_success_create' => 'Access Token 建立成功',
    'text_success_update' => 'Access Token 更新成功',
    'text_success_revoke' => '撤銷成功',
    'text_error_create'   => '建立失敗',
    'text_error_revoke'   => '撤銷失敗',
    'error_select_revoke' => '請選擇要撤銷的項目',

    // 驗證訊息
    'error_name_required'       => '名稱為必填',
    'error_abilities_required'  => '請至少選擇一個存取範圍',
    'error_expires_at_after'    => '到期日必須晚於今天',
    'error_username_required'   => '帳號為必填',
    'error_username_unique'     => '帳號已存在',
    'error_local_name_required' => '顯示名稱為必填',
    'error_user_id_required'    => '請選擇使用者',
    'error_user_id_exists'      => '使用者不存在',
];
