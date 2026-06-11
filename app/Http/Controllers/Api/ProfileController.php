<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => new UserResource($user),
        ]);
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

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user->load('profile')),
        ]);
    }
}
