<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chunking Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how documents are split into chunks for embedding. Chunk size
    | is measured in words. Overlap creates continuity between adjacent chunks.
    |
    */

    'chunking' => [
        'chunk_size' => 750,
        'overlap_size' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Configuration
    |--------------------------------------------------------------------------
    |
    | Dimensions must match your embedding provider's output. OpenAI's
    | text-embedding-3-small produces 1536-dimensional vectors.
    |
    */

    'embedding' => [
        'dimensions' => 1536,
        'batch_size' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */

    'search' => [
        'min_similarity' => 0.5,
        'default_limit' => 10,
        'prefetch_limit' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    */

    'storage' => [
        'disk' => 'local',
        'path' => 'knowledge-base',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported File Extensions
    |--------------------------------------------------------------------------
    */

    'supported_extensions' => ['pdf', 'docx', 'xlsx', 'xls', 'txt', 'md', 'html', 'csv'],

];
