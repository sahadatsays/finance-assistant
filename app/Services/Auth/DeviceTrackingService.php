<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceTrackingService
{
    /**
     * Track or update a device for the authenticated user.
     */
    public function track(
        User $user,
        Request $request,
        ?int $tokenId = null,
        ?string $sessionId = null,
        ?string $deviceName = null,
    ): UserDevice {
        $fingerprint = $this->resolveFingerprint($request);
        $parsed = $this->parseUserAgent($request->userAgent());

        return UserDevice::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'name' => $deviceName ?? $parsed['name'],
                'platform' => $parsed['platform'],
                'browser' => $parsed['browser'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_active_at' => now(),
                'token_id' => $tokenId,
                'session_id' => $sessionId ?? ($request->hasSession() ? $request->session()->getId() : null),
            ],
        );
    }

    /**
     * Resolve a stable device fingerprint from the request.
     */
    public function resolveFingerprint(Request $request): string
    {
        $provided = $request->header('X-Device-Id') ?? $request->input('device_id');

        if (is_string($provided) && $provided !== '') {
            return hash('sha256', $provided);
        }

        return hash('sha256', implode('|', array_filter([
            $request->userAgent(),
            $request->ip(),
        ])));
    }

    /**
     * @return array{name: string, platform: string|null, browser: string|null}
     */
    public function parseUserAgent(?string $userAgent): array
    {
        if ($userAgent === null || $userAgent === '') {
            return [
                'name' => 'Unknown device',
                'platform' => null,
                'browser' => null,
            ];
        }

        $browser = $this->detectBrowser($userAgent);
        $platform = $this->detectPlatform($userAgent);

        return [
            'name' => trim(implode(' on ', array_filter([$browser, $platform]))) ?: 'Unknown device',
            'platform' => $platform,
            'browser' => $browser,
        ];
    }

    private function detectBrowser(string $userAgent): ?string
    {
        return match (true) {
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Edg') => 'Edge',
            str_contains($userAgent, 'Chrome') => 'Chrome',
            str_contains($userAgent, 'Safari') => 'Safari',
            default => null,
        };
    }

    private function detectPlatform(string $userAgent): ?string
    {
        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'iPhone') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };
    }
}
