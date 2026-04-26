<?php

return [
    // 按鈕
    'button_submit' => '送出',
    'button_cancel' => '取消',
    'button_save' => '儲存',
    'button_delete' => '刪除',
    'button_edit' => '編輯',
    'button_add' => '新增',
    'button_create' => '建立',
    'button_update' => '更新',
    'button_search' => '搜尋',
    'button_filter' => '搜尋',
    'button_reset' => '重設',
    'button_clear' => '清除',
    'button_back' => '返回',
    'button_close' => '關閉',
    'button_confirm' => '確認',
    'button_export' => '匯出',
    'button_import' => '匯入',
    'button_logout' => '登出',

    // 欄位
    'column_id' => '編號',
    'column_name' => '名稱',
    'column_code' => '代碼',
    'column_status' => '狀態',
    'column_sort_order' => '排序',
    'column_action' => '操作',
    'column_created_at' => '建立時間',
    'column_updated_at' => '更新時間',
    'column_remark' => '備註',
    'column_active' => '啟用',
    'column_is_active' => '啟用',  // alias of column_active；新程式碼建議用 column_active

    // 文字
    'text_home' => '首頁',
    'text_loading' => '載入中...',
    'text_no_data' => '無資料',
    'text_total' => '共 %s 筆',
    'text_yes' => '是',
    'text_no' => '否',
    'text_all' => '-- 全部 --',
    'text_none' => '無',
    'text_select' => '請選擇',  // @deprecated 改用 text_please_select（form 必填）或 text_all（filter 不篩選）
    'text_please_select' => '-- 請選擇 --',
    'text_enabled' => '啟用',
    'text_disabled' => '停用',
    'text_no_results' => '無結果',
    'text_frontend' => '前台首頁',

    // Tab（全後台只標準化這兩個，其它 tab 由各模組自定）
    // 規範：見 docs/common/00003_Ocadmin程式規範.md「Tab 標籤統一」一節
    'tab_basic' => '基本資料',
    'tab_trans' => '多語資料',

    // 篩選
    'text_filter' => '搜尋',
    'text_showing' => '顯示 %s 到 %s，共 %s 筆',
    'text_confirm_batch_delete' => '確定要刪除選取的 %s 筆資料嗎？',

    // 確認
    'text_confirm_delete' => '確定要刪除嗎？',
    'text_confirm_unsaved' => '尚有未儲存的變更，確定要離開嗎？',

    // 訊息
    'text_success_save' => '儲存成功',
    'text_success_delete' => '刪除成功',
    'text_success_update' => '更新成功',
    'text_success_create' => '建立成功',
    'text_error_save' => '儲存失敗',
    'text_error_delete' => '刪除失敗',
    'text_error_not_found' => '資料不存在',
    'text_error_system' => '系統發生錯誤，請聯絡管理員。',
];
