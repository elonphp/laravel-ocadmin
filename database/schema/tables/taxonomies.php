<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'code' => 'varchar:50|unique',
        'description' => 'varchar:255|nullable',
        'sort_order' => 'int|default:0',
        'is_active' => 'tinyint|default:1',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:100',
    ],
];
