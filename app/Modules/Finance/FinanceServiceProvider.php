<?php

namespace App\Modules\Finance;

use App\Models\Finance\Attachment;
use App\Models\Finance\Budget;
use App\Models\Finance\Category;
use App\Models\Finance\Goal;
use App\Models\Finance\Transaction;
use App\Modules\Finance\Contracts\AttachmentStorage;
use App\Modules\Finance\Services\AttachmentStorageService;
use App\Policies\Finance\AttachmentPolicy;
use App\Policies\Finance\BudgetPolicy;
use App\Policies\Finance\CategoryPolicy;
use App\Policies\Finance\GoalPolicy;
use App\Policies\Finance\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AttachmentStorage::class, AttachmentStorageService::class);
    }

    public function boot(): void
    {
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Goal::class, GoalPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
