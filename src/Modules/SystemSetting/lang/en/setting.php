<?php

return [
    'title' => 'System Settings',
    'list' => 'Settings List',
    'create' => 'Create Setting',
    'edit' => 'Edit Setting',

    // Fields
    'code' => 'Code',
    'group' => 'Group',
    'locale' => 'Locale',
    'type' => 'Type',
    'content' => 'Content',
    'note' => 'Note',

    // Placeholders
    'code_placeholder' => 'Enter code (e.g., site_name)',
    'group_placeholder' => 'Enter group (e.g., general, mail)',
    'locale_placeholder' => 'Enter locale code (e.g., zh-TW, en)',
    'content_placeholder' => 'Enter setting value',
    'note_placeholder' => 'Enter note',

    // Hints
    'code_hint' => 'Unique identifier for retrieving setting value in code',
    'group_hint' => 'Used for categorizing settings',
    'locale_hint' => 'Leave empty for global settings',
    'content_hint' => 'Enter content according to the selected type',
    'note_hint' => 'For administrator reference',

    // Type labels
    'type_text' => 'Text',
    'type_line' => 'Multi-line',
    'type_json' => 'JSON',
    'type_serialized' => 'Serialized',
    'type_bool' => 'Boolean',
    'type_int' => 'Integer',
    'type_float' => 'Float',
    'type_array' => 'Array',

    // Type hints
    'hint_text' => 'Enter plain text',
    'hint_line' => 'One item per line',
    'hint_json' => 'Enter valid JSON format',
    'hint_serialized' => 'Enter PHP serialized format',
    'hint_bool' => 'Enter 1 (yes) or 0 (no)',
    'hint_int' => 'Enter integer',
    'hint_float' => 'Enter decimal number',
    'hint_array' => 'Enter comma-separated values',

    // Messages
    'duplicate_code' => 'This code already exists (in the same locale)',
];
