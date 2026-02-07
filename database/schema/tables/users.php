<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'name' => 'varchar:255',
        'email' => 'varchar:255|unique',
        'email_verified_at' => 'timestamp|nullable',
        'password' => 'varchar:255',
        'username' => 'varchar:255|unique',
        'first_name' => 'varchar:255|nullable',
        'last_name' => 'varchar:255|nullable',
        'remember_token' => 'varchar:100|nullable',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
];
