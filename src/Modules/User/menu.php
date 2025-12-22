<?php

return [
    [
        'title' => 'user::menu.account_management',
        'icon' => 'users',
        'children' => [
            [
                'title' => 'user::menu.users',
                'icon' => 'user',
                'route' => 'users.index',
                'permission' => 'users.view',
            ],
        ],
    ],
];
