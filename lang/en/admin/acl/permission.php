<?php

return [
    // Heading
    'heading_title' => 'Permission Management',

    // Text
    'text_list' => 'Permission List',
    'text_add' => 'Add Permission',
    'text_edit' => 'Edit Permission',
    'text_system' => 'System',

    // Column
    'column_name' => 'Permission Code',
    'column_display_name' => 'Display Name',
    'column_guard_name' => 'Guard',
    'column_note' => 'Note',
    'column_search' => 'Keyword Search',

    // Placeholder
    'placeholder_name' => 'e.g. mss.employee.list',
    'placeholder_display_name' => 'Enter display name',
    'placeholder_note' => 'Note',
    'placeholder_search' => 'Search code, name, note',

    // Help
    'help_name' => 'Three-part format: {module}.{resource}.{action}, lowercase letters, numbers, underscores, dots only',
    'help_guard_name' => 'Default is web, usually no need to change',

    // Success
    'text_success_add' => 'Permission added successfully!',
    'text_success_edit' => 'Permission updated successfully!',

    // Error
    'error_has_roles' => 'This permission is assigned to roles. Please remove role assignments first',
    'error_select_delete' => 'Please select items to delete',
    'error_batch_has_roles' => 'Some permissions are assigned to roles. Please remove role assignments first',
];
