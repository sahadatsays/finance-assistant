<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginHistoryResource;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function __construct(
        private LoginHistoryService $loginHistory,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $histories = $this->loginHistory->paginateForUser(
            $request->user(),
            perPage: (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => LoginHistoryResource::collection($histories),
            'meta' => [
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
                'per_page' => $histories->perPage(),
                'total' => $histories->total(),
            ],
        ]);
    }
}
