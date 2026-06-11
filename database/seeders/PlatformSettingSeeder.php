<?php

namespace Database\Seeders;

use App\Models\Platform\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'app_name', 'value' => 'Finance Assistant', 'type' => 'string'],
            ['group' => 'general', 'key' => 'support_email', 'value' => 'support@financeassistant.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'trial_days', 'value' => '14', 'type' => 'integer'],
            ['group' => 'general', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'general', 'key' => 'allow_registration', 'value' => '1', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
