<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'option_id' => 'bigint|unsigned|index|foreign:ctl_options.id',
        'code' => 'varchar:50|nullable',
        'image' => 'varchar:255|nullable',
        'sort_order' => 'int|default:0',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:128',
    ],
];
