<?php

namespace App\OpenApi\PublicApi;

use App\OpenApi\Shared\SanctumSecurityConfiguration;
use OpenApi\Attributes as OA;

class FoundationPaths
{
    #[OA\Get(
        path: '/',
        operationId: 'getApiVersion',
        summary: 'API version metadata',
        description: 'Returns supported API versions, documentation links, and service metadata.',
        tags: ['Foundation'],
        security: SanctumSecurityConfiguration::PUBLIC,
        responses: [
            new OA\Response(
                response: 200,
                description: 'Version metadata',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Finance Assistant API v1'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'version', type: 'string', example: 'v1'),
                                new OA\Property(property: 'status', type: 'string', example: 'stable'),
                                new OA\Property(property: 'name', type: 'string', example: 'Finance Assistant API'),
                                new OA\Property(property: 'documentation', type: 'string', example: '/docs/api/v1'),
                                new OA\Property(
                                    property: 'supported_versions',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['v1'],
                                ),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function version(): void {}

    #[OA\Get(
        path: '/health',
        operationId: 'getApiHealth',
        summary: 'Health check',
        description: 'Returns API health status and server timestamp.',
        tags: ['Foundation'],
        security: SanctumSecurityConfiguration::PUBLIC,
        responses: [
            new OA\Response(
                response: 200,
                description: 'API is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'API is healthy.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function health(): void {}
}
