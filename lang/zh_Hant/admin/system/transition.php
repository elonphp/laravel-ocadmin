<?php

return [
    'heading_title' => '遷移功能',

    // 文字
    'text_intro'      => '執行 database/transitions/ 的資料庫遷移腳本（等同 SSH 內的 php artisan db:transition）。程式更新（Git 自動部署）後，於此一鍵套用尚未執行的 schema / 資料異動。僅 super_admin 可操作。',
    'text_list'       => '遷移腳本列表',
    'text_pending'    => '待執行',
    'text_failed'     => '失敗',
    'text_total'      => '共',
    'unit_item'       => '支',
    'text_output'     => '執行輸出',
    'text_running'    => '執行中，請勿關閉頁面…',
    'text_previewing' => '預覽中…',

    'text_confirm_run' => '確定要執行所有待執行的遷移？此操作會直接異動資料庫，且部分 DDL 無法還原。',
    'text_success_run' => '遷移執行完成',
    'error_run'        => '遷移執行失敗，請檢視下方輸出',

    // 欄位
    'column_status'      => '狀態',
    'column_version'     => '版本',
    'column_description' => '說明',
    'column_file'        => '檔名',
    'column_executed_at' => '執行時間',

    // 狀態
    'status_success' => '已執行',
    'status_failed'  => '失敗',
    'status_pending' => '待執行',

    // 按鈕
    'button_preview' => '預覽（dry-run）',
    'button_run'     => '執行遷移',

    // Help
    'text_help' => '此頁只能執行「已部署到伺服器上」的遷移腳本，無法 git pull。長時間的 ETL / backfill 腳本可能超過網頁逾時上限，仍建議透過 SSH 或於離峰時段執行。失敗的腳本會停在該支並記錄，修正後再次執行會自動從失敗處重試。',
];
