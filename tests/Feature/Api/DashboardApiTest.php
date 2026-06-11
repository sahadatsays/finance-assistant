<?php

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Services\Finance\TenantDashboardService;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant owner can retrieve dashboard via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $token = $owner->createToken('mobile');

    $response = $this->withToken($token->plainTextToken)
        ->getJson(route('api.dashboard.show'));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Dashboard retrieved successfully.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'tenant' => ['id', 'name', 'slug', 'currency'],
                'metrics' => [
                    'total_income',
                    'total_expense',
                    'total_savings',
                    'net_worth',
                    'budget_status' => ['spent', 'budgeted', 'percentage', 'status'],
                    'savings_goal_progress' => [
                        'summary' => ['total_saved', 'total_target', 'percentage', 'active_count', 'completed_count'],
                        'goals',
                    ],
                ],
                'charts' => [
                    'income_vs_expense',
                    'monthly_trend',
                    'category_breakdown',
                ],
            ],
            'meta' => ['period', 'cache_enabled', 'cache_ttl'],
        ])
        ->assertJsonPath('data.tenant.slug', 'acme-corp')
        ->assertJsonPath('data.metrics.total_income', 6400);
});

test('dashboard charts include mobile-friendly month labels', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $token = $owner->createToken('mobile');

    $response = $this->withToken($token->plainTextToken)
        ->getJson(route('api.dashboard.show'));

    $response->assertSuccessful();

    $incomeVsExpense = $response->json('data.charts.income_vs_expense');

    expect($incomeVsExpense)->toBeArray()->not->toBeEmpty();
    expect($incomeVsExpense[0])->toHaveKeys(['month', 'month_label', 'income', 'expense']);
});

test('dashboard api can target a specific tenant via header', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $startup = Tenant::query()->where('slug', 'startup-inc')->firstOrFail();
    $token = $owner->createToken('mobile');

    $this->withToken($token->plainTextToken)
        ->withHeader('X-Tenant-Id', (string) $startup->id)
        ->getJson(route('api.dashboard.show'))
        ->assertSuccessful()
        ->assertJsonPath('data.tenant.slug', 'startup-inc');
});

test('dashboard api caches tenant payload', function () {
    Cache::flush();

    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $token = $owner->createToken('mobile');
    $cacheKey = sprintf('api.dashboard.%d.%s', $tenant->id, now()->format('Y-m'));

    expect(Cache::has($cacheKey))->toBeFalse();

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.dashboard.show'))
        ->assertSuccessful();

    expect(Cache::has($cacheKey))->toBeTrue();
});

test('dashboard api returns forbidden when user has no workspace', function () {
    $guest = User::query()->where('email', 'guest@example.com')->firstOrFail();
    $token = $guest->createToken('mobile');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.dashboard.show'))
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'No workspace available.',
        ]);
});

test('dashboard api returns forbidden for suspended tenant owner', function () {
    $owner = User::query()->where('email', 'owner@suspended.com')->firstOrFail();
    $token = $owner->createToken('mobile');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.dashboard.show'))
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'No workspace available.',
        ]);
});

test('dashboard api requires authentication', function () {
    $this->getJson(route('api.dashboard.show'))->assertUnauthorized();
});

test('forget api cache clears dashboard payload', function () {
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $service = app(TenantDashboardService::class);

    $service->forApi($tenant);

    $cacheKey = sprintf('api.dashboard.%d.%s', $tenant->id, now()->format('Y-m'));
    expect(Cache::has($cacheKey))->toBeTrue();

    $service->forgetApiCache($tenant);

    expect(Cache::has($cacheKey))->toBeFalse();
});
