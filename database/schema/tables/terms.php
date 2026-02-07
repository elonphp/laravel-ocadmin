<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'taxonomy_id' => 'bigint|unsigned|foreign:taxonomies.id',
        'parent_id' => 'bigint|unsigned|nullable|index|foreign:terms.id',
        'code' => 'varchar:50',
        'sort_order' => 'int|default:0',
        'is_active' => 'tinyint|default:1',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'unique' => [
        'terms_taxonomy_id_code_unique' => [
            'taxonomy_id',
            'code',
        ],
    ],
    'translations' => [
        'name' => 'varchar:100',
    ],
];
