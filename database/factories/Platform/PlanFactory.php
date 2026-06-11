<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price_monthly' => fake()->randomFloat(2, 0, 99),
            'max_users' => fake()->numberBetween(1, 50),
            'features' => ['transactions', 'budgets', 'reports'],
            'is_active' => true,
        ];
    }
}
