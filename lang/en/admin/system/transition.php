<?php

return [
    'heading_title' => 'Transitions',

    // Text
    'text_intro'      => 'Run the database transition scripts in database/transitions/ (equivalent to php artisan db:transition over SSH). After a code deployment, apply pending schema / data changes here with one click. Super admin only.',
    'text_list'       => 'Transition Scripts',
    'text_pending'    => 'Pending',
    'text_failed'     => 'Failed',
    'text_total'      => 'Total',
    'unit_item'       => 'items',
    'text_output'     => 'Command Output',
    'text_running'    => 'Running, please do not close this page...',
    'text_previewing' => 'Previewing...',

    'text_confirm_run' => 'Run all pending transitions? This changes the database directly, and some DDL cannot be rolled back.',
    'text_success_run' => 'Transitions executed successfully',
    'error_run'        => 'Transition run failed, see the output below',

    // Columns
    'column_status'      => 'Status',
    'column_version'     => 'Version',
    'column_description' => 'Description',
    'column_file'        => 'File',
    'column_executed_at' => 'Executed At',

    // Status
    'status_success' => 'Executed',
    'status_failed'  => 'Failed',
    'status_pending' => 'Pending',

    // Buttons
    'button_preview' => 'Preview (dry-run)',
    'button_run'     => 'Run Transitions',

    // Help
    'text_help' => 'This page only runs transition scripts already deployed to the server; it cannot git pull. Long-running ETL / backfill scripts may exceed the web timeout, so run those over SSH or off-peak. A failed script stops the run and is recorded; fix it and run again to retry from the failure point.',
];
