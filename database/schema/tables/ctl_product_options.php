<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'product_id' => 'bigint|unsigned|index|foreign:ctl_products.id',
        'option_id' => 'bigint|unsigned|index|foreign:ctl_options.id',
        'value' => 'text|nullable',
        'required' => 'tinyint|default:0',
    ],
];
