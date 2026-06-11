<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Services\Auth\SessionManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private SessionManagementService $sessions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'sessions' => SessionResource::collection(
                $this->sessions->listSessions($request->user(), $request),
            ),
        ]);
    }

    public function destroy(Request $request, string $session): JsonResponse
    {
        if (! $this->sessions->revokeSession($request->user(), $session, $request)) {
            return response()->json([
                'message' => 'Unable to revoke session. It may be your current session or does not exist.',
            ], 422);
        }

        return response()->json([
            'message' => 'Session revoked successfully.',
        ]);
    }

    public function destroyOthers(Request $request): JsonResponse
    {
        $revoked = $this->sessions->revokeOtherSessions($request->user(), $request);

        return response()->json([
            'message' => 'Other sessions and tokens revoked successfully.',
            'revoked_count' => $revoked,
        ]);
    }
}
