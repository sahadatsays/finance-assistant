<?php

use App\Models\Finance\Budget;
use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\BudgetPeriodType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function budgetApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can list budgets with pagination', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $response = $this->withToken(budgetApiToken($member))
        ->getJson(route('api.budgets.index'));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Budgets retrieved successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'period_type', 'amount', 'utilization', 'categories', 'lines'],
            ],
            'meta' => [
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                'filters',
            ],
        ])
        ->assertJsonPath('meta.pagination.total', 2);
});

test('budgets can be filtered by period type', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(budgetApiToken($owner))
        ->getJson(route('api.budgets.index', ['period_type' => 'monthly']));

    $response->assertSuccessful();

    expect(collect($response->json('data'))->every(fn (array $b) => $b['period_type'] === 'monthly'))->toBeTrue();
});

test('tenant member can view budget analysis', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $budget = Budget::query()->where('tenant_id', $tenant->id)->where('name', 'Monthly Budget')->firstOrFail();

    $response = $this->withToken(budgetApiToken($member))
        ->getJson(route('api.budgets.analysis', $budget));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'analysis' => [
                    'budget',
                    'allocated',
                    'spent',
                    'remaining',
                    'percentage',
                    'status',
                    'categories',
                ],
            ],
        ])
        ->assertJsonPath('data.analysis.budget.id', $budget->id);

    $analysis = $response->json('data.analysis');

    expect($analysis['allocated'])->toBeGreaterThan(0)
        ->and($analysis['spent'])->toBeGreaterThanOrEqual(0)
        ->and($analysis['remaining'])->toBeGreaterThanOrEqual(0)
        ->and($analysis['percentage'])->toBeGreaterThanOrEqual(0);
});

test('tenant owner can create budget via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $groceries = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Groceries')->firstOrFail();
    $transport = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Transport')->firstOrFail();

    $response = $this->withToken(budgetApiToken($owner))
        ->postJson(route('api.budgets.store'), [
            'name' => 'API Monthly Budget',
            'period_type' => BudgetPeriodType::Monthly->value,
            'lines' => [
                ['category_id' => $groceries->id, 'amount' => 400],
                ['category_id' => $transport->id, 'amount' => 250],
            ],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.budget.name', 'API Monthly Budget')
        ->assertJsonPath('data.budget.amount', 650);

    $this->assertDatabaseHas('budgets', [
        'name' => 'API Monthly Budget',
        'amount' => 650,
    ]);
});

test('tenant member cannot create budgets via api', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->withToken(budgetApiToken($member))
        ->postJson(route('api.budgets.store'), [
            'name' => 'Blocked',
            'period_type' => BudgetPeriodType::Monthly->value,
            'lines' => [
                ['category_id' => $category->id, 'amount' => 100],
            ],
        ])
        ->assertForbidden();
});

test('tenant owner can update and delete budget via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $budget = Budget::query()->where('tenant_id', $tenant->id)->where('name', 'Weekly Budget')->firstOrFail();
    $groceries = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Groceries')->firstOrFail();

    $this->withToken(budgetApiToken($owner))
        ->putJson(route('api.budgets.update', $budget), [
            'name' => 'API Updated Weekly',
            'lines' => [
                ['category_id' => $groceries->id, 'amount' => 200],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.budget.name', 'API Updated Weekly');

    expect($budget->fresh()->lines)->toHaveCount(1);

    $this->withToken(budgetApiToken($owner))
        ->deleteJson(route('api.budgets.destroy', $budget))
        ->assertSuccessful()
        ->assertJson(['success' => true, 'message' => 'Budget deleted successfully.']);

    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('budget from another tenant returns not found via api', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $acme = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $budget = Budget::query()->where('tenant_id', $acme->id)->firstOrFail();

    $this->withToken(budgetApiToken($owner))
        ->withHeader('X-Tenant-Id', (string) Tenant::query()->where('slug', 'startup-inc')->value('id'))
        ->getJson(route('api.budgets.show', $budget))
        ->assertNotFound();
});

test('budgets api requires authentication', function () {
    $this->getJson(route('api.budgets.index'))->assertUnauthorized();
});
