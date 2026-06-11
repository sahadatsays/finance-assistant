<?php

use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
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

function apiTokenFor(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant owner can list categories with pagination', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(apiTokenFor($owner))
        ->getJson(route('api.categories.index', ['per_page' => 10]));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'type', 'color', 'kind', 'is_system', 'is_active'],
            ],
            'meta' => [
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                'filters',
            ],
        ])
        ->assertJsonPath('meta.pagination.per_page', 10)
        ->assertJsonPath('meta.pagination.total', 16);
});

test('categories can be filtered by type', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(apiTokenFor($owner))
        ->getJson(route('api.categories.index', ['type' => 'income', 'per_page' => 100]));

    $response->assertSuccessful();

    $types = collect($response->json('data'))->pluck('type')->unique()->all();

    expect($types)->toBe(['income']);
});

test('categories can be filtered by kind', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(apiTokenFor($owner))
        ->getJson(route('api.categories.index', ['kind' => 'system', 'per_page' => 100]));

    $response->assertSuccessful();

    expect(collect($response->json('data'))->every(fn (array $c) => $c['kind'] === 'system'))->toBeTrue();
});

test('categories can be filtered by search term', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->withToken(apiTokenFor($owner))
        ->getJson(route('api.categories.index', ['search' => 'Rent']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Rent');
});

test('tenant owner can view a single category', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Salary')->firstOrFail();

    $this->withToken(apiTokenFor($owner))
        ->getJson(route('api.categories.show', $category))
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'Salary')
        ->assertJsonPath('data.category.kind', 'system');
});

test('tenant owner can create custom category via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(apiTokenFor($owner))
        ->postJson(route('api.categories.store'), [
            'name' => 'Side Hustle',
            'type' => CategoryType::Income->value,
            'color' => '#22c55e',
            'icon' => 'wallet',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Category created successfully.',
        ])
        ->assertJsonPath('data.category.name', 'Side Hustle')
        ->assertJsonPath('data.category.kind', 'custom');

    $this->assertDatabaseHas('categories', [
        'name' => 'Side Hustle',
        'is_system' => false,
    ]);
});

test('tenant member cannot create categories via api', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->withToken(apiTokenFor($member))
        ->postJson(route('api.categories.store'), [
            'name' => 'Blocked',
            'type' => CategoryType::Expense->value,
            'color' => '#ff0000',
        ])
        ->assertForbidden();
});

test('tenant owner can update system and custom categories via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Salary')->firstOrFail();

    $this->withToken(apiTokenFor($owner))
        ->putJson(route('api.categories.update', $category), [
            'name' => 'Base Salary',
            'color' => '#333333',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'Base Salary');

    expect($category->fresh()->name)->toBe('Base Salary');
});

test('system categories cannot be deleted via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $system = Category::query()->where('tenant_id', $tenant->id)->where('is_system', true)->firstOrFail();

    $this->withToken(apiTokenFor($owner))
        ->deleteJson(route('api.categories.destroy', $system))
        ->assertForbidden();
});

test('categories with transactions cannot be deleted via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $tenant->id)->where('name', 'Rent')->firstOrFail();

    expect(Transaction::query()->where('category_id', $category->id)->exists())->toBeTrue();

    $this->withToken(apiTokenFor($owner))
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => 'Categories with transactions cannot be deleted. Archive instead.',
        ]);
});

test('custom category without transactions can be deleted via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $category = Category::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Disposable',
        'type' => CategoryType::Expense,
        'color' => '#abcdef',
        'is_system' => false,
        'created_by' => $owner->id,
    ]);

    $this->withToken(apiTokenFor($owner))
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('category from another tenant returns not found', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $acme = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $category = Category::query()->where('tenant_id', $acme->id)->firstOrFail();

    $this->withToken(apiTokenFor($owner))
        ->withHeader('X-Tenant-Id', (string) Tenant::query()->where('slug', 'startup-inc')->value('id'))
        ->getJson(route('api.categories.show', $category))
        ->assertNotFound();
});

test('categories api requires authentication', function () {
    $this->getJson(route('api.categories.index'))->assertUnauthorized();
});
