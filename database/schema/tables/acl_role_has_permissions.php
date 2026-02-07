<?php

return [
    'columns' => [
        'permission_id' => 'bigint|unsigned|primary|foreign:acl_permissions.id',
        'role_id' => 'bigint|unsigned|primary|index|foreign:acl_roles.id',
    ],
];
