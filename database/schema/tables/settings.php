<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'group' => 'varchar:100|nullable|comment:群組',
        'code' => 'varchar:255|unique|comment:設定代碼',
        'value' => 'text|nullable',
        'type' => 'enum:text,line,json,serialized,bool,int,float,array|default:\'text\'|comment:設定值類型',
        'is_autoload' => 'tinyint|default:0|comment:啟動時自動載入至 Config',
        'note' => 'varchar:255|nullable|comment:備註',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
];
