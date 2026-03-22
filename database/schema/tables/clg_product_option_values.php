<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'product_option_id' => 'bigint|unsigned|index|foreign:clg_product_options.id',
        'product_id' => 'bigint|unsigned|index|foreign:clg_products.id',
        'option_id' => 'bigint|unsigned|index|foreign:clg_options.id',
        'option_value_id' => 'bigint|unsigned|index|foreign:clg_option_values.id',
        'quantity' => 'int|default:0',
        'subtract' => 'tinyint|default:0',
        'price' => 'decimal:15,4|default:0.0000',
        'price_prefix' => 'varchar:1|default:\'+\'',
        'weight' => 'decimal:15,8|default:0.00000000',
        'weight_prefix' => 'varchar:1|default:\'+\'',
    ],
];
