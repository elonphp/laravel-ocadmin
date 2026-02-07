<?php

return [
    'columns' => [
        'company_id' => 'bigint|unsigned|primary|foreign:companies.id',
        'user_id' => 'bigint|unsigned|primary|index|foreign:users.id',
    ],
];
