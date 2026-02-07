<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'company_id' => 'bigint|unsigned|index|foreign:companies.id',
        'parent_id' => 'bigint|unsigned|nullable|index|foreign:departments.id',
        'name' => 'varchar:100',
        'code' => 'varchar:20|nullable',
        'is_active' => 'tinyint|default:1',
        'sort_order' => 'int|default:0',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
];
