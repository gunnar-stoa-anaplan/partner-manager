<?php

return [
    'user_source' => env('TEAM_USER_SOURCE', 'local'),

    'directory' => [
        'synced_fields' => ['name', 'email', 'manager_id'],
        'local_fields' => ['role', 'avatar', 'preferences', 'timezone'],
        'default_role' => 'psm',
    ],
];
