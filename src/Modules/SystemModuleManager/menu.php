<?php

return [
    [
        'title' => 'ocadmin::menu.system',
        'icon' => 'cog',
        'children' => [
            [
                'title' => 'system-module-manager::menu.modules',
                'icon' => 'puzzle-piece',
                'route' => 'modules.index',
                'permission' => 'modules.view',
            ],
        ],
    ],
];
