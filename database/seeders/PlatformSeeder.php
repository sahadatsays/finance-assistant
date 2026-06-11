<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@financeassistant.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_platform_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
