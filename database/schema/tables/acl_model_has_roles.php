<?php

return [
    'columns' => [
        'role_id' => 'bigint|unsigned|primary|foreign:acl_roles.id',
        'model_type' => 'varchar:255|primary',
        'model_id' => 'bigint|unsigned|primary',
    ],
    'indexes' => [
        'model_has_roles_model_id_model_type_index' => [
            'model_id',
            'model_type',
        ],
    ],
];
