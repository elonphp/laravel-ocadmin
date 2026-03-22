<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'product_id' => 'bigint|unsigned|index|foreign:clg_products.id',
        'option_id' => 'bigint|unsigned|index|foreign:clg_options.id',
        'value' => 'text|nullable',
        'required' => 'tinyint|default:0',
    ],
];
