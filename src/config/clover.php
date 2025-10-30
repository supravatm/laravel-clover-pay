<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Clover API Configuration
    |--------------------------------------------------------------------------
    */
    'environment' => env('CLOVER_ENV', 'sandbox'),
    'public_key' => env('CLOVER_PUBLIC_KEY', ''),
    'access_token'  =>  env('CLOVER_ACCESS_TOKEN', ''),
    'api_base' => env('CLOVER_API_URL', 'https://sandbox.dev.clover.com/v3/merchants'),
    'token_url'  =>  env('CLOVER_TOKEN_URL', 'https://token-sandbox.dev.clover.com/v1/tokens'),
    'merchant_id' => env('CLOVER_MERCHANT_ID', ''),
    'oauth_url' => env('CLOVER_OAUTH_URL', 'https://sandbox.dev.clover.com/oauth/token'),
    'app_id' => env('CLOVER_APP_ID', ''), //your_clover_app_id_here
    'app_secret' => env('CLOVER_APP_SECRET', ''), //your_clover_app_secret_here
    'redirect_url' => env('CLOVER_REDIRECT_URL', ''),
    'tender_id' => env('CLOVER_TENDER_ID', ''),
    'timeout' => 10
];
