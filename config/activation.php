<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activation Telemetry
    |--------------------------------------------------------------------------
    |
    | When enabled, the instance will include feature activation progression
    | snapshots in its heartbeat payload to the control plane. This allows
    | fleet-wide visibility into which tenants are approaching feature
    | unlocks and where they may be stalling.
    |
    | Set to false to opt out of reporting activation data.
    |
    */

    'telemetry_enabled' => env('ACTIVATION_TELEMETRY', true),

];
