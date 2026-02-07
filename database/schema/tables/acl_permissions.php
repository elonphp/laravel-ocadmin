<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'name' => 'varchar:255',
        'guard_name' => 'varchar:255',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'unique' => [
        'acl_permissions_name_guard_name_unique' => [
            'name',
            'guard_name',
        ],
    ],
    'translations' => [
        'display_name' => 'varchar:100',
        'note' => 'text|nullable',
    ],
];
