<?php

use App\Models\Finance\Goal;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\GoalType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function goalApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can list goals with progress and forecast', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $response = $this->withToken(goalApiToken($member))
        ->getJson(route('api.goals.index'));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Savings goals retrieved successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'type',
                    'target_amount',
                    'current_amount',
                    'progress' => ['current', 'target', 'remaining', 'percentage', 'status'],
                    'forecast' => ['remaining', 'days_remaining', 'required_monthly', 'projected_completion', 'is_behind'],
                    'contributions',
                    'contribution_trend',
                ],
            ],
            'meta' => [
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                'filters',
            ],
        ])
        ->assertJsonPath('meta.pagination.total', 5);
});

test('goals can be filtered by type and search', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(goalApiToken($owner))
        ->getJson(route('api.goals.index', ['type' => GoalType::Travel->value, 'search' => 'Japan']));

    $response->assertSuccessful();

    $goals = collect($response->json('data'));

    expect($goals)->toHaveCount(1)
        ->and($goals->first()['name'])->toBe('Japan Trip')
        ->and($goals->first()['type'])->toBe(GoalType::Travel->value);
});

test('tenant member can view goal with remaining amount', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $goal = Goal::query()->where('tenant_id', $tenant->id)->where('name', 'Japan Trip')->firstOrFail();

    $response = $this->withToken(goalApiToken($member))
        ->getJson(route('api.goals.show', $goal));

    $response->assertSuccessful()
        ->assertJsonPath('data.goal.id', $goal->id)
        ->assertJsonPath('data.goal.name', 'Japan Trip');

    $progress = $response->json('data.goal.progress');
    $forecast = $response->json('data.goal.forecast');

    expect($progress['remaining'])->toEqual(1800)
        ->and($progress['target'])->toEqual(5000)
        ->and($forecast['remaining'])->toEqual(1800);
});

test('tenant owner can create goal via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(goalApiToken($owner))
        ->postJson(route('api.goals.store'), [
            'name' => 'API Vacation Fund',
            'type' => GoalType::Travel->value,
            'target_amount' => 3000,
            'target_date' => now()->addMonths(5)->toDateString(),
            'initial_contribution' => 250,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.goal.name', 'API Vacation Fund')
        ->assertJsonPath('data.goal.current_amount', 250)
        ->assertJsonPath('data.goal.progress.remaining', 2750);

    $this->assertDatabaseHas('goals', [
        'name' => 'API Vacation Fund',
        'target_amount' => 3000,
        'current_amount' => 250,
    ]);
});

test('tenant member cannot create goals via api', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->withToken(goalApiToken($member))
        ->postJson(route('api.goals.store'), [
            'name' => 'Blocked Goal',
            'type' => GoalType::Custom->value,
            'target_amount' => 1000,
        ])
        ->assertForbidden();
});

test('tenant owner can update and delete goals via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $goal = Goal::query()->where('tenant_id', $tenant->id)->where('name', 'New Laptop')->firstOrFail();

    $this->withToken(goalApiToken($owner))
        ->putJson(route('api.goals.update', $goal), [
            'name' => 'Gaming Laptop',
            'target_amount' => 2500,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.goal.name', 'Gaming Laptop')
        ->assertJsonPath('data.goal.target_amount', 2500);

    $this->withToken(goalApiToken($owner))
        ->deleteJson(route('api.goals.destroy', $goal))
        ->assertSuccessful();

    expect(Goal::query()->find($goal->id))->toBeNull();
});

test('tenant member can contribute to goal via api', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $goal = Goal::query()->where('tenant_id', $tenant->id)->where('name', 'Japan Trip')->firstOrFail();
    $previousAmount = (float) $goal->current_amount;

    $response = $this->withToken(goalApiToken($member))
        ->postJson(route('api.goals.contribute', $goal), [
            'amount' => 200,
            'notes' => 'Bonus savings',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.contribution.amount', 200);

    expect($response->json('data.goal.current_amount'))->toEqual($previousAmount + 200)
        ->and($response->json('data.goal.progress.remaining'))->toEqual(1600);

    $this->assertDatabaseHas('goal_contributions', [
        'goal_id' => $goal->id,
        'amount' => 200,
        'notes' => 'Bonus savings',
    ]);
});

test('goal from another tenant returns not found', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $otherTenant = Tenant::query()->where('slug', '!=', 'acme-corp')->first();

    if ($otherTenant === null) {
        $this->markTestSkipped('No secondary tenant seeded.');
    }

    $foreignGoal = Goal::query()->where('tenant_id', $otherTenant->id)->first();

    if ($foreignGoal === null) {
        $this->markTestSkipped('No goal in secondary tenant.');
    }

    $this->withToken(goalApiToken($owner))
        ->getJson(route('api.goals.show', $foreignGoal))
        ->assertNotFound();
});
