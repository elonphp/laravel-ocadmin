<?php

return [
    [
        'title' => 'system-log::menu.system_management',
        'icon' => 'cog',
        'children' => [
            [
                'title' => 'system-log::menu.system_logs',
                'icon' => 'file-text',
                'permission' => 'system.logs.view',
                'children' => [
                    [
                        'title' => 'system-log::menu.database_logs',
                        'route' => 'system.logs.database',
                        'permission' => 'system.logs.view',
                    ],
                    [
                        'title' => 'system-log::menu.archived_logs',
                        'route' => 'system.logs.archived',
                        'permission' => 'system.logs.view',
                    ],
                ],
            ],
        ],
    ],
];
