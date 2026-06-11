<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return $this->success(
            data: ['user' => new UserResource($user)],
            message: 'Profile retrieved successfully.',
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $profileData = collect($validated)->only([
            'avatar_url',
            'phone',
            'timezone',
            'locale',
            'bio',
        ])->filter()->all();

        $user->fill(collect($validated)->only(['name', 'email'])->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($profileData !== []) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData,
            );
        }

        return $this->success(
            data: ['user' => new UserResource($user->load('profile'))],
            message: 'Profile updated successfully.',
        );
    }
}
