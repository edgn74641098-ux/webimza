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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'microsoft_graph' => [
        'sync_enabled' => filter_var(env('ENTRA_SYNC_ENABLED', false), FILTER_VALIDATE_BOOL),
        'tenant_id' => env('ENTRA_TENANT_ID', 'organizations'),
        'client_id' => env('ENTRA_CLIENT_ID'),
        'client_secret' => env('ENTRA_CLIENT_SECRET'),
        'sync_groups' => filter_var(env('ENTRA_SYNC_GROUPS', true), FILTER_VALIDATE_BOOL),
        'group_prefix' => env('ENTRA_GROUP_PREFIX'),
    ],

];
