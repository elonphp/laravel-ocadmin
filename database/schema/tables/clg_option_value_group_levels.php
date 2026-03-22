<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'option_value_group_id' => 'bigint|unsigned|foreign:clg_option_value_groups.id',
        'option_id' => 'bigint|unsigned|index|foreign:clg_options.id',
        'level' => 'tinyint|unsigned',
    ],
    'unique' => [
        'ovg_levels_group_level_unique' => [
            'option_value_group_id',
            'level',
        ],
        'ovg_levels_group_option_unique' => [
            'option_value_group_id',
            'option_id',
        ],
    ],
];
