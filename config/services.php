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

    'external_api' => [
        'access_key' => env('EXTERNAL_API_ACCESS_KEY'),
        'secret_key' => env('EXTERNAL_API_SECRET_KEY'),
        'base_url' => env('EXTERNAL_API_BASE_URL'),

        // MODO DEMO (temporal): cuando es true, /external-data devuelve
        // datos de ejemplo en lugar de consultar la API real.
        // Ponlo en false (o quítalo) cuando tengas las credenciales reales.
        'demo' => env('EXTERNAL_API_DEMO', false),
    ],
];