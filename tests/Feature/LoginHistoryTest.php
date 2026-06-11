<?php

use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;

test('successful web login records login history', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'status' => LoginStatus::Success->value,
        'login_method' => 'password',
    ]);
});

test('failed web login records login history', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertDatabaseHas('login_histories', [
        'email' => $user->email,
        'status' => LoginStatus::Failed->value,
    ]);
});

test('authenticated user can view login history via api', function () {
    $user = User::factory()->create();
    LoginHistory::factory()->count(3)->create(['user_id' => $user->id]);
    $token = $user->createToken('test');

    $response = $this->withToken($token->plainTextToken)
        ->getJson(route('api.login-history.index'));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'ip_address', 'login_method', 'status', 'logged_in_at'],
            ],
            'meta',
        ]);
});

test('registration creates user profile', function () {
    $this->post(route('register.store'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'new@example.com')->first();

    expect($user)->not->toBeNull();
    $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
});
