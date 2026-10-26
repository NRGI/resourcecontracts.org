<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | All external integrations read their credentials from here so that
    | `php artisan config:cache` can be used (env() must not be called outside
    | the config directory).
    |
    */

    'ses' => [
        'key'    => env('AWS_KEY'),
        'secret' => env('AWS_SECRET'),
        'region' => env('AWS_REGION', 'us-east-1'),
    ],

    // Separate ElasticSearch API application (rc-elasticsearch)
    'elastic_search' => [
        'url' => env('ELASTIC_SEARCH_URL'),
    ],

    // Public sites that display the contracts
    'sites' => [
        'rc'  => env('RC_LINK'),
        'olc' => env('OLC_LINK'),
    ],

    'loggly' => [
        'token' => env('LOG_TOKEN'),
    ],

    // Microsoft Graph / OneDrive import
    'one_drive' => [
        'tenant_id'     => env('ONE_DRIVE_TENANT_ID'),
        'client_id'     => env('ONE_DRIVE_CLIENT_ID'),
        'client_secret' => env('ONE_DRIVE_CLIENT_SECRET'),
        'redirect'      => env('ONE_DRIVE_AUTH_REDIRECT'),
    ],

    // ABBYY OCR
    'abbyy' => [
        'app_id'   => env('ABBYY_APP_ID'),
        'password' => env('ABBYY_PASSWORD'),
        'url'      => env('ABBYY_OCR_URL'),
    ],

    // SPARQL endpoint used to refresh government entities
    'sparql' => [
        'username' => env('SPARQL_USERNAME'),
        'password' => env('SPARQL_PASSWORD'),
    ],

];
