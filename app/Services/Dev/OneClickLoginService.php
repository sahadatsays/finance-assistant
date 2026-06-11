<?php

namespace App\Services\Dev;

use App\Models\User;
use Illuminate\Support\Collection;

class OneClickLoginService
{
    public function isEnabled(): bool
    {
        return (bool) config('dev.one_click_login', false);
    }

    /**
     * @return Collection<int, array{id: int, name: string, email: string, label: string, description: string}>
     */
    public function accounts(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $configuredEmails = collect(config('dev.accounts', []))
            ->keyBy('email');

        return User::query()
            ->whereIn('email', $configuredEmails->keys())
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($configuredEmails): array {
                $account = $configuredEmails->get($user->email, []);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'label' => $account['label'] ?? $user->name,
                    'description' => $account['description'] ?? $user->email,
                ];
            })
            ->sortBy(fn (array $account): int => $this->sortOrder($account['email']))
            ->values();
    }

    public function isAllowedUser(User $user): bool
    {
        return collect(config('dev.accounts', []))
            ->pluck('email')
            ->contains($user->email);
    }

    public function redirectPathFor(User $user): string
    {
        if ($user->isPlatformAdmin()) {
            return route('admin.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }

    private function sortOrder(string $email): int
    {
        $order = collect(config('dev.accounts', []))
            ->pluck('email')
            ->values()
            ->search($email);

        return $order === false ? 999 : (int) $order;
    }
}
