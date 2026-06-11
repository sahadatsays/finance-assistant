<?php

use App\Models\User;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('mobile sync endpoints return delta payloads with timestamps', function () {
    $user = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $token = $user->createToken('mobile')->plainTextToken;

    $full = $this->withToken($token)->getJson(route('api.sync.transactions'));
    $full->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['items', 'deleted_ids', 'synced_at'],
            'meta' => ['server_time', 'synced_at', 'delta'],
        ]);

    $since = $full->json('data.synced_at');

    $this->withToken($token)
        ->getJson(route('api.sync.transactions', ['since' => $since]))
        ->assertSuccessful();

    $this->withToken($token)->getJson(route('api.sync.budgets'))->assertSuccessful();
    $this->withToken($token)->getJson(route('api.sync.goals'))->assertSuccessful();
    $this->withToken($token)->getJson(route('api.sync.dashboard'))->assertSuccessful();
    $this->withToken($token)->getJson(route('api.sync.notifications'))->assertSuccessful();
});
