<?php

use L5Swagger\Generator;

/** @var array<string, mixed> $swagger */
$swagger = require __DIR__.'/swagger.php';

$swaggerMiddleware = ['swagger.enabled'];

$buildDocumentation = static function (string $key) use ($swagger): array {
    $doc = $swagger['documentations'][$key];

    return [
        'api' => [
            'title' => $doc['title'],
        ],
        'routes' => [
            'api' => $doc['route'],
            'docs' => $doc['route'].'/docs',
            'oauth2_callback' => $doc['route'].'/oauth2-callback',
        ],
        'paths' => [
            'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
            'docs_json' => $doc['docs_json'],
            'docs_yaml' => str_replace('.json', '.yaml', $doc['docs_json']),
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
            'annotations' => array_map(
                static fn (string $path): string => base_path($path),
                $doc['annotation_paths'],
            ),
        ],
    ];
};

return [
    'default' => 'authenticated',

    'documentations' => [
        'public' => $buildDocumentation('public'),
        'authenticated' => $buildDocumentation('authenticated'),
        'admin' => $buildDocumentation('admin'),
    ],

    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => $swaggerMiddleware,
                'asset' => $swaggerMiddleware,
                'docs' => $swaggerMiddleware,
                'oauth2_callback' => $swaggerMiddleware,
            ],
            'group_options' => [],
        ],

        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'excludes' => [],
        ],

        'scanOptions' => [
            'default_processors_configuration' => [],
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'pattern' => null,
            'exclude' => [],
            'open_api_spec_version' => env(
                'L5_SWAGGER_OPEN_API_SPEC_VERSION',
                $swagger['open_api_version'] ?? Generator::OPEN_API_DEFAULT_SPEC_VERSION,
            ),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'description' => 'Laravel Sanctum personal access token. Login via POST /api/v1/auth/login, copy `data.token`, click Authorize, and paste the token (Swagger UI adds the Bearer prefix).',
                    'scheme' => 'bearer',
                    'bearerFormat' => $swagger['sanctum']['bearer_format'] ?? 'Sanctum',
                ],
                'tenant' => [
                    'type' => 'apiKey',
                    'description' => 'Optional active workspace tenant ID for multi-tenant SaaS context.',
                    'name' => $swagger['tenant_header'] ?? 'X-Tenant-Id',
                    'in' => 'header',
                ],
            ],
            'security' => [],
        ],

        'generate_always' => (bool) ($swagger['generate_always'] ?? false),
        'generate_yaml_copy' => (bool) ($swagger['generate_yaml_copy'] ?? true),
        'proxy' => false,
        'additional_config_url' => null,
        'validator_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', 'alpha'),

        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'list'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),
                'oauth2' => [
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', env('APP_URL', 'http://localhost')),
        ],
    ],
];
