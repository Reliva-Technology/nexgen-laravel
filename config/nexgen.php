<?php

/**
 * Nexgen Payment Gateway Configuration
 *
 * This file defines configuration for the Reliva Nexgen package, which integrates
 * with the Nexgen payment API for Online Banking / MPGS,
 * and Duitnow Dynamic QR.
 *
 * Values are read from the environment. After publishing this config, set the
 * corresponding variables in your .env file. Never commit API keys or secrets.
 *
 * Publish this config: php artisan vendor:publish --tag=nexgen-config
 *
 * @see https://github.com/reliva/nexgen
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Nexgen Collections & Billings API (NexgenClient)
    |--------------------------------------------------------------------------
    |
    | Used for link-based payments: collections, billings, and DuitNow. Set
    | ENVIRONMENT and the matching API key/secret for that environment.
    |
    */

    /*
    | Environment for Collections/Billings API requests.
    | .env: NEXGEN_ENVIRONMENT
    | Values: 'sandbox' | 'production' | 'custom'
    | Required: Yes. When 'custom', also set NEXGEN_CUSTOM_ENDPOINT.
    */
    'ENVIRONMENT' => env('NEXGEN_ENVIRONMENT', 'sandbox'),

    /*
    | Production API credentials (used when ENVIRONMENT is 'production' or 'custom').
    | .env: NEXGEN_PROD_API_KEY, NEXGEN_PROD_API_SECRET
    | Required: Yes when ENVIRONMENT is production or custom.
    */
    'API_PROD_KEY' => env('NEXGEN_PROD_API_KEY'),
    'API_PROD_SECRET' => env('NEXGEN_PROD_API_SECRET'),

    /*
    | Sandbox API credentials (used when ENVIRONMENT is 'sandbox').
    | .env: NEXGEN_SANDBOX_API_KEY, NEXGEN_SANDBOX_API_SECRET
    | Required: Yes when ENVIRONMENT is sandbox.
    */
    'API_SANDBOX_KEY' => env('NEXGEN_SANDBOX_API_KEY'),
    'API_SANDBOX_SECRET' => env('NEXGEN_SANDBOX_API_SECRET'),

    /*
    | Resolved API key and secret for the current ENVIRONMENT.
    | Do not set these in .env; they are derived from the keys above.
    */
    'API_KEY' => env('NEXGEN_ENVIRONMENT', 'sandbox') === 'sandbox'
        ? env('NEXGEN_SANDBOX_API_KEY')
        : env('NEXGEN_PROD_API_KEY'),
    'API_SECRET' => env('NEXGEN_ENVIRONMENT', 'sandbox') === 'sandbox'
        ? env('NEXGEN_SANDBOX_API_SECRET')
        : env('NEXGEN_PROD_API_SECRET'),

    /*
    | Custom API base URL. Only used when ENVIRONMENT is 'custom'.
    | .env: NEXGEN_CUSTOM_ENDPOINT
    | Example: https://your-nexgen-endpoint.com
    */
    'ENDPOINT' => env('NEXGEN_CUSTOM_ENDPOINT'),

    /*
    | Default collection code for creating billings. Can be overridden per request.
    | .env: NEXGEN_COLLECTION_CODE
    | Optional.
    */
    'COLLECTION_CODE' => env('NEXGEN_COLLECTION_CODE'),

    /*
    | Webhook URL where Nexgen sends payment status updates (e.g. paid, failed).
    | .env: NEXGEN_CALLBACK_URL
    | Optional; can be overridden per billing or in NexgenClient constructor.
    */
    'CALLBACK_URL' => env('NEXGEN_CALLBACK_URL'),

    /*
    | URL to redirect the customer after payment (success or failure).
    | .env: NEXGEN_REDIRECT_URL
    | Optional; can be overridden per billing or in NexgenClient constructor.
    */
    'REDIRECT_URL' => env('NEXGEN_REDIRECT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Nexgen QR API (NexgenQRClient)
    |--------------------------------------------------------------------------
    |
    | Used for Dynamic QR payments (terminals, QR billing). QR API has no
    | sandbox; use production or a custom endpoint for testing.
    |
    */

    /*
    | Environment for QR API. No sandbox; use 'production' or 'custom'.
    | .env: NEXGEN_QR_ENVIRONMENT
    | Values: 'production' | 'custom'
    */
    'QR_ENVIRONMENT' => env('NEXGEN_QR_ENVIRONMENT', 'production'),

    /*
    | QR API production credentials.
    | .env: NEXGEN_QR_PROD_API_KEY, NEXGEN_QR_PROD_API_SECRET
    | Required when using NexgenQRClient.
    */
    'QR_API_PROD_KEY' => env('NEXGEN_QR_PROD_API_KEY'),
    'QR_API_PROD_SECRET' => env('NEXGEN_QR_PROD_API_SECRET'),

    /*
    | Resolved QR API key and secret (derived from QR_ENVIRONMENT).
    | Do not set in .env.
    */
    'QR_API_KEY' => env('NEXGEN_QR_PROD_API_KEY'),
    'QR_API_SECRET' => env('NEXGEN_QR_PROD_API_SECRET'),

    /*
    | Custom base URL for QR API. Only used when QR_ENVIRONMENT is 'custom'.
    | .env: NEXGEN_QR_CUSTOM_ENDPOINT
    */
    'QR_ENDPOINT' => env('NEXGEN_QR_CUSTOM_ENDPOINT'),

    /*
    | Default terminal code for Dynamic QR billing.
    | .env: NEXGEN_QR_TERMINAL_CODE
    | Optional; can be overridden when creating QR payments.
    */
    'QR_TERMINAL_CODE' => env('NEXGEN_QR_TERMINAL_CODE'),

    /*
    | Webhook URL for QR payment status updates.
    | .env: NEXGEN_QR_CALLBACK_URL
    | Optional.
    */
    'QR_CALLBACK_URL' => env('NEXGEN_QR_CALLBACK_URL'),

];
