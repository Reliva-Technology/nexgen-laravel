<?php


return [
    // Nexgen API configuration

    /*
    |--------------------------------------------------------------------------
    | Nexgen API Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains configuration settings for the Nexgen payment API.
    | Values are typically set via your .env file.
    |
    */

    // The environment for Nexgen API requests.
    // Supported: 'sandbox', 'production', 'custom'
    'ENVIRONMENT' => env('NEXGEN_ENVIRONMENT', 'sandbox'),

    // Your Nexgen Production API Key (find this in your Nexgen dashboard).
    'API_PROD_KEY' => env('NEXGEN_PROD_API_KEY'),

    // Your Nexgen Production API Secret (find this in your Nexgen dashboard).
    'API_PROD_SECRET' => env('NEXGEN_PROD_API_SECRET'),

    // Your Nexgen Production API Key (find this in your Nexgen dashboard).
    'API_SANDBOX_KEY' => env('NEXGEN_SANDBOX_API_KEY'),

    // Your Nexgen Sandbox API Secret (find this in your Nexgen dashboard).
    'API_SANDBOX_SECRET' => env('NEXGEN_SANDBOX_API_SECRET'),

    // Computed API key and secret based on ENVIRONMENT
    // Automatically selects sandbox or production keys based on ENVIRONMENT setting
    'API_KEY' => env('NEXGEN_ENVIRONMENT', 'sandbox') === 'sandbox'
        ? env('NEXGEN_SANDBOX_API_KEY')
        : env('NEXGEN_PROD_API_KEY'),

    'API_SECRET' => env('NEXGEN_ENVIRONMENT', 'sandbox') === 'sandbox'
        ? env('NEXGEN_SANDBOX_API_SECRET')
        : env('NEXGEN_PROD_API_SECRET'),

    // Nexgen custom endpoint (only used if ENVIRONMENT is set to 'custom').
    // Example: 'https://your-nexgen-endpoint.com'
    'ENDPOINT' => env('NEXGEN_CUSTOM_ENDPOINT'),

    // The default collection code used for billing (optional override).
    'COLLECTION_CODE' => env('NEXGEN_COLLECTION_CODE'),

    // Default webhook callback URL for payment updates (optional override).
    'CALLBACK_URL' => env('NEXGEN_CALLBACK_URL'),

    // Default URL to redirect users after payment (optional override).
    'REDIRECT_URL' => env('NEXGEN_REDIRECT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Nexgen QR API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for payment collection via QR codes. Used if your application
    | interacts with Nexgen's QR collection features.
    |
    */

    // Environment for QR API integration. Supported: 'production' or 'custom'. No sandbox support for QR API.
    'QR_ENVIRONMENT' => env('NEXGEN_QR_ENVIRONMENT', 'production'),

    // Your Nexgen QR Production API Key (find this in your Nexgen dashboard).
    'QR_API_PROD_KEY' => env('NEXGEN_QR_PROD_API_KEY'),

    // Your Nexgen QR Production API Secret (find this in your Nexgen dashboard).
    'QR_API_PROD_SECRET' => env('NEXGEN_QR_PROD_API_SECRET'),

    // Computed QR API key and secret based on QR_ENVIRONMENT
    // QR API only supports production/custom, so always uses production keys
    'QR_API_KEY' => env('NEXGEN_QR_PROD_API_KEY'),
    'QR_API_SECRET' => env('NEXGEN_QR_PROD_API_SECRET'),

    // Custom endpoint for QR API (used if QR_ENVIRONMENT is 'custom').
    'QR_ENDPOINT' => env('NEXGEN_QR_CUSTOM_ENDPOINT'),

    // Default QR terminal code (for Dynamic QR billing).
    'QR_TERMINAL_CODE' => env('NEXGEN_QR_TERMINAL_CODE'),

    // Default QR webhook callback URL (for Dynamic QR payments).
    'QR_CALLBACK_URL' => env('NEXGEN_QR_CALLBACK_URL'),

];
