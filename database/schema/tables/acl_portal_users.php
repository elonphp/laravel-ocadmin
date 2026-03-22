<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'user_id' => 'bigint|unsigned|foreign:users.id',
        'portal' => 'varchar:20|comment:Portal 識別碼（admin, hrm, www, ...）',
        'enrolled_at' => 'timestamp|nullable',
        'revoked_at' => 'timestamp|nullable',
        'last_login_at' => 'timestamp|nullable',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'unique' => [
        'acl_portal_users_user_id_portal_unique' => [
            'user_id',
            'portal',
        ],
    ],
];
