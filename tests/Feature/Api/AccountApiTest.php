<?php

use App\Models\Finance\Account;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\AccountType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function accountApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can list accounts and net worth', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->withToken(accountApiToken($member))
        ->getJson(route('api.accounts.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['accounts']]);

    $this->withToken(accountApiToken($member))
        ->getJson(route('api.net-worth.show'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['net_worth' => ['net_worth', 'accounts']]]);

    $this->withToken(accountApiToken($member))
        ->getJson(route('api.net-worth.history'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['net_worth' => ['history']]]);
});

test('tenant owner can manage accounts via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $create = $this->withToken(accountApiToken($owner))
        ->postJson(route('api.accounts.store'), [
            'name' => 'API Savings',
            'type' => AccountType::Savings->value,
            'balance' => 500,
        ]);

    $create->assertCreated()->assertJsonPath('data.account.name', 'API Savings');

    $accountId = $create->json('data.account.id');

    $this->withToken(accountApiToken($owner))
        ->putJson(route('api.accounts.update', $accountId), ['name' => 'API Savings Plus'])
        ->assertSuccessful()
        ->assertJsonPath('data.account.name', 'API Savings Plus');

    $emptyAccount = Account::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Temp Cash',
        'type' => AccountType::Cash,
        'balance' => 0,
        'currency' => 'USD',
        'created_by' => $owner->id,
    ]);

    $this->withToken(accountApiToken($owner))
        ->deleteJson(route('api.accounts.destroy', $emptyAccount))
        ->assertSuccessful();
});

test('tenant member cannot create accounts via api', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->withToken(accountApiToken($member))
        ->postJson(route('api.accounts.store'), [
            'name' => 'Blocked',
            'type' => AccountType::Checking->value,
        ])
        ->assertForbidden();
});
