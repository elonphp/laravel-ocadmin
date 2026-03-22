<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'model' => 'varchar:64|default:\'\'',
        'image' => 'varchar:255|nullable',
        'price' => 'decimal:15,4|default:0.0000',
        'quantity' => 'int|default:0',
        'minimum' => 'int|default:1',
        'subtract' => 'tinyint|default:1',
        'shipping' => 'tinyint|default:1',
        'status' => 'tinyint|default:1',
        'sort_order' => 'int|default:0',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
    'translations' => [
        'name' => 'varchar:255',
        'description' => 'text|nullable',
        'meta_title' => 'varchar:255|nullable',
        'meta_keyword' => 'varchar:255|nullable',
        'meta_description' => 'text|nullable',
    ],
];
