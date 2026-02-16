<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | The current version of the application. This is baked into the Docker
    | image at build time and used by the agent to report the running version
    | to the control plane.
    |
    */

    'current' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Agent Status Path
    |--------------------------------------------------------------------------
    |
    | The path to the shared volume where the agent writes status files
    | (version-check.json) and the app writes acceptance files
    | (update-accepted.json). This volume is mounted read-write for the
    | app and read-write for the agent.
    |
    */

    'agent_status_path' => env('AGENT_STATUS_PATH', '/var/lib/pm-agent/status'),

];
