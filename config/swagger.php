<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Swagger UI Access
    |--------------------------------------------------------------------------
    |
    | Swagger UI is only available in local and development environments.
    | Production always disables the interactive documentation routes.
    |
    */

    'ui_enabled' => (bool) env(
        'SWAGGER_UI_ENABLED',
        in_array(env('APP_ENV', 'production'), ['local', 'development'], true),
    ),

    /*
    |--------------------------------------------------------------------------
    | Documentation Generation
    |--------------------------------------------------------------------------
    */

    'generate_always' => (bool) env(
        'SWAGGER_GENERATE_ALWAYS',
        env('APP_ENV', 'production') === 'local',
    ),

    'generate_yaml_copy' => (bool) env('SWAGGER_GENERATE_YAML_COPY', true),

    'open_api_version' => env('SWAGGER_OPEN_API_VERSION', '3.0.0'),

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    */

    'api_version' => env('SWAGGER_API_VERSION', 'v1'),

    'base_paths' => [
        'public' => '/api/v1',
        'authenticated' => '/api/v1',
        'admin' => '/api/v1/admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Sets
    |--------------------------------------------------------------------------
    |
    | Three isolated OpenAPI documents for public, tenant-authenticated,
    | and platform-admin API surfaces.
    |
    */

    'documentations' => [
        'public' => [
            'title' => 'Finance Assistant API — Public',
            'description' => 'Unauthenticated foundation and authentication endpoints for the Finance Assistant multi-tenant SaaS platform.',
            'route' => 'api/documentation/public',
            'docs_json' => 'public-api-docs.json',
            'annotation_paths' => [
                'app/OpenApi/Shared',
                'app/OpenApi/Schemas',
                'app/OpenApi/Responses',
                'app/OpenApi/PublicApi',
                'app/Http/Controllers/Api/Auth/LoginController.php',
            ],
        ],
        'authenticated' => [
            'title' => 'Finance Assistant API — Authenticated',
            'description' => 'Tenant-scoped finance APIs. Requires Laravel Sanctum bearer token and verified email for protected routes.',
            'route' => 'api/documentation',
            'docs_json' => 'authenticated-api-docs.json',
            'annotation_paths' => [
                'app/OpenApi/Shared',
                'app/OpenApi/Schemas',
                'app/OpenApi/Responses',
                'app/OpenApi/Authenticated',
                'app/Http/Controllers/Api/Auth/LogoutController.php',
                'app/Http/Controllers/Api/Auth/ProfileController.php',
                'app/Http/Controllers/Api/V1/CategoryController.php',
            ],
        ],
        'admin' => [
            'title' => 'Finance Assistant API — Admin',
            'description' => 'Platform administrator APIs for tenant and subscription management.',
            'route' => 'api/documentation/admin',
            'docs_json' => 'admin-api-docs.json',
            'annotation_paths' => [
                'app/OpenApi/Shared',
                'app/OpenApi/Schemas',
                'app/OpenApi/Responses',
                'app/OpenApi/Admin',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Headers
    |--------------------------------------------------------------------------
    */

    'tenant_header' => 'X-Tenant-Id',

    /*
    |--------------------------------------------------------------------------
    | Sanctum Bearer Authentication (OpenAPI)
    |--------------------------------------------------------------------------
    |
    | HTTP Bearer scheme used by Swagger UI Authorize dialog. Tokens are
    | issued by POST /api/v1/auth/login and sent as Authorization: Bearer {token}.
    |
    */

    'sanctum' => [
        'scheme' => 'bearer',
        'bearer_format' => 'Sanctum',
        'header' => 'Authorization',
        'token_prefix' => 'Bearer',
        'login_path' => '/api/v1/auth/login',
        'logout_path' => '/api/v1/auth/logout',
        'profile_path' => '/api/v1/auth/profile',
        'demo_credentials' => [
            'email' => 'owner@acme.com',
            'password' => 'password',
        ],
    ],

];
