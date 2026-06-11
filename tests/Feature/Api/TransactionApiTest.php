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

function transactionApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can list transactions with pagination', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $response = $this->withToken(transactionApiToken($member))
        ->getJson(route('api.transactions.index', ['per_page' => 5]));

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Transactions retrieved successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'amount', 'notes', 'occurred_at', 'account', 'category', 'tags', 'attachments'],
            ],
            'meta' => [
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                'filters',
            ],
        ])
        ->assertJsonPath('meta.pagination.per_page', 5);
});

test('transactions can be filtered by type search and amount', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(transactionApiToken($owner))
        ->getJson(route('api.transactions.index', [
            'type' => TransactionType::Income->value,
            'search' => 'Salary',
            'amount_min' => 1000,
        ]));

    $response->assertSuccessful();

    $transactions = collect($response->json('data'));

    expect($transactions)->not->toBeEmpty()
        ->and($transactions->every(fn (array $t) => $t['type'] === 'income'))->toBeTrue()
        ->and($transactions->every(fn (array $t) => $t['amount'] >= 1000))->toBeTrue();
});

test('transactions can be sorted by amount ascending', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $response = $this->withToken(transactionApiToken($owner))
        ->getJson(route('api.transactions.index', [
            'sort' => 'amount',
            'direction' => 'asc',
            'per_page' => 20,
        ]));

    $response->assertSuccessful();

    $amounts = collect($response->json('data'))->pluck('amount')->all();
    $sorted = $amounts;
    sort($sorted);

    expect($amounts)->toBe($sorted);
});

test('tenant member can view a single transaction', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->withToken(transactionApiToken($member))
        ->getJson(route('api.transactions.show', $transaction))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['transaction' => ['id', 'type', 'amount', 'tags', 'attachments']]]);
});

test('user can create income transaction with tags via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Income)
        ->firstOrFail();

    $balanceBefore = (float) $account->fresh()->balance;

    $response = $this->withToken(transactionApiToken($owner))
        ->postJson(route('api.transactions.store'), [
            'type' => TransactionType::Income->value,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 250.50,
            'occurred_at' => now()->toDateString(),
            'notes' => 'API bonus payment',
            'tags' => ['bonus', 'work'],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.transaction.notes', 'API bonus payment')
        ->assertJsonCount(2, 'data.transaction.tags');

    expect((float) $account->fresh()->balance)->toBe($balanceBefore + 250.50);
});

test('user can create transfer via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $from = Account::query()->where('tenant_id', $tenant->id)->where('name', 'Main Checking')->firstOrFail();
    $to = Account::query()->where('tenant_id', $tenant->id)->where('name', 'Emergency Fund')->firstOrFail();

    $fromBefore = (float) $from->fresh()->balance;
    $toBefore = (float) $to->fresh()->balance;

    $this->withToken(transactionApiToken($owner))
        ->postJson(route('api.transactions.store'), [
            'type' => TransactionType::Transfer->value,
            'account_id' => $from->id,
            'transfer_account_id' => $to->id,
            'amount' => 500,
            'occurred_at' => now()->toDateString(),
            'notes' => 'API transfer',
        ])
        ->assertCreated()
        ->assertJsonPath('data.transaction.type', 'transfer');

    expect((float) $from->fresh()->balance)->toBe($fromBefore - 500)
        ->and((float) $to->fresh()->balance)->toBe($toBefore + 500);
});

test('user can update and delete transaction via api', function () {
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
        'notes' => 'API test expense',
        'occurred_at' => now(),
        'created_by' => $owner->id,
    ]);

    Account::query()->where('id', $account->id)->decrement('balance', 50);

    $this->withToken(transactionApiToken($owner))
        ->putJson(route('api.transactions.update', $transaction), [
            'amount' => 75,
            'notes' => 'Updated via API',
            'tags' => ['updated'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.transaction.notes', 'Updated via API')
        ->assertJsonCount(1, 'data.transaction.tags');

    expect($transaction->fresh()->amount)->toBe('75.00');

    $this->withToken(transactionApiToken($owner))
        ->deleteJson(route('api.transactions.destroy', $transaction))
        ->assertSuccessful()
        ->assertJson(['success' => true, 'message' => 'Transaction deleted successfully.']);

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

test('user can attach file to transaction via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $account = Account::query()->where('tenant_id', $tenant->id)->firstOrFail();
    $category = Category::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', CategoryType::Expense)
        ->firstOrFail();

    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');
    $token = transactionApiToken($owner);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->post(route('api.transactions.store'), [
        'type' => TransactionType::Expense->value,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 25,
        'occurred_at' => now()->toDateString(),
        'notes' => 'API receipt attached',
        'attachment' => $file,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.transaction.notes', 'API receipt attached')
        ->assertJsonCount(1, 'data.transaction.attachments')
        ->assertJsonPath('data.transaction.attachments.0.mime_type', 'application/pdf');
});

test('transaction from another tenant returns not found via api', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $acme = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $acme->id)->firstOrFail();

    $this->withToken(transactionApiToken($owner))
        ->withHeader('X-Tenant-Id', (string) Tenant::query()->where('slug', 'startup-inc')->value('id'))
        ->getJson(route('api.transactions.show', $transaction))
        ->assertNotFound();
});

test('transactions api requires authentication', function () {
    $this->getJson(route('api.transactions.index'))->assertUnauthorized();
});
