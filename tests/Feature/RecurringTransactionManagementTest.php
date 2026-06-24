<?php

use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\RecurringTransaction;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\RecurrenceFrequency;
use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Services\RecurringTransactionService;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant member can view scheduled transactions page', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('recurring-transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recurring-transactions/index')
            ->has('frequencies', 5)
            ->where('permissions.create', true));
});

test('tenant member can create daily expense schedule', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $this->actingAs($member)
        ->post(route('recurring-transactions.store'), [
            'name' => 'Daily Coffee',
            'type' => TransactionType::Expense->value,
            'amount' => 5.50,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'frequency' => RecurrenceFrequency::Daily->value,
            'run_time' => '08:30',
            'start_date' => now()->toDateString(),
        ])
        ->assertRedirect(route('recurring-transactions.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'tenant_id' => $tenant->id,
        'name' => 'Daily Coffee',
        'frequency' => RecurrenceFrequency::Daily->value,
        'is_active' => true,
    ]);
});

test('tenant member can create biweekly salary schedule', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Income)
        ->firstOrFail();

    $startDate = now()->next(Carbon::FRIDAY)->toDateString();

    $this->actingAs($member)
        ->post(route('recurring-transactions.store'), [
            'name' => 'Salary',
            'type' => TransactionType::Income->value,
            'amount' => 2500,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'frequency' => RecurrenceFrequency::Biweekly->value,
            'run_time' => '09:00',
            'start_date' => $startDate,
        ])
        ->assertRedirect(route('recurring-transactions.index'));

    $rule = RecurringTransaction::query()->where('name', 'Salary')->firstOrFail();

    expect($rule->frequency)->toBe(RecurrenceFrequency::Biweekly)
        ->and($rule->type)->toBe(TransactionType::Income);
});

test('due recurring schedules create transactions and advance next run', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $balanceBefore = (float) $account->fresh()->balance;
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $dueAt = now()->subHour();

    $rule = RecurringTransaction::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Daily Lunch',
        'type' => TransactionType::Expense,
        'amount' => 12.50,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'frequency' => RecurrenceFrequency::Daily,
        'run_time' => '12:00:00',
        'start_date' => $dueAt->copy()->subDay()->toDateString(),
        'next_run_at' => $dueAt,
        'is_active' => true,
        'created_by' => $owner->id,
    ]);

    $processed = app(RecurringTransactionService::class)->processDue(now());

    expect($processed)->toBe(1);

    $rule->refresh();
    $transaction = Transaction::query()
        ->where('recurring_transaction_id', $rule->id)
        ->firstOrFail();

    expect((float) $transaction->amount)->toBe(12.50)
        ->and($transaction->type)->toBe(TransactionType::Expense)
        ->and($transaction->notes)->toContain('Auto: Daily Lunch')
        ->and((float) $account->fresh()->balance)->toBe($balanceBefore - 12.50)
        ->and($rule->last_run_at?->toDateTimeString())->toBe($dueAt->toDateTimeString())
        ->and($rule->next_run_at->greaterThan($dueAt))->toBeTrue();
});

test('processing the same due schedule twice does not duplicate transactions', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Income)
        ->firstOrFail();

    $dueAt = now()->subMinutes(30);

    RecurringTransaction::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Paycheck',
        'type' => TransactionType::Income,
        'amount' => 1000,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'frequency' => RecurrenceFrequency::Biweekly,
        'run_time' => '09:00:00',
        'start_date' => $dueAt->copy()->subWeeks(2)->toDateString(),
        'next_run_at' => $dueAt,
        'is_active' => true,
        'created_by' => $owner->id,
    ]);

    $service = app(RecurringTransactionService::class);

    expect($service->processDue(now()))->toBe(1)
        ->and($service->processDue(now()))->toBe(0)
        ->and(Transaction::query()->where('notes', 'like', 'Auto: Paycheck%')->count())->toBe(1);
});

test('scheduled transaction can be paused and resumed', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $rule = RecurringTransaction::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Gym',
        'type' => TransactionType::Expense,
        'amount' => 30,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'frequency' => RecurrenceFrequency::Monthly,
        'run_time' => '07:00:00',
        'start_date' => now()->toDateString(),
        'next_run_at' => now()->addDay(),
        'is_active' => true,
        'created_by' => $member->id,
    ]);

    $this->actingAs($member)
        ->delete(route('recurring-transactions.destroy', $rule))
        ->assertRedirect(route('recurring-transactions.index'));

    expect($rule->fresh()->is_active)->toBeFalse();

    $this->actingAs($member)
        ->post(route('recurring-transactions.resume', $rule))
        ->assertRedirect(route('recurring-transactions.index'));

    expect($rule->fresh()->is_active)->toBeTrue();
});

test('every minute schedule catches up missed runs', function () {
    Carbon::setTestNow('2026-06-24 10:04:30');

    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $firstDue = Carbon::parse('2026-06-24 10:03:00');

    RecurringTransaction::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Minute Tick',
        'type' => TransactionType::Expense,
        'amount' => 1,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'frequency' => RecurrenceFrequency::EveryMinute,
        'run_time' => '00:00:00',
        'start_date' => $firstDue->toDateString(),
        'next_run_at' => $firstDue,
        'is_active' => true,
        'created_by' => $owner->id,
    ]);

    $processed = app(RecurringTransactionService::class)->processDue(now());

    Carbon::setTestNow();

    expect($processed)->toBe(2)
        ->and(Transaction::query()->where('notes', 'like', 'Auto: Minute Tick%')->count())->toBe(2);
});

test('process recurring transactions command runs successfully', function () {
    $this->artisan('finance:process-recurring-transactions')
        ->assertSuccessful();
});
