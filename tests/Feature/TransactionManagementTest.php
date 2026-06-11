<?php

use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\TransactionType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant member can view transactions page', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('transactions/index')
            ->has('transactions')
            ->has('accounts')
            ->has('categories')
            ->where('permissions.create', true));
});

test('user can create income transaction with tags', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Income)
        ->firstOrFail();

    $balanceBefore = (float) $account->fresh()->balance;

    $this->actingAs($owner)
        ->post(route('transactions.store'), [
            'type' => TransactionType::Income->value,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 250.50,
            'occurred_at' => now()->toDateString(),
            'notes' => 'Bonus payment',
            'tags' => 'bonus, work',
        ])
        ->assertRedirect(route('transactions.index'));

    $transaction = Transaction::query()->where('notes', 'Bonus payment')->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(TransactionType::Income)
        ->and($transaction->tags)->toHaveCount(2);

    expect((float) $account->fresh()->balance)->toBe($balanceBefore + 250.50);

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Transaction (income) of 250.50 was created.',
        'log_name' => 'finance',
    ]);
});

test('user can create transfer between accounts', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $from = Account::query()->where('tenant_id', $tenant->id)->where('name', 'Main Checking')->firstOrFail();
    $to = Account::query()->where('tenant_id', $tenant->id)->where('name', 'Emergency Fund')->firstOrFail();

    $fromBefore = (float) $from->fresh()->balance;
    $toBefore = (float) $to->fresh()->balance;

    $this->actingAs($owner)
        ->post(route('transactions.store'), [
            'type' => TransactionType::Transfer->value,
            'account_id' => $from->id,
            'transfer_account_id' => $to->id,
            'amount' => 500,
            'occurred_at' => now()->toDateString(),
            'notes' => 'Move to savings',
        ])
        ->assertRedirect(route('transactions.index'));

    expect((float) $from->fresh()->balance)->toBe($fromBefore - 500)
        ->and((float) $to->fresh()->balance)->toBe($toBefore + 500);
});

test('user can update and delete transaction', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $transaction = Transaction::query()->create([
        'tenant_id' => $tenant->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 50,
        'notes' => 'Test expense',
        'occurred_at' => now(),
        'created_by' => $owner->id,
    ]);

    Account::query()->where('id', $account->id)->decrement('balance', 50);

    $this->actingAs($owner)
        ->put(route('transactions.update', $transaction), [
            'amount' => 75,
            'notes' => 'Updated expense',
        ])
        ->assertRedirect(route('transactions.index'));

    expect($transaction->fresh()->amount)->toBe('75.00')
        ->and($transaction->fresh()->notes)->toBe('Updated expense');

    $this->actingAs($owner)
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('transactions.index'));

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('transactions can be searched and filtered', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('transactions.index', ['type' => TransactionType::Income->value, 'search' => 'Salary']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.type', TransactionType::Income->value)
            ->where('filters.search', 'Salary'));
});

test('user can export transactions csv', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->actingAs($owner)->get(route('transactions.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('user can attach file to transaction', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    $this->actingAs($owner)
        ->post(route('transactions.store'), [
            'type' => TransactionType::Expense->value,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 25,
            'occurred_at' => now()->toDateString(),
            'notes' => 'Receipt attached',
            'attachment' => $file,
        ])
        ->assertRedirect(route('transactions.index'));

    $transaction = Transaction::query()->where('notes', 'Receipt attached')->firstOrFail();
    expect($transaction->attachments)->toHaveCount(1);
});

test('transactions are scoped to tenant', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $acme = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $acme->id)->firstOrFail();

    $this->actingAs($owner)
        ->delete(route('transactions.destroy', $transaction))
        ->assertNotFound();
});
