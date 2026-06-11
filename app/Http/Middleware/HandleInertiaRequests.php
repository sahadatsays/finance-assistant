<?php

namespace App\Http\Middleware;

use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'isPlatformAdmin' => $request->user()?->isPlatformAdmin() ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currency' => fn () => $this->resolveCurrency($request),
            'quickEntry' => fn () => $this->resolveQuickEntry($request),
        ];
    }

    private function resolveCurrency(Request $request): string
    {
        $user = $request->user();

        if ($user === null) {
            return 'USD';
        }

        $tenant = app(TenantContextService::class)->resolveForUser($user, $request);

        return $tenant?->settings['currency'] ?? 'USD';
    }

    /**
     * @return array{accounts: list<array{id: int, name: string, type: string}>, categories: list<array{id: int, name: string, type: string, color: string}>}|null
     */
    private function resolveQuickEntry(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $tenant = app(TenantContextService::class)->resolveForUser($user, $request);

        if ($tenant === null) {
            return null;
        }

        if (! Gate::forUser($user)->allows('create', [Transaction::class, $tenant])) {
            return null;
        }

        return [
            'accounts' => Account::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type'])
                ->all(),
            'categories' => Category::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'color'])
                ->all(),
        ];
    }
}
