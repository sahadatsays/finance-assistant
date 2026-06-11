<?php

use App\Models\Finance\Goal;
use App\Models\Finance\GoalContribution;
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

test('tenant owner can view goals page with analytics', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('goals.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('goals/index')
            ->has('analytics.summary')
            ->has('analytics.by_type')
            ->has('analytics.trend')
            ->has('goals', 5)
            ->has('goalTypes', 5)
            ->where('permissions.create', true)
            ->where('permissions.update', true));
});

test('tenant member can view and contribute but cannot manage goals', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $this->actingAs($member)
        ->get(route('goals.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('permissions.create', false)
            ->where('permissions.update', false)
            ->where('permissions.contribute', true)
            ->where('permissions.export', true));

    $this->actingAs($member)
        ->post(route('goals.store'), [
            'name' => 'Blocked Goal',
            'type' => GoalType::Custom->value,
            'target_amount' => 1000,
        ])
        ->assertForbidden();

    $goal = Goal::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->actingAs($member)
        ->put(route('goals.update', $goal), ['name' => 'Renamed'])
        ->assertForbidden();

    $this->actingAs($member)
        ->delete(route('goals.destroy', $goal))
        ->assertForbidden();
});

test('tenant owner can create savings goal with initial contribution', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('goals.store'), [
            'name' => 'New Car',
            'type' => GoalType::Purchase->value,
            'target_amount' => 15000,
            'target_date' => now()->addMonths(6)->toDateString(),
            'initial_contribution' => 500,
        ])
        ->assertRedirect(route('goals.index'));

    $goal = Goal::query()->where('name', 'New Car')->firstOrFail();

    expect($goal->type)->toBe(GoalType::Purchase)
        ->and((float) $goal->current_amount)->toBe(500.0)
        ->and($goal->contributions)->toHaveCount(1);

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Savings goal "New Car" was created.',
        'log_name' => 'finance',
    ]);
});

test('tenant member can add contribution to goal', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $goal = Goal::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'Japan Trip')
        ->firstOrFail();

    $previousAmount = (float) $goal->current_amount;

    $this->actingAs($member)
        ->post(route('goals.contributions.store', $goal), [
            'amount' => 250,
            'notes' => 'Bonus savings',
        ])
        ->assertRedirect(route('goals.index'));

    expect((float) $goal->fresh()->current_amount)->toBe($previousAmount + 250);

    $this->assertDatabaseHas('goal_contributions', [
        'goal_id' => $goal->id,
        'amount' => 250,
        'notes' => 'Bonus savings',
    ]);
});

test('tenant owner can update and delete goal', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $goal = Goal::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'MBA Course')
        ->firstOrFail();

    $this->actingAs($owner)
        ->put(route('goals.update', $goal), [
            'name' => 'Executive MBA',
            'target_amount' => 12000,
        ])
        ->assertRedirect(route('goals.index'));

    expect($goal->fresh()->name)->toBe('Executive MBA')
        ->and((float) $goal->fresh()->target_amount)->toBe(12000.0);

    $this->actingAs($owner)
        ->delete(route('goals.destroy', $goal))
        ->assertRedirect(route('goals.index'));

    $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
});

test('tenant member can export goals report', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('goals.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('deleting contribution reduces goal current amount', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $goal = Goal::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'New Laptop')
        ->firstOrFail();

    $contribution = GoalContribution::query()
        ->where('goal_id', $goal->id)
        ->firstOrFail();

    $previousAmount = (float) $goal->current_amount;
    $contributionAmount = (float) $contribution->amount;

    $this->actingAs($owner)
        ->delete(route('goals.contributions.destroy', [$goal, $contribution]))
        ->assertRedirect(route('goals.index'));

    expect((float) $goal->fresh()->current_amount)->toBe($previousAmount - $contributionAmount);
    $this->assertDatabaseMissing('goal_contributions', ['id' => $contribution->id]);
});
