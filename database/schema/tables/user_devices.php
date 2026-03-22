<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'user_id' => 'bigint|unsigned|foreign:users.id',
        'device_name' => 'varchar:255',
        'device_fingerprint' => 'varchar:64',
        'ip_address' => 'varchar:45',
        'location' => 'varchar:255|nullable',
        'last_active_at' => 'timestamp|nullable',
        'is_current' => 'tinyint|default:0',
        'trusted_until' => 'timestamp|nullable',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'indexes' => [
        'user_devices_user_id_device_fingerprint_index' => [
            'user_id',
            'device_fingerprint',
        ],
    ],
];
