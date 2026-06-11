<?php

use App\Models\Platform\AppNotification;
use App\Models\Platform\DeviceToken;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Platform\Services\NotificationService;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('user can list mark read notifications and manage device tokens', function () {
    $user = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $token = $user->createToken('mobile')->plainTextToken;

    $notification = app(NotificationService::class)->create($tenant, $user, [
        'type' => 'budget_alert',
        'title' => 'Budget warning',
        'body' => 'You exceeded 80% of groceries budget.',
    ]);

    $this->withToken($token)
        ->getJson(route('api.notifications.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.notifications.0.id', $notification->id);

    $this->withToken($token)
        ->postJson(route('api.notifications.read'), ['ids' => [$notification->id]])
        ->assertSuccessful();

    expect(AppNotification::query()->find($notification->id)?->read_at)->not->toBeNull();

    $this->withToken($token)
        ->postJson(route('api.device-token.store'), [
            'token' => 'fcm-device-token-123',
            'platform' => 'android',
            'device_name' => 'Pixel 8',
        ])
        ->assertCreated();

    $this->withToken($token)
        ->deleteJson(route('api.device-token.destroy'), ['token' => 'fcm-device-token-123'])
        ->assertSuccessful();

    expect(DeviceToken::query()->where('token', 'fcm-device-token-123')->exists())->toBeFalse();
});
