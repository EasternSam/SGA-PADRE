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
        // URL base de la API (Lab o Prod)
        'base_uri' => env('CARDNET_BASE_URI', 'https://lab.cardnet.com.do/servicios/tokens/v1'),
        
        // Llaves proporcionadas por Cardnet (extraídas de tu documentación)
        'public_key' => env('CARDNET_PUBLIC_KEY', 'J_eHXPYlDo9wlFpFXjgalm_I56ONV7HQ'), // Key para el script JS
        'private_key' => env('CARDNET_PRIVATE_KEY', '9kYH2uY5zoTD-WBMEoc0KNRQYrC7crPRJ7zPegg3suXguw_8L-rZDQ'), // Key para API Server-to-Server
        
        // Configuración visual del Lightbox
        'image_url' => env('CARDNET_IMAGE_URL', 'https://www.cardnet.com.do/capp/images/logo_nuevo_x_2.png'),
    ],
    // ====================================================================

];