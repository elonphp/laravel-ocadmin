<?php

return [
    [
        'title' => 'ocadmin::menu.system',
        'icon' => 'cog',
        'children' => [
            [
                'title' => 'system-setting::menu.settings',
                'icon' => 'sliders',
                'route' => 'settings.index',
                'permission' => 'settings.view',
            ],
        ],
    ],
];
