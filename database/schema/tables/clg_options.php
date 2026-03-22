<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'code' => 'varchar:50|nullable|unique',
        'type' => 'varchar:20|default:\'select\'',
        'sort_order' => 'int|default:0',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:128',
    ],
];
