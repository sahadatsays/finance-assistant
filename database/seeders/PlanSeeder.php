<?php

namespace Database\Seeders;

use App\Models\Platform\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'For individuals getting started with personal finance.',
                'price_monthly' => 0,
                'max_users' => 1,
                'features' => ['accounts', 'transactions', 'basic_reports'],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For households and power users.',
                'price_monthly' => 9.99,
                'max_users' => 5,
                'features' => ['accounts', 'transactions', 'budgets', 'reports', 'exports'],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For small teams managing shared finances.',
                'price_monthly' => 29.99,
                'max_users' => 25,
                'features' => ['accounts', 'transactions', 'budgets', 'reports', 'exports', 'api_access'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true]),
            );
        }
    }
}
