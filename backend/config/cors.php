<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The frontend is normally served from the same origin as the API in
    | production. Local development may use the Vite dev server on :5173.
    |
    */

    'paths' => ['api/*', 'up'],

    'allowed_methods' => ['GET', 'OPTIONS'],

    'allowed_origins' => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
