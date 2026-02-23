<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider to use for chat completions and embeddings.
    | Supported: "openai", "anthropic", "azure", "ollama"
    |
    */

    'ai_provider' => env('IDEA_ROOM_AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configurations
    |--------------------------------------------------------------------------
    */

    'ai_providers' => [

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 4096),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5-20250929'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 4096),
            'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Orchestrator Configuration
    |--------------------------------------------------------------------------
    */

    'orchestrator' => [
        'temperature' => 0.7,
        'max_tokens' => 2048,
    ],

    /*
    |--------------------------------------------------------------------------
    | Room Agent Configuration
    |--------------------------------------------------------------------------
    */

    'room_agent' => [
        'temperature' => 0.7,
        'max_tokens' => 4096,
        'max_memories_in_context' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resurfacing Configuration
    |--------------------------------------------------------------------------
    */

    'resurfacing' => [
        'dormant_days' => 30,
        'max_suggestions_per_day' => 3,
        'enabled' => env('IDEA_ROOM_RESURFACING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery Configuration
    |--------------------------------------------------------------------------
    */

    'gallery' => [
        'templates_path' => base_path('data/gallery-templates'),
        'default_template' => env('GALLERY_DEFAULT_TEMPLATE', 'minimal-4'),
        'image_source' => env('GALLERY_IMAGE_SOURCE', 'pexels'),

        'cache' => [
            'directory' => storage_path('app/gallery/cache'),
            'style_version' => env('GALLERY_STYLE_VERSION', 'v1'),
        ],

        'output' => [
            'directory' => storage_path('app/gallery/output'),
            'filename' => 'gallery-wall.png',
        ],

        'pexels' => [
            'api_key' => env('PEXELS_API_KEY', ''),
            'orientation' => 'landscape',
        ],

        'unsplash' => [
            'access_key' => env('UNSPLASH_ACCESS_KEY', ''),
            'orientation' => 'landscape',
        ],

        'dalle' => [
            'model' => env('DALLE_MODEL', 'dall-e-3'),
            'size' => env('DALLE_SIZE', '1024x1024'),
            'quality' => env('DALLE_QUALITY', 'standard'),
            'style' => env('DALLE_STYLE', 'vivid'),
        ],

        'debug_overlay' => env('GALLERY_DEBUG', false),

        'prompt_template' => 'A high-quality artistic painting for an office gallery wall, '
            .'representing the concept of ":room_name". '
            .'Style: modern impressionist, warm lighting, professional aesthetic. '
            .'No text, no words, no logos, no watermarks, no faces.',
    ],

];
