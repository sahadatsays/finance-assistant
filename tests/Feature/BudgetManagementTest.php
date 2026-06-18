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

test('tenant owner can view budgets page with analytics', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('budgets.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('budgets/index')
            ->has('analytics.monthly')
            ->has('analytics.weekly')
            ->has('analytics.alerts')
            ->has('analytics.trend')
            ->has('budgets', 2)
            ->where('permissions.create', true)
            ->where('permissions.update', true));
});

test('tenant member can view but cannot manage budgets', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $this->actingAs($member)
        ->get(route('budgets.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('permissions.create', false)
            ->where('permissions.update', false)
            ->where('permissions.export', true));

    $this->actingAs($member)
        ->post(route('budgets.store'), [
            'name' => 'Blocked Budget',
            'period_type' => BudgetPeriodType::Monthly->value,
            'lines' => [
                ['category_id' => 1, 'amount' => 100],
            ],
        ])
        ->assertForbidden();

    $budget = Budget::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->actingAs($member)
        ->put(route('budgets.update', $budget), [
            'name' => 'Renamed',
        ])
        ->assertForbidden();

    $this->actingAs($member)
        ->delete(route('budgets.destroy', $budget))
        ->assertForbidden();
});

test('tenant owner can create monthly budget with category lines', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $groceries = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Groceries')->firstOrFail();
    $transport = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Transport')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('budgets.store'), [
            'name' => 'Q2 Budget',
            'period_type' => BudgetPeriodType::Monthly->value,
            'lines' => [
                ['category_id' => $groceries->id, 'amount' => 400],
                ['category_id' => $transport->id, 'amount' => 250],
            ],
        ])
        ->assertRedirect(route('budgets.index'));

    $this->assertDatabaseHas('budgets', [
        'tenant_id' => $tenant->id,
        'name' => 'Q2 Budget',
        'period_type' => BudgetPeriodType::Monthly->value,
        'amount' => 650,
    ]);

    $budget = Budget::query()->where('name', 'Q2 Budget')->firstOrFail();
    expect($budget->lines)->toHaveCount(2);

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Budget "Q2 Budget" was created.',
        'log_name' => 'finance',
    ]);
});

test('budget update merges duplicate category lines instead of failing', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $budget = Budget::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'Weekly Budget')
        ->firstOrFail();

    $groceries = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Groceries')->firstOrFail();

    $this->actingAs($owner)
        ->put(route('budgets.update', $budget), [
            'lines' => [
                ['category_id' => $groceries->id, 'amount' => 100],
                ['category_id' => $groceries->id, 'amount' => 150],
            ],
        ])
        ->assertRedirect(route('budgets.index'));

    $budget->refresh();

    expect($budget->lines)->toHaveCount(1)
        ->and((float) $budget->lines->first()->amount)->toBe(250.0)
        ->and((float) $budget->amount)->toBe(250.0);
});

test('tenant owner can update and delete budget', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $budget = Budget::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'Weekly Budget')
        ->firstOrFail();

    $groceries = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Groceries')->firstOrFail();

    $this->actingAs($owner)
        ->put(route('budgets.update', $budget), [
            'name' => 'Updated Weekly',
            'lines' => [
                ['category_id' => $groceries->id, 'amount' => 200],
            ],
        ])
        ->assertRedirect(route('budgets.index'));

    expect($budget->fresh()->name)->toBe('Updated Weekly')
        ->and($budget->fresh()->lines)->toHaveCount(1);

    $this->actingAs($owner)
        ->delete(route('budgets.destroy', $budget))
        ->assertRedirect(route('budgets.index'));

    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Budget "Updated Weekly" was deleted.',
        'log_name' => 'finance',
    ]);
});

test('tenant member can export budget report', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('budgets.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('budget store requires at least one category line', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('budgets.store'), [
            'name' => 'Empty Budget',
            'period_type' => BudgetPeriodType::Monthly->value,
            'lines' => [],
        ])
        ->assertSessionHasErrors('lines');
});
