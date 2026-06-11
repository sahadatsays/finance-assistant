<?php

namespace Database\Seeders;

use App\Models\Finance\Account;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetLine;
use App\Models\Finance\Category;
use App\Models\Finance\Goal;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\AccountType;
use App\Modules\Finance\Enums\BudgetPeriodType;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Services\SystemCategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'acme-corp')->first();

        if ($tenant === null) {
            return;
        }

        $owner = User::query()->where('email', 'owner@acme.com')->first();

        app(SystemCategoryService::class)->seedForTenant($tenant);

        if ($tenant->accounts()->exists()) {
            return;
        }

        $checking = Account::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Checking',
            'type' => AccountType::Checking,
            'balance' => 12450.00,
            'currency' => 'USD',
            'created_by' => $owner?->id,
        ]);

        Account::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Emergency Fund',
            'type' => AccountType::Savings,
            'balance' => 8500.00,
            'currency' => 'USD',
            'created_by' => $owner?->id,
        ]);

        $categoryModels = Category::query()
            ->where('tenant_id', $tenant->id)
            ->get()
            ->keyBy('name');

        Category::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Rent',
            'type' => CategoryType::Expense,
            'color' => '#dc2626',
            'icon' => 'building',
            'is_system' => false,
            'created_by' => $owner?->id,
        ]);

        $categoryModels = Category::query()
            ->where('tenant_id', $tenant->id)
            ->get()
            ->keyBy('name');

        $this->seedTransactions($tenant, $checking, $categoryModels, $owner);
        $this->seedBudget($tenant, $categoryModels, $owner);
        $this->seedGoals($tenant, $owner);
    }

    /**
     * @param  Collection<string, Category>  $categories
     */
    private function seedTransactions(
        Tenant $tenant,
        Account $account,
        Collection $categories,
        ?User $owner,
    ): void {
        $transactions = [
            ['desc' => 'Monthly Salary', 'cat' => 'Salary', 'type' => TransactionType::Income, 'amount' => 5200, 'months_ago' => 0, 'day' => 1],
            ['desc' => 'Monthly Salary', 'cat' => 'Salary', 'type' => TransactionType::Income, 'amount' => 5200, 'months_ago' => 1, 'day' => 1],
            ['desc' => 'Monthly Salary', 'cat' => 'Salary', 'type' => TransactionType::Income, 'amount' => 5200, 'months_ago' => 2, 'day' => 1],
            ['desc' => 'Freelance Project', 'cat' => 'Freelance', 'type' => TransactionType::Income, 'amount' => 1200, 'months_ago' => 0, 'day' => 15],
            ['desc' => 'Freelance Project', 'cat' => 'Freelance', 'type' => TransactionType::Income, 'amount' => 800, 'months_ago' => 1, 'day' => 18],
            ['desc' => 'Apartment Rent', 'cat' => 'Rent', 'type' => TransactionType::Expense, 'amount' => 1800, 'months_ago' => 0, 'day' => 3],
            ['desc' => 'Apartment Rent', 'cat' => 'Rent', 'type' => TransactionType::Expense, 'amount' => 1800, 'months_ago' => 1, 'day' => 3],
            ['desc' => 'Apartment Rent', 'cat' => 'Rent', 'type' => TransactionType::Expense, 'amount' => 1800, 'months_ago' => 2, 'day' => 3],
            ['desc' => 'Whole Foods', 'cat' => 'Groceries', 'type' => TransactionType::Expense, 'amount' => 156.42, 'months_ago' => 0, 'day' => 8],
            ['desc' => 'Trader Joes', 'cat' => 'Groceries', 'type' => TransactionType::Expense, 'amount' => 89.15, 'months_ago' => 0, 'day' => 22],
            ['desc' => 'Electric Bill', 'cat' => 'Utilities', 'type' => TransactionType::Expense, 'amount' => 142.30, 'months_ago' => 0, 'day' => 12],
            ['desc' => 'Gas & Uber', 'cat' => 'Transport', 'type' => TransactionType::Expense, 'amount' => 215.00, 'months_ago' => 0, 'day' => 10],
            ['desc' => 'Netflix & Spotify', 'cat' => 'Entertainment', 'type' => TransactionType::Expense, 'amount' => 45.98, 'months_ago' => 0, 'day' => 5],
            ['desc' => 'Doctor Visit', 'cat' => 'Healthcare', 'type' => TransactionType::Expense, 'amount' => 75.00, 'months_ago' => 0, 'day' => 20],
            ['desc' => 'Grocery Run', 'cat' => 'Groceries', 'type' => TransactionType::Expense, 'amount' => 134.20, 'months_ago' => 1, 'day' => 14],
            ['desc' => 'Electric Bill', 'cat' => 'Utilities', 'type' => TransactionType::Expense, 'amount' => 128.50, 'months_ago' => 1, 'day' => 11],
            ['desc' => 'Weekend Outing', 'cat' => 'Entertainment', 'type' => TransactionType::Expense, 'amount' => 95.00, 'months_ago' => 1, 'day' => 25],
        ];

        foreach ($transactions as $tx) {
            $occurredAt = Carbon::now()
                ->subMonths($tx['months_ago'])
                ->day($tx['day'])
                ->setTime(12, 0);

            Transaction::query()->create([
                'tenant_id' => $tenant->id,
                'account_id' => $account->id,
                'category_id' => $categories[$tx['cat']]->id,
                'type' => $tx['type'],
                'amount' => $tx['amount'],
                'notes' => $tx['desc'],
                'occurred_at' => $occurredAt,
                'created_by' => $owner?->id,
            ]);
        }
    }

    /**
     * @param  Collection<string, Category>  $categories
     */
    private function seedBudget(Tenant $tenant, Collection $categories, ?User $owner): void
    {
        $monthly = Budget::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Monthly Budget',
            'period_type' => BudgetPeriodType::Monthly,
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'amount' => 3500.00,
            'is_active' => true,
            'created_by' => $owner?->id,
        ]);

        $lines = [
            'Rent' => 1800,
            'Groceries' => 500,
            'Utilities' => 200,
            'Transport' => 300,
            'Entertainment' => 150,
            'Healthcare' => 200,
        ];

        foreach ($lines as $name => $amount) {
            BudgetLine::query()->create([
                'budget_id' => $monthly->id,
                'category_id' => $categories[$name]->id,
                'amount' => $amount,
            ]);
        }

        $weekly = Budget::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Weekly Budget',
            'period_type' => BudgetPeriodType::Weekly,
            'period_start' => Carbon::now()->startOfWeek(),
            'period_end' => Carbon::now()->endOfWeek(),
            'amount' => 800.00,
            'is_active' => true,
            'created_by' => $owner?->id,
        ]);

        $weeklyLines = [
            'Groceries' => 150,
            'Transport' => 75,
            'Entertainment' => 50,
            'Healthcare' => 25,
        ];

        foreach ($weeklyLines as $name => $amount) {
            BudgetLine::query()->create([
                'budget_id' => $weekly->id,
                'category_id' => $categories[$name]->id,
                'amount' => $amount,
            ]);
        }
    }

    private function seedGoals(Tenant $tenant, ?User $owner): void
    {
        Goal::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Vacation Fund',
            'target_amount' => 5000,
            'current_amount' => 3200,
            'target_date' => Carbon::now()->addMonths(4),
            'color' => '#8b5cf6',
            'created_by' => $owner?->id,
        ]);

        Goal::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'New Laptop',
            'target_amount' => 2000,
            'current_amount' => 1450,
            'target_date' => Carbon::now()->addMonths(2),
            'color' => '#06b6d4',
            'created_by' => $owner?->id,
        ]);

        Goal::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Home Down Payment',
            'target_amount' => 50000,
            'current_amount' => 8500,
            'target_date' => Carbon::now()->addYears(2),
            'color' => '#10b981',
            'created_by' => $owner?->id,
        ]);
    }
}
