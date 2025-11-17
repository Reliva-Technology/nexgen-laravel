<?php 
return [
    // Nexgen API configuration

    'environment' => env('NEXGEN_ENVIRONMENT', 'sandbox'),
    'API_KEY' => env('NEXGEN_API_KEY'),
    'API_SECRET' => env('NEXGEN_API_SECRET'),
    'ENDPOINT' => env('NEXGEN_CUSTOM_ENDPOINT'),

    'COLLECTION_CODE' => env('NEXGEN_COLLECTION_CODE'),

    'CALLBACK_URL' => env('NEXGEN_CALLBACK_URL'),
    'REDIRECT_URL' => env('NEXGEN_REDIRECT_URL'),
    
];