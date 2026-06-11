<?php

namespace App\Modules\Tenant;

use App\Models\Platform\Tenant;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Modules\Tenant\Repositories\Eloquent\TenantRepository;
use App\Policies\Platform\TenantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);
    }
}
