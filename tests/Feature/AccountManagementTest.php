<?php

use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\AccountType;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\TransactionType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant owner can view accounts page with demo accounts', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('accounts/index')
            ->has('accounts', 2)
            ->where('permissions.create', true)
            ->where('permissions.update', true)
            ->has('accountTypes', 4)
            ->has('summary.by_currency', 1)
            ->where('summary.by_currency.0.currency', 'USD'));
});

test('tenant member can view but cannot manage accounts', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('permissions.create', false)
            ->where('permissions.update', false));

    $this->actingAs($member)
        ->post(route('accounts.store'), [
            'name' => 'Blocked Account',
            'type' => AccountType::Cash->value,
            'balance' => 100,
        ])
        ->assertForbidden();
});

test('tenant owner can create account with starting balance', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('accounts.store'), [
            'name' => 'Petty Cash',
            'type' => AccountType::Cash->value,
            'balance' => 150.25,
            'currency' => 'USD',
        ])
        ->assertRedirect(route('accounts.index'));

    $this->assertDatabaseHas('accounts', [
        'name' => 'Petty Cash',
        'type' => AccountType::Cash->value,
        'balance' => 150.25,
        'is_active' => true,
    ]);
});

test('tenant owner can update account details but not balance directly', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $account = Account::query()->where('name', 'Main Checking')->firstOrFail();
    $balanceBefore = (float) $account->balance;

    $this->actingAs($owner)
        ->put(route('accounts.update', $account), [
            'name' => 'Primary Checking',
            'type' => AccountType::Checking->value,
        ])
        ->assertRedirect(route('accounts.index'));

    $account->refresh();

    expect($account->name)->toBe('Primary Checking')
        ->and((float) $account->balance)->toBe($balanceBefore);
});

test('tenant owner cannot archive account with transactions', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $account = Account::query()
        ->where('tenant_id', $tenant->id)
        ->whereHas('transactions')
        ->firstOrFail();

    $this->actingAs($owner)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect()
        ->assertSessionHasErrors('account');

    expect($account->fresh()->is_active)->toBeTrue();
});

test('tenant owner can archive empty account', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $account = Account::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Unused Cash',
        'type' => AccountType::Cash,
        'balance' => 0,
        'currency' => 'USD',
        'created_by' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect(route('accounts.index'));

    expect($account->fresh()->is_active)->toBeFalse();
});

test('income and expense transactions update account balance', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $account = Account::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Flow Test',
        'type' => AccountType::Checking,
        'balance' => 1000,
        'currency' => 'USD',
        'created_by' => $owner->id,
    ]);

    $incomeCategory = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Income)
        ->firstOrFail();

    $expenseCategory = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $this->actingAs($owner)
        ->post(route('transactions.store'), [
            'type' => TransactionType::Income->value,
            'amount' => 500,
            'account_id' => $account->id,
            'category_id' => $incomeCategory->id,
            'description' => 'Paycheck',
            'occurred_at' => now()->toDateString(),
        ])
        ->assertRedirect();

    expect((float) $account->fresh()->balance)->toBe(1500.0);

    $this->actingAs($owner)
        ->post(route('transactions.store'), [
            'type' => TransactionType::Expense->value,
            'amount' => 200,
            'account_id' => $account->id,
            'category_id' => $expenseCategory->id,
            'description' => 'Groceries',
            'occurred_at' => now()->toDateString(),
        ])
        ->assertRedirect();

    expect((float) $account->fresh()->balance)->toBe(1300.0);
});
