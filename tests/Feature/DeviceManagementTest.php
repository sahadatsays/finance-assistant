<?php

use App\Models\User;
use App\Models\UserDevice;

test('authenticated user can list devices via api', function () {
    $user = User::factory()->create();
    UserDevice::factory()->count(2)->create(['user_id' => $user->id]);
    $token = $user->createToken('test');

    $response = $this->withToken($token->plainTextToken)
        ->getJson(route('api.devices.index'));

    $response->assertSuccessful()
        ->assertJsonCount(2, 'devices');
});

test('authenticated user can revoke another device via api', function () {
    $user = User::factory()->create();
    $device = UserDevice::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('current');

    $this->withToken($token->plainTextToken)
        ->deleteJson(route('api.devices.destroy', $device))
        ->assertSuccessful();

    $this->assertDatabaseMissing('user_devices', ['id' => $device->id]);
});

test('user cannot revoke another users device', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $device = UserDevice::factory()->create(['user_id' => $otherUser->id]);
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->deleteJson(route('api.devices.destroy', $device))
        ->assertForbidden();
});

test('devices settings page can be rendered', function () {
    $user = User::factory()->create();
    UserDevice::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('devices.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/devices')
            ->has('devices', 1));
});
