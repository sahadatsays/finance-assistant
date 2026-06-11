<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends ApiController
{
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(
                data: ['user' => new UserResource($request->user()->load('profile'))],
                message: 'Email already verified.',
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->success(
            data: ['user' => new UserResource($request->user()->load('profile'))],
            message: 'Email verified successfully.',
        );
    }

    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(message: 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success(message: 'Verification link sent.');
    }

    public function status(Request $request): JsonResponse
    {
        return $this->success(
            data: [
                'verified' => $request->user()->hasVerifiedEmail(),
                'email' => $request->user()->email,
            ],
            message: 'Email verification status retrieved.',
        );
    }
}
