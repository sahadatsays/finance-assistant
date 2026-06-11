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

    /*
    |--------------------------------------------------------------------------
    | Dashboard API Caching
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'cache_enabled' => (bool) env('API_DASHBOARD_CACHE_ENABLED', true),
        'cache_ttl' => (int) env('API_DASHBOARD_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment Storage
    |--------------------------------------------------------------------------
    */

    'attachments' => [
        'disk' => env('ATTACHMENT_DISK', 'local'),
        'max_size_kb' => (int) env('ATTACHMENT_MAX_SIZE_KB', 5120),
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
        'signed_url_ttl_minutes' => (int) env('ATTACHMENT_SIGNED_URL_TTL', 30),
        'pending_ttl_hours' => (int) env('ATTACHMENT_PENDING_TTL_HOURS', 24),
    ],

];
