<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'parent_id' => 'bigint|unsigned|nullable|index|foreign:companies.id',
        'code' => 'varchar:20|nullable|unique',
        'business_no' => 'varchar:20|nullable',
        'phone' => 'varchar:30|nullable',
        'address' => 'varchar:255|nullable',
        'is_active' => 'tinyint|default:1',
        'sort_order' => 'int|default:0',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:200',
        'short_name' => 'varchar:100|nullable',
    ],
];
