<?php

return [
    'columns' => [
        'id' => 'bigint|unsigned|auto_increment|primary',
        'user_id' => 'bigint|unsigned|nullable|index|foreign:users.id',
        'company_id' => 'bigint|unsigned|nullable|index|foreign:companies.id',
        'department_id' => 'bigint|unsigned|nullable|index|foreign:departments.id',
        'employee_no' => 'varchar:20|nullable|unique',
        'first_name' => 'varchar:50',
        'last_name' => 'varchar:50|nullable',
        'email' => 'varchar:100|nullable',
        'phone' => 'varchar:30|nullable',
        'hire_date' => 'date|nullable',
        'birth_date' => 'date|nullable',
        'gender' => 'varchar:10|nullable|comment:male / female / other',
        'job_title' => 'varchar:100|nullable',
        'address' => 'text|nullable',
        'note' => 'text|nullable',
        'is_active' => 'tinyint|default:1',
        'created_at' => 'timestamp|nullable',
        'updated_at' => 'timestamp|nullable',
    ],
];
