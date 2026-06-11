<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('authenticated user can list sessions via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test');

    DB::table('sessions')->insert([
        'id' => 'test-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->timestamp,
    ]);

    $response = $this->withToken($token->plainTextToken)
        ->getJson(route('api.sessions.index'));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'sessions');
});

test('authenticated user can revoke other sessions via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('current');

    DB::table('sessions')->insert([
        [
            'id' => 'other-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Other',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'another-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.2',
            'user_agent' => 'Another',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ],
    ]);

    $this->withToken($token->plainTextToken)
        ->deleteJson(route('api.sessions.destroy-others'))
        ->assertSuccessful();

    $this->assertDatabaseMissing('sessions', ['id' => 'other-session']);
    $this->assertDatabaseMissing('sessions', ['id' => 'another-session']);
});

test('sessions settings page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sessions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/sessions'));
});
