<?php

return [
    'title' => '參數設定',
    'list' => '參數列表',
    'create' => '新增參數',
    'edit' => '編輯參數',

    // Fields
    'code' => '代碼',
    'group' => '群組',
    'locale' => '語系',
    'type' => '類型',
    'content' => '內容',
    'note' => '備註',

    // Placeholders
    'code_placeholder' => '請輸入代碼（如：site_name）',
    'group_placeholder' => '請輸入群組（如：general、mail）',
    'locale_placeholder' => '請輸入語系代碼（如：zh-TW、en）',
    'content_placeholder' => '請輸入設定值',
    'note_placeholder' => '請輸入備註說明',

    // Hints
    'code_hint' => '唯一識別碼，用於程式取得設定值',
    'group_hint' => '用於將設定分類管理',
    'locale_hint' => '留空表示全域設定',
    'content_hint' => '根據類型輸入對應格式的內容',
    'note_hint' => '供管理人員參考用',

    // Type labels
    'type_text' => '純文字',
    'type_line' => '多行文字',
    'type_json' => 'JSON',
    'type_serialized' => '序列化',
    'type_bool' => '布林值',
    'type_int' => '整數',
    'type_float' => '小數',
    'type_array' => '陣列',

    // Type hints
    'hint_text' => '輸入純文字',
    'hint_line' => '一行一個項目',
    'hint_json' => '輸入有效的 JSON 格式',
    'hint_serialized' => '輸入 PHP 序列化格式',
    'hint_bool' => '輸入 1（是）或 0（否）',
    'hint_int' => '輸入整數',
    'hint_float' => '輸入小數',
    'hint_array' => '輸入逗號分隔的值',

    // Messages
    'duplicate_code' => '此代碼已存在（相同語系下）',
];
