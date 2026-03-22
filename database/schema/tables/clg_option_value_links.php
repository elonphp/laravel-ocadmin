<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'parent_option_value_id' => 'bigint|unsigned|foreign:clg_option_values.id',
        'child_option_value_id' => 'bigint|unsigned|index|foreign:clg_option_values.id',
    ],
    'unique' => [
        'ovl_parent_child_unique' => [
            'parent_option_value_id',
            'child_option_value_id',
        ],
    ],
];
