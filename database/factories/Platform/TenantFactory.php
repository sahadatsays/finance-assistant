<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Tenant;
use App\Modules\Tenant\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => TenantStatus::Active,
            'settings' => [
                'timezone' => 'UTC',
                'locale' => 'en',
                'currency' => 'USD',
            ],
            'trial_ends_at' => null,
            'suspended_at' => null,
            'created_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Pending]);
    }

    public function trial(): static
    {
        return $this->state(fn () => [
            'status' => TenantStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => TenantStatus::Suspended,
            'suspended_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Cancelled]);
    }
}
