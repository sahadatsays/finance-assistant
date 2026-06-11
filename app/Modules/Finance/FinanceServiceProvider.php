<?php

namespace App\Modules\Finance;

use App\Models\Finance\Category;
use App\Policies\Finance\CategoryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
    }
}
