<?php

namespace App\Modules\Finance;

use App\Models\Finance\Budget;
use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Policies\Finance\BudgetPolicy;
use App\Policies\Finance\CategoryPolicy;
use App\Policies\Finance\TransactionPolicy;
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
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
