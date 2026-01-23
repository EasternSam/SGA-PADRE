<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    'wordpress' => [
        'base_uri' => env('WP_API_BASE_URI'),
        'secret' => env('WP_API_SECRET'),
    ],

    // --- CONFIGURACIÓN CARDNET (REDIRECCIÓN) ---
    'cardnet' => [
        'environment'     => env('CARDNET_ENV', 'sandbox'), // sandbox | production
        'merchant_id'     => env('CARDNET_MERCHANT_ID', '349000000'), 
        'terminal_id'     => env('CARDNET_TERMINAL_ID'),
        'currency'        => '214', // 214 = Peso Dominicano
        'url_sandbox'     => 'https://labservicios.cardnet.com.do/authorize', 
        'url_production'  => 'https://payments.cardnet.com.do/authorize',
    ],

];