<?php

use App\Models\User;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function reportApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can view report summary', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $response = $this->withToken(reportApiToken($member))
        ->getJson(route('api.reports.summary'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'report' => ['period', 'income', 'expense', 'net', 'net_worth', 'budget_status'],
            ],
        ]);
});

test('tenant member can view monthly category cashflow and net worth reports', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->withToken(reportApiToken($owner))
        ->getJson(route('api.reports.monthly'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['report' => ['months']]]);

    $this->withToken(reportApiToken($owner))
        ->getJson(route('api.reports.category'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['report' => ['categories', 'total']]]);

    $this->withToken(reportApiToken($owner))
        ->getJson(route('api.reports.cashflow'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['report' => ['months']]]);

    $this->withToken(reportApiToken($owner))
        ->getJson(route('api.reports.net-worth'))
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'report' => ['net_worth', 'accounts', 'history'],
            ],
        ]);
});

test('tenant member can export reports as json csv and pdf', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $json = $this->withToken(reportApiToken($member))
        ->postJson(route('api.reports.export'), [
            'report' => 'summary',
            'format' => 'json',
        ]);

    $json->assertSuccessful();
    expect($json->headers->get('content-type'))->toContain('application/json');

    $csv = $this->withToken(reportApiToken($member))
        ->postJson(route('api.reports.export'), [
            'report' => 'monthly',
            'format' => 'csv',
        ]);

    $csv->assertSuccessful();
    expect($csv->headers->get('content-type'))->toContain('text/csv');

    $pdf = $this->withToken(reportApiToken($member))
        ->postJson(route('api.reports.export'), [
            'report' => 'category',
            'format' => 'pdf',
        ]);

    $pdf->assertSuccessful();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');
});

test('export rejects invalid report format', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->withToken(reportApiToken($owner))
        ->postJson(route('api.reports.export'), [
            'report' => 'summary',
            'format' => 'xlsx',
        ])
        ->assertUnprocessable();
});
