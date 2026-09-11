<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Screen Agent
    |--------------------------------------------------------------------------
    |
    | The embedded, screen-aware assistant. It answers questions about the data
    | on the current screen and offers proactive insights — always grounded in
    | the caller's row-level-scoped data (it can only ever see what the user can).
    |
    | Disabled by default: the feature is additive and inert until enabled.
    |
    */

    'enabled' => env('AGENT_ENABLED', false),

    /*
    | Provider strategy. 'simulated' produces deterministic, templated analysis
    | over the real scoped data (no external calls). Swap to a real LLM provider
    | (e.g. 'anthropic') later by adding a binding — the grounding and the
    | screen-context contract do not change.
    */
    'provider' => env('AGENT_PROVIDER', 'simulated'),

];
