<?php

return [
    'columns' => [
        'permission_id' => 'bigint|unsigned|primary|foreign:acl_permissions.id',
        'model_type' => 'varchar:255|primary',
        'model_id' => 'bigint|unsigned|primary',
    ],
    'indexes' => [
        'model_has_permissions_model_id_model_type_index' => [
            'model_id',
            'model_type',
        ],
    ],
];
