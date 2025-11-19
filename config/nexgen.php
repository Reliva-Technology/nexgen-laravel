<?php 

use Reliva\Nexgen\Enum\NexgenEnvironment;

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
    'ENVIRONMENT' => env('NEXGEN_ENVIRONMENT', NexgenEnvironment::SANDBOX),

    // Your Nexgen API Key (find this in your Nexgen dashboard).
    'API_KEY' => env('NEXGEN_API_KEY'),

    // Your Nexgen API Secret (find this in your Nexgen dashboard).
    'API_SECRET' => env('NEXGEN_API_SECRET'),

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

    // Custom endpoint for QR API (used if QR_ENVIRONMENT is 'custom').
    'QR_ENDPOINT' => env('NEXGEN_QR_CUSTOM_ENDPOINT'),

    // Default QR terminal code (for Dynamic QR billing).
    'QR_TERMINAL_CODE' => env('NEXGEN_QR_TERMINAL_CODE'),

    // Default QR webhook callback URL (for Dynamic QR payments).
    'QR_CALLBACK_URL' => env('NEXGEN_QR_CALLBACK_URL'),

];