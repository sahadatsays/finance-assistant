<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | The default API version used when generating documentation links and
    | version metadata. Future versions (v2, v3) will be registered alongside.
    |
    */

    'default_version' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Supported API Versions
    |--------------------------------------------------------------------------
    |
    | Each version maps to its route prefix and documentation path.
    |
    */

    'versions' => [
        'v1' => [
            'prefix' => 'v1',
            'status' => 'stable',
            'documentation' => '/docs/api/v1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'authenticated' => (int) env('API_RATE_LIMIT_AUTHENTICATED', 120),
        'guest' => (int) env('API_RATE_LIMIT_GUEST', 60),
        'auth' => (int) env('API_RATE_LIMIT_AUTH', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Request Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => (bool) env('API_LOGGING_ENABLED', true),
        'channel' => env('API_LOG_CHANNEL', 'api'),
        'log_request_body' => (bool) env('API_LOG_REQUEST_BODY', false),
        'log_response_body' => (bool) env('API_LOG_RESPONSE_BODY', false),
    ],

];
