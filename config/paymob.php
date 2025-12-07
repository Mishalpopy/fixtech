<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paymob API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used for Paymob Intention API (Unified Checkout).
    | Get them from your Paymob dashboard: https://uae.paymob.com/
    |
    */

    'api_key'       => env('PAYMOB_API_KEY'),
    'secret_key'    => env('PAYMOB_SECRET_KEY'), // Secret key for Intention API (Token authentication) - Backend only
    'public_key'    => env('PAYMOB_PUBLIC_KEY'), // Public key for Intention API (Frontend SDK initialization) - Safe to expose
    'integration_id' => env('PAYMOB_INTEGRATION_ID'), // Integration ID from Paymob Dashboard → Developers → Payment Integrations Tab
    'hmac'          => env('PAYMOB_HMAC'), // HMAC secret for webhook verification

    /*
    |--------------------------------------------------------------------------
    | Paymob API Base URL
    |--------------------------------------------------------------------------
    */

    'base_url'      => env('PAYMOB_BASE_URL', 'https://uae.paymob.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */

    'currency' => env('PAYMOB_CURRENCY', 'AED'),
    'timeout' => env('PAYMOB_TIMEOUT', 30), // API request timeout in seconds
];

