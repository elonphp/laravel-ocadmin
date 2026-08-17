<?php

return [
    'heading_title' => 'Access Token',
    'text_add'      => 'Add Access Token',
    'text_edit'     => 'Edit Access Token',
    'text_list'     => 'Access Token List',

    // Columns
    'column_search'       => 'Keyword',
    'column_user'         => 'User',
    'column_user_source'  => 'User Source',
    'column_username'     => 'Username',
    'column_local_name'   => 'Display Name',
    'column_abilities'    => 'Abilities',
    'column_last_used_at' => 'Last Used',
    'column_expires_at'   => 'Expires At',

    // Text
    'text_existing_user'  => 'Select Existing User',
    'text_create_local'   => 'Create Local Account',
    'text_no_expiry'      => 'No Expiry',
    'text_expired'        => 'Expired',
    'text_active'         => 'Active',
    'text_token_created'  => 'Access Token Created',
    'text_token_warning'  => 'Copy this Token now. It will not be shown again!',
    'text_copied'         => 'Copied',
    'text_user_deleted'   => '(Deleted)',
    'text_confirm_revoke' => 'Are you sure you want to revoke %s selected Access Token(s)? They will no longer be usable.',

    // Placeholder
    'placeholder_search'      => 'Name or user',
    'placeholder_user_search' => 'Search by user name',
    'placeholder_username'    => 'e.g. svc_api',
    'placeholder_local_name'  => 'e.g. API Service Account',
    'placeholder_name'        => 'e.g. portal_api, webhook_xxx',

    // Help
    'help_user_search' => 'Auto-search after typing',
    'help_username'    => 'Username, must be unique. users.username',
    'help_local_name'  => 'User display name. users.name',
    'help_name'        => 'Name to identify this Access Token. personal_access_tokens.name',
    'help_abilities'   => 'Select which Portal(s) this Token can access',
    'help_expires_at'    => 'Optional. Leave blank for no expiry',
    'help_user_readonly' => 'User binding cannot be changed',

    // Buttons
    'button_revoke' => 'Revoke',
    'button_copy'   => 'Copy',

    // Success / Error
    'text_success_create' => 'Access Token created successfully',
    'text_success_update' => 'Access Token updated successfully',
    'text_success_revoke' => 'Revoked successfully',
    'text_error_create'   => 'Create failed',
    'text_error_revoke'   => 'Revoke failed',
    'error_select_revoke' => 'Please select items to revoke',

    // Validation
    'error_name_required'       => 'Name is required',
    'error_abilities_required'  => 'Please select at least one ability',
    'error_expires_at_after'    => 'Expiry date must be after today',
    'error_username_required'   => 'Username is required',
    'error_username_unique'     => 'Username already exists',
    'error_local_name_required' => 'Display name is required',
    'error_user_id_required'    => 'Please select a user',
    'error_user_id_exists'      => 'User does not exist',
];
