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
    'supabase' => [
        'url' => env('SUPABASE_URL'),

        'key' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY'),

        'bucket' => env('SUPABASE_BUCKET'),
        'reports_bucket' => env('SUPABASE_REPORTS_BUCKET'),
        'voices' => env('SUPABASE_EVALUATION_VOICE_BUCKET'),
        'backups_bucket' => env(
            'SUPABASE_BACKUPS_BUCKET',
            'backups'
        ),
        'backup_pg_dump_path' => env(
            'BACKUP_PG_DUMP_PATH',
            'pg_dump'
        ),
    ],

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
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency'       => env('STRIPE_CURRENCY', 'usd'),
    ],

];
