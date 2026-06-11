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
        ->assertJsonStructure(['user', 'token', 'token_type']);

    $this->assertDatabaseHas('users', ['email' => 'api@example.com']);
    $this->assertDatabaseHas('user_profiles', [
        'user_id' => User::query()->where('email', 'api@example.com')->value('id'),
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
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonStructure(['token']);

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'status' => 'success',
        'login_method' => 'api_token',
    ]);
});

test('api user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();

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

    $response->assertSuccessful();
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
});

test('api user can request password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->postJson(route('api.auth.forgot-password'), [
        'email' => $user->email,
    ]);

    $response->assertSuccessful();

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

    $response->assertSuccessful();

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
        ->getJson(route('api.profile.show'))
        ->assertSuccessful()
        ->assertJsonPath('user.email', $user->email);

    $this->withToken($token->plainTextToken)
        ->putJson(route('api.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'bio' => 'Finance enthusiast',
            'timezone' => 'America/New_York',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.name', 'Updated Name');

    $this->assertDatabaseHas('user_profiles', [
        'user_id' => $user->id,
        'bio' => 'Finance enthusiast',
        'timezone' => 'America/New_York',
    ]);
});

test('api routes require authentication', function () {
    $this->getJson(route('api.profile.show'))->assertUnauthorized();
});
