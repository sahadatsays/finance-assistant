<?php

use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Models\Platform\ActivityLog;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant owner can view categories page with system categories', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/index')
            ->has('categories', 16)
            ->where('permissions.create', true)
            ->where('permissions.update', true));
});

test('tenant member can view but cannot create categories', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('permissions.create', false)
            ->where('permissions.update', false));

    $this->actingAs($member)
        ->post(route('categories.store'), [
            'name' => 'Blocked Category',
            'type' => CategoryType::Expense->value,
            'color' => '#ff0000',
        ])
        ->assertForbidden();
});

test('category icon must be from allowed list', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('categories.store'), [
            'name' => 'Invalid Icon',
            'type' => CategoryType::Expense->value,
            'color' => '#ff0000',
            'icon' => 'not-a-real-icon',
        ])
        ->assertSessionHasErrors('icon');
});

test('tenant owner can create custom category', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('categories.store'), [
            'name' => 'Side Hustle',
            'type' => CategoryType::Income->value,
            'color' => '#22c55e',
            'icon' => 'wallet',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Side Hustle',
        'icon' => 'wallet',
        'is_system' => false,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Category "Side Hustle" was created.',
        'log_name' => 'finance',
    ]);
});

test('tenant owner can update custom category and system category color', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $custom = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Rent')->firstOrFail();
    $system = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Salary')->firstOrFail();

    $this->actingAs($owner)
        ->put(route('categories.update', $custom), [
            'name' => 'Office Rent',
            'color' => '#111111',
        ])
        ->assertRedirect(route('categories.index'));

    expect($custom->fresh()->name)->toBe('Office Rent');

    $this->actingAs($owner)
        ->put(route('categories.update', $system), [
            'name' => 'Renamed Salary',
            'color' => '#222222',
        ])
        ->assertRedirect(route('categories.index'));

    expect($system->fresh()->name)->toBe('Salary')
        ->and($system->fresh()->color)->toBe('#222222');
});

test('tenant owner can archive and restore category', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'Insurance')
        ->firstOrFail();

    $this->actingAs($owner)
        ->post(route('categories.archive', $category))
        ->assertRedirect(route('categories.index'));

    expect($category->fresh()->is_active)->toBeFalse();

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Category "Insurance" was archived.',
    ]);

    $this->actingAs($owner)
        ->post(route('categories.restore', $category))
        ->assertRedirect(route('categories.index', ['archived' => 1]));

    expect($category->fresh()->is_active)->toBeTrue();
});

test('system categories cannot be deleted', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $system = Category::query()->where('tenant_id', $tenant->id)->where('is_system', true)->firstOrFail();

    $this->actingAs($owner)
        ->delete(route('categories.destroy', $system))
        ->assertForbidden();
});

test('categories with transactions cannot be deleted', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Rent')->firstOrFail();

    expect(Transaction::query()->where('category_id', $category->id)->exists())->toBeTrue();

    $this->actingAs($owner)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHasErrors('category');
});

test('custom category without transactions can be deleted', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $category = Category::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Temporary',
        'type' => CategoryType::Expense,
        'color' => '#abcdef',
        'is_system' => false,
        'created_by' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);

    expect(ActivityLog::query()->where('description', 'Category "Temporary" was deleted.')->exists())->toBeTrue();
});
