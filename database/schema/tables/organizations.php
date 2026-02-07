<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'business_no' => 'varchar:20|nullable|comment:統一編號',
        'shipping_state' => 'varchar:255|nullable|comment:州/省/縣市',
        'shipping_city' => 'varchar:255|nullable|comment:區/鄉/鎮',
        'shipping_address1' => 'varchar:255|nullable|comment:地址1',
        'shipping_address2' => 'varchar:255|nullable|comment:地址2',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:200',
        'short_name' => 'varchar:100|nullable',
    ],
];
