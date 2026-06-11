<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformSetting;
use Illuminate\Support\Collection;

class PlatformSettingService
{
    /**
     * @return Collection<string, mixed>
     */
    public function all(): Collection
    {
        return PlatformSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (PlatformSetting $setting) => [
                $setting->key => $setting->getCastedValue(),
            ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function grouped(): array
    {
        return PlatformSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn (Collection $settings) => $settings->mapWithKeys(fn (PlatformSetting $s) => [
                $s->key => [
                    'value' => $s->getCastedValue(),
                    'type' => $s->type,
                ],
            ])->all())
            ->all();
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function updateMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            PlatformSetting::query()
                ->where('key', $key)
                ->update(['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = PlatformSetting::query()->where('key', $key)->first();

        return $setting?->getCastedValue() ?? $default;
    }
}
