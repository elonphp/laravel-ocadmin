<?php

return [
    'heading_title' => 'Schema Management',

    // Text
    'text_list'               => 'Table List',
    'text_add'                => 'Add Table',
    'text_edit'               => 'Edit Table',
    'text_system'             => 'System',
    'text_synced'             => 'Synced',
    'text_diff'               => 'Diff',
    'text_db_only'            => 'DB Only',
    'text_schema_only'        => 'Schema Only',
    'text_success_save'       => 'Schema definition saved successfully!',
    'text_success_sync'       => 'Table synced successfully!',
    'text_success_export'     => 'Schema file exported successfully!',
    'text_success_export_all' => 'Exported schema files for %s tables!',
    'text_diff_preview'       => 'Diff Preview',
    'text_no_changes'         => 'No changes needed, structure is consistent',
    'text_sql_preview'        => 'SQL Preview',
    'text_confirm_sync'       => 'Are you sure you want to sync? This will modify the database structure directly.',
    'text_confirm_export_all' => 'Are you sure you want to export all tables? Existing schema files will be overwritten.',
    'text_pending_changes'    => 'Schema definition saved, but changes have not been applied to the table yet.',
    'text_no_pending'         => 'Table structure matches schema definition, no changes needed.',

    // Column
    'column_table_name'        => 'Table Name',
    'column_table_comment'     => 'Table Comment',
    'column_comment'           => 'Comment',
    'column_column_count'      => 'Columns',
    'column_translation_count' => 'Translation Columns',
    'column_status'            => 'Status',
    'column_changes'           => 'Changes',
    'column_column_name'       => 'Column Name',
    'column_type'              => 'Type',
    'column_length'            => 'Length',
    'column_unsigned'          => 'Unsigned',
    'column_nullable'          => 'Nullable',
    'column_default'           => 'Default',
    'column_primary'           => 'PK',
    'column_auto_inc'          => 'Auto Inc.',
    'column_index'             => 'Index',
    'column_unique'            => 'Unique',
    'column_foreign'           => 'Foreign Key',
    'column_index_name'        => 'Index Name',
    'column_index_type'        => 'Type',
    'column_index_columns'     => 'Columns',

    // Tab
    'tab_columns'      => 'Column Definition',
    'tab_translations' => 'Translation Columns',
    'tab_indexes'      => 'Indexes',

    // Button
    'button_add_index'       => 'Add Index',
    'button_add_column'      => 'Add Column',
    'button_add_translation' => 'Add Translation Column',
    'button_diff'            => 'Diff Preview',
    'button_sync'            => 'Sync',
    'button_apply'           => 'Apply Changes to Table',
    'button_export'          => 'Export',
    'button_export_all'      => 'Export All',

    // Help
    'help_table_name'    => 'Lowercase letters, numbers, and underscores, e.g. sal_orders',
    'help_foreign'       => 'Format: table.column, e.g. users.id',
    'help_length'        => 'varchar: character length, decimal: precision,scale',
    'help_unsigned'      => 'Unsigned',
    'help_nullable'      => 'Allow null',
    'help_primary'       => 'Primary key',
    'help_index_columns' => 'Comma-separated column names, e.g. col1, col2',

    // Filter
    'placeholder_search_table' => 'Search table name',

    // Error
    'error_table_name_required' => 'Table name is required',
    'error_no_columns'          => 'At least one column definition is required',
];
