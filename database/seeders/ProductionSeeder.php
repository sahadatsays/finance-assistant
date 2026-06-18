<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production-ready platform data without demo tenants or sample finance data.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            PlatformSettingSeeder::class,
            WebsiteContentSeeder::class,
        ]);

        $this->createSuperAdmin();
    }

    private function createSuperAdmin(): void
    {
        $name = (string) config('seeding.production.admin_name');
        $email = (string) config('seeding.production.admin_email');
        $password = config('seeding.production.admin_password');

        if (blank($password)) {
            throw new RuntimeException(
                'PRODUCTION_ADMIN_PASSWORD must be set in the environment before running production seeds.',
            );
        }

        if (strlen((string) $password) < 12) {
            throw new RuntimeException(
                'PRODUCTION_ADMIN_PASSWORD must be at least 12 characters long.',
            );
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make((string) $password),
                'is_platform_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        if ($user->profile === null) {
            $user->profile()->create([]);
        }
    }
}
