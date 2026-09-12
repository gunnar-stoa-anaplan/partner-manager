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

    /*
    |--------------------------------------------------------------------------
    | Anthropic provider (Phase 2)
    |--------------------------------------------------------------------------
    |
    | Used only when 'provider' is 'anthropic'. The provider sends the already
    | row-level-scoped screen facts to the Messages API and grounds every answer
    | in them. With no api_key set (or on any API error) it degrades gracefully
    | to the deterministic simulated provider, so switching providers without a
    | key never breaks the feature.
    |
    | Egress note: enabling this sends scoped business figures (partner names,
    | pipeline values) to Anthropic. Keep it opt-in and review your data policy.
    |
    */

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('AGENT_ANTHROPIC_MODEL', 'claude-sonnet-5'),
        'max_tokens' => env('AGENT_ANTHROPIC_MAX_TOKENS', 1024),
        'base_uri' => env('ANTHROPIC_BASE_URI', 'https://api.anthropic.com'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
        'timeout' => env('AGENT_ANTHROPIC_TIMEOUT', 20),
    ],

];
