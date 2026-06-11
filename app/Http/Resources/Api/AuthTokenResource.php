<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\UserResource;
use App\Models\User;

class AuthTokenResource
{
    /**
     * @return array{user: array<string, mixed>, token: string, token_type: string}
     */
    public static function toArray(User $user, string $plainTextToken): array
    {
        $user->loadMissing('profile');

        return [
            'user' => (new UserResource($user))->resolve(),
            'token' => $plainTextToken,
            'token_type' => 'Bearer',
        ];
    }
}
