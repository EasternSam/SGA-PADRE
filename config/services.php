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

    // ====================================================================
    // CONFIGURACIÓN PARA LA API DE WORDPRESS
    // ====================================================================
    'wordpress' => [
        'base_uri' => env('WP_API_BASE_URI'),
        'secret' => env('WP_API_SECRET'),
    ],
    
    // ====================================================================
    // CONFIGURACIÓN DE CARDNET (TOKENIZACIÓN)
    // ====================================================================
    'cardnet' => [
        // CORRECCIÓN CRÍTICA: URL exacta del proveedor tecnológico (Seglan)
        // Esta URL evita el error de "origen no permitido" del script JS.
        'base_uri' => env('CARDNET_BASE_URI', 'https://tr-tsp-test.gtp-seglan.com/tr-tsp-mw-cardnet/v1'),
        
        // Llaves proporcionadas por Cardnet
        'public_key' => env('CARDNET_PUBLIC_KEY', 'J_eHXPYlDo9wlFpFXjgalm_I56ONV7HQ'), 
        'private_key' => env('CARDNET_PRIVATE_KEY', '9kYH2uY5zoTD-WBMEoc0KNRQYrC7crPRJ7zPegg3suXguw_8L-rZDQ'), 
        
        'image_url' => env('CARDNET_IMAGE_URL', 'https://www.cardnet.com.do/capp/images/logo_nuevo_x_2.png'),
    ],
    // ====================================================================

];