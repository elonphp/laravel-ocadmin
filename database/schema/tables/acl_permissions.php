<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'parent_id' => 'bigint|unsigned|nullable|index|foreign:acl_permissions.id',
        'name' => 'varchar:255',
        'guard_name' => 'varchar:255',
        'type' => 'varchar:255|nullable|index|comment:menu or action',
        'icon' => 'varchar:255|nullable',
        'sort_order' => 'int|unsigned|default:0',
        'is_active' => 'tinyint|default:1',
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
