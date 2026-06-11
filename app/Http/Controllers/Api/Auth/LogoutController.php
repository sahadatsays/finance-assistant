<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\OpenApi\Shared\SanctumSecurityConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LogoutController extends ApiController
{
    #[OA\Post(
        path: '/auth/logout',
        operationId: 'logoutUser',
        summary: 'Logout and revoke current token',
        description: 'Revokes the current Sanctum access token and removes the associated device record.',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PROTECTED,
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.'),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
        ],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token !== null) {
            $request->user()->devices()
                ->where('token_id', $token->id)
                ->delete();

            $token->delete();
        }

        return $this->success(message: 'Logged out successfully.');
    }
}
