<?php

return [
    'global' => [
        'dir' => null,
    ],
    'ocadmin' => [
        'url_prefix'  => 'admin',
        'role_prefix' => 'admin',
        'dir'         => 'Ocadmin',
    ],
    'web' => [
        // url_prefix 省略：domain-based portal，不透過 path 區分
        'role_prefix' => 'web',
        'dir'         => 'Web',
    ],
];
