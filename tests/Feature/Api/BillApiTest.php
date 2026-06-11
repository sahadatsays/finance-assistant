<?php

use App\Models\Finance\Bill;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\BillRecurrence;
use App\Modules\Finance\Enums\BillStatus;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function billApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('tenant member can create list and view upcoming bills', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $create = $this->withToken(billApiToken($member))
        ->postJson(route('api.bills.store'), [
            'name' => 'Internet Bill',
            'amount' => 79.99,
            'due_date' => now()->addDays(5)->toDateString(),
            'recurrence' => BillRecurrence::Monthly->value,
        ]);

    $create->assertCreated()
        ->assertJsonPath('data.bill.name', 'Internet Bill');

    $this->withToken(billApiToken($member))
        ->getJson(route('api.bills.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'meta' => ['pagination']]);

    $this->withToken(billApiToken($member))
        ->getJson(route('api.bills.upcoming'))
        ->assertSuccessful()
        ->assertJsonPath('data.bills.0.name', 'Internet Bill');
});

test('tenant member can update delete and mark bill paid', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    $bill = Bill::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Rent',
        'amount' => 1500,
        'due_date' => now()->addDays(3),
        'recurrence' => BillRecurrence::Monthly,
        'status' => BillStatus::Upcoming,
        'created_by' => $owner->id,
    ]);

    $this->withToken(billApiToken($owner))
        ->putJson(route('api.bills.update', $bill), ['amount' => 1550])
        ->assertSuccessful()
        ->assertJsonPath('data.bill.amount', 1550);

    $this->withToken(billApiToken($owner))
        ->postJson(route('api.bills.mark-paid', $bill))
        ->assertSuccessful()
        ->assertJsonPath('data.bill.status', BillStatus::Paid->value);

    expect(Bill::query()->where('tenant_id', $tenant->id)->where('name', 'Rent')->count())->toBe(2);

    $paidBill = Bill::query()->where('id', $bill->id)->firstOrFail();

    $this->withToken(billApiToken($owner))
        ->deleteJson(route('api.bills.destroy', $paidBill))
        ->assertSuccessful();

    expect($paidBill->fresh()->is_active)->toBeFalse();
});
