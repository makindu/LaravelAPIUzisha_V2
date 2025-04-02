<?php

// return [
//     'paths' => ['api/*', 'storage/app/public/uploads/*'], // Autorise les requêtes sur ces routes
//     'allowed_methods' => ['*'], // Autorise toutes les méthodes (GET, POST, etc.)
//     'allowed_origins' => ['*'], // Autorise les requêtes venant de ton frontend Ionic
//     'allowed_origins_patterns' => ['*'],
//     'allowed_headers' => ['*'], // Autorise tous les headers
//     'exposed_headers' => ['*'],
//     'max_age' => 0,
//     'supports_credentials' =>true, // Mets à true si tu utilises des cookies ou sessions cross-origin
// ];

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
//     */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
