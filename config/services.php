<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    | Wompi (pasarela de pagos). Llaves en el panel de Wompi > Desarrolladores.
    | WOMPI_ENV: sandbox (llaves *_test_*) o production (llaves *_prod_*).
    */
    'wompi' => [
        'env' => env('WOMPI_ENV', 'sandbox'),
        'public_key' => env('WOMPI_PUBLIC_KEY'),
        'private_key' => env('WOMPI_PRIVATE_KEY'),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET'),
        'events_secret' => env('WOMPI_EVENTS_SECRET'),
        'checkout_url' => env('WOMPI_CHECKOUT_URL', 'https://checkout.wompi.co/p/'),
        'api_url' => env('WOMPI_API_URL', env('WOMPI_ENV', 'sandbox') === 'production'
            ? 'https://production.wompi.co/v1'
            : 'https://sandbox.wompi.co/v1'),
        'currency' => 'COP',
        'min_amount' => (int) env('DONATION_MIN_AMOUNT', 5000),
        'max_amount' => (int) env('DONATION_MAX_AMOUNT', 10000000),
    ],

];
