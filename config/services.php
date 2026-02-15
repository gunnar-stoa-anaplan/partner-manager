<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anaplan' => [
        'username' => env('ANAPLAN_USERNAME'),
        'password' => env('ANAPLAN_PASSWORD'),
        'auth_url' => env('ANAPLAN_AUTH_URL', 'https://auth.anaplan.com/token/authenticate'),
        'api_url' => env('ANAPLAN_API_URL', 'https://api.anaplan.com/2/0'),
    ],

    'anaplan_ifp' => [
        'username' => env('ANAPLAN_IFP_USERNAME'),
        'password' => env('ANAPLAN_IFP_PASSWORD'),
        'auth_url' => env('ANAPLAN_IFP_AUTH_URL', 'https://auth.anaplan.com/token/authenticate'),
        'api_url' => env('ANAPLAN_IFP_API_URL', 'https://api.anaplan.com/2/0'),
    ],

    'anaplan_owp' => [
        'username' => env('ANAPLAN_OWP_USERNAME'),
        'password' => env('ANAPLAN_OWP_PASSWORD'),
        'auth_url' => env('ANAPLAN_OWP_AUTH_URL', 'https://auth.anaplan.com/token/authenticate'),
        'api_url' => env('ANAPLAN_OWP_API_URL', 'https://api.anaplan.com/2/0'),
    ],

    'anaplan_spm' => [
        'username' => env('ANAPLAN_SPM_USERNAME'),
        'password' => env('ANAPLAN_SPM_PASSWORD'),
        'auth_url' => env('ANAPLAN_SPM_AUTH_URL', 'https://auth.anaplan.com/token/authenticate'),
        'api_url' => env('ANAPLAN_SPM_API_URL', 'https://api.anaplan.com/2/0'),
    ],

];
