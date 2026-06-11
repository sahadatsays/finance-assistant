<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDevice>
 */
class UserDeviceFactory extends Factory
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
            'device_fingerprint' => hash('sha256', fake()->uuid()),
            'name' => fake()->randomElement(['Chrome on macOS', 'Safari on iOS', 'Firefox on Windows']),
            'platform' => fake()->randomElement(['macOS', 'Windows', 'iOS', 'Android']),
            'browser' => fake()->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'is_trusted' => false,
            'last_active_at' => now(),
        ];
    }
}
