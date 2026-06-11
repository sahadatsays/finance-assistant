<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends ApiController
{
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
