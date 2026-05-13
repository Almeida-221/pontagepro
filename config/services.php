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

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

    'sendtext' => [
        'url'         => env('SENDTEXT_API_URL', 'https://api.sendtext.sn/v1/sms'),
        'api_key'     => env('SENDTEXT_API_KEY'),
        'api_secret'  => env('SENDTEXT_API_SECRET'),
        'sender_name' => env('SENDTEXT_SENDER_NAME', 'EPSILON'),
    ],

    'app_downloads' => [
        'mob' => env('APP_MOB_DOWNLOAD_URL'),
        'sec' => env('APP_SEC_DOWNLOAD_URL'),
    ],

];
