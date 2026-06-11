<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('api user can register and receive token', function () {
    Notification::fake();

    $response = $this->postJson(route('api.auth.register'), [
        'name' => 'API User',
        'email' => 'api@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'Test Phone',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'email_verified', 'profile'],
                'token',
                'token_type',
            ],
            'meta',
        ])
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.email', 'api@example.com')
        ->assertJsonPath('data.user.email_verified', false);

    $this->assertDatabaseHas('users', ['email' => 'api@example.com']);
    $this->assertDatabaseHas('user_profiles', [
        'user_id' => User::query()->where('email', 'api@example.com')->value('id'),
    ]);
    $this->assertDatabaseHas('user_devices', [
        'user_id' => User::query()->where('email', 'api@example.com')->value('id'),
        'name' => 'Test Phone',
    ]);

    Notification::assertSentTo(
        User::query()->where('email', 'api@example.com')->first(),
        VerifyEmail::class,
    );
});

test('api user can login and receive token', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'API Client',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Login successful.',
        ])
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure(['data' => ['token']]);

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'status' => 'success',
        'login_method' => 'api_token',
    ]);
    $this->assertDatabaseHas('user_devices', [
        'user_id' => $user->id,
        'name' => 'API Client',
    ]);
});

test('api user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => 'The given data was invalid.',
        ])
        ->assertJsonStructure(['data' => ['errors' => ['email']]]);

    $this->assertDatabaseHas('login_histories', [
        'email' => $user->email,
        'status' => 'failed',
    ]);
});

test('api user can logout and revoke token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    $response = $this->withToken($token->plainTextToken)
        ->postJson(route('api.auth.logout'));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
});

test('api user can request password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->postJson(route('api.auth.forgot-password'), [
        'email' => $user->email,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('api user can reset password with valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->postJson(route('api.auth.reset-password'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-12!',
        'password_confirmation' => 'new-password-12!',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertTrue(
        auth()->guard('web')->attempt([
            'email' => $user->email,
            'password' => 'new-password-12!',
        ]),
    );
});

test('api profile can be viewed and updated by authenticated user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.auth.profile.show'))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Profile retrieved successfully.',
        ])
        ->assertJsonPath('data.user.email', $user->email);

    $this->withToken($token->plainTextToken)
        ->putJson(route('api.auth.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'bio' => 'Finance enthusiast',
            'timezone' => 'America/New_York',
        ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ])
        ->assertJsonPath('data.user.name', 'Updated Name');

    $this->assertDatabaseHas('user_profiles', [
        'user_id' => $user->id,
        'bio' => 'Finance enthusiast',
        'timezone' => 'America/New_York',
    ]);
});

test('api auth profile requires authentication', function () {
    $this->getJson(route('api.auth.profile.show'))->assertUnauthorized();
});

test('api user can check email verification status', function () {
    $user = User::factory()->unverified()->create();
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.auth.verification.status'))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'verified' => false,
                'email' => $user->email,
            ],
        ]);
});

test('api user can resend email verification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->postJson(route('api.auth.verification.resend'))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Verification link sent.',
        ]);

    Notification::assertSentTo($user, VerifyEmail::class);
});
