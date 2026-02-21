<?php

return [
    // Heading
    'heading_title' => 'Role Management',

    // Text
    'text_list' => 'Role List',
    'text_add' => 'Add Role',
    'text_edit' => 'Edit Role',
    'text_system' => 'System',

    // Column
    'column_name' => 'Role Code',
    'column_display_name' => 'Display Name',
    'column_guard_name' => 'Guard',
    'column_note' => 'Note',
    'column_sort_order' => 'Sort Order',
    'column_is_active' => 'Status',
    'column_search' => 'Keyword Search',

    // Placeholder
    'placeholder_name' => 'e.g. ess.hr_manager',
    'placeholder_display_name' => 'Enter display name',
    'placeholder_note' => 'Note',
    'placeholder_search' => 'Search code, name, note',

    // Help
    'help_name' => 'Format: {prefix}.{role}, lowercase letters, numbers, underscores, dots only',
    'help_guard_name' => 'Default is web, usually no need to change',

    // Tab
    'tab_permission' => 'Permission Assignment',

    // Permission Group
    'permission_group' => [
        'ess' => [
            'profile'    => 'Profile',
            'attendance' => 'Attendance',
            'leave'      => 'Leave',
            'payslip'    => 'Payslip',
        ],
        'mss' => [
            'employee'   => 'Employee Management',
            'department' => 'Department Management',
            'attendance' => 'Attendance Management',
            'leave'      => 'Leave Management',
        ],
    ],

    // Success
    'text_success_add' => 'Role added successfully!',
    'text_success_edit' => 'Role updated successfully!',

    // Error
    'error_has_users' => 'This role is assigned to users. Please remove user assignments first',
    'error_select_delete' => 'Please select items to delete',
    'error_batch_has_users' => 'Some roles are assigned to users. Please remove user assignments first',
];
