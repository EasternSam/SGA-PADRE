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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Wordpress API
    'wordpress' => [
        'base_uri' => env('WP_API_BASE_URI'),
        'secret' => env('WP_API_SECRET'),
    ],

    // INTEGRACIÓN CARDNET (CORREGIDA)
    'cardnet' => [
        'environment' => env('CARDNET_ENV', 'sandbox'), // sandbox o production
        'public_key'  => env('CARDNET_PUBLIC_KEY'),
        'private_key' => env('CARDNET_PRIVATE_KEY'),
        'image_url'   => env('CARDNET_IMAGE_URL', 'https://centu.edu.do/wp-content/uploads/2021/08/logo-centu.png'),
    ],

];