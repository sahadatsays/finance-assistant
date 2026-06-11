<?php

use App\Models\User;
use App\Modules\Finance\Enums\InvestmentType;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant member can manage investments and view portfolio', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $create = $this->withToken($member->createToken('mobile')->plainTextToken)
        ->postJson(route('api.investments.store'), [
            'name' => 'Apple Inc',
            'symbol' => 'AAPL',
            'type' => InvestmentType::Stock->value,
            'quantity' => 10,
            'cost_basis' => 1500,
            'current_price' => 175,
        ]);

    $create->assertCreated()->assertJsonPath('data.investment.symbol', 'AAPL');

    $id = $create->json('data.investment.id');

    $this->withToken($member->createToken('mobile')->plainTextToken)
        ->getJson(route('api.investments.index'))
        ->assertSuccessful();

    $this->withToken($member->createToken('mobile')->plainTextToken)
        ->getJson(route('api.portfolio.performance'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['performance' => ['total_value', 'gain_loss']]]);

    $this->withToken($member->createToken('mobile')->plainTextToken)
        ->getJson(route('api.portfolio.allocation'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['allocation' => ['by_type', 'by_symbol']]]);

    $this->withToken($member->createToken('mobile')->plainTextToken)
        ->deleteJson(route('api.investments.destroy', $id))
        ->assertSuccessful();
});
