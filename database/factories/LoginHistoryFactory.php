<?php

namespace Database\Factories;

use App\Enums\LoginMethod;
use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginHistory>
 */
class LoginHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'user_device_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'login_method' => LoginMethod::Password,
            'status' => LoginStatus::Success,
            'failure_reason' => null,
            'logged_in_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoginStatus::Failed,
            'failure_reason' => 'Invalid credentials.',
        ]);
    }

    public function withDevice(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_device_id' => UserDevice::factory(),
        ]);
    }
}
