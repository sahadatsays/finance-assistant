<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\CategoryType;

class SystemCategoryService
{
    /**
     * @var list<array{name: string, type: CategoryType, color: string, icon: string}>
     */
    private const array DEFINITIONS = [
        ['name' => 'Salary', 'type' => CategoryType::Income, 'color' => '#10b981', 'icon' => 'banknote'],
        ['name' => 'Freelance', 'type' => CategoryType::Income, 'color' => '#06b6d4', 'icon' => 'briefcase'],
        ['name' => 'Investments', 'type' => CategoryType::Income, 'color' => '#8b5cf6', 'icon' => 'trending-up'],
        ['name' => 'Gifts', 'type' => CategoryType::Income, 'color' => '#ec4899', 'icon' => 'gift'],
        ['name' => 'Other Income', 'type' => CategoryType::Income, 'color' => '#64748b', 'icon' => 'circle-dollar-sign'],
        ['name' => 'Housing', 'type' => CategoryType::Expense, 'color' => '#ef4444', 'icon' => 'home'],
        ['name' => 'Groceries', 'type' => CategoryType::Expense, 'color' => '#f59e0b', 'icon' => 'shopping-cart'],
        ['name' => 'Transport', 'type' => CategoryType::Expense, 'color' => '#3b82f6', 'icon' => 'car'],
        ['name' => 'Utilities', 'type' => CategoryType::Expense, 'color' => '#8b5cf6', 'icon' => 'zap'],
        ['name' => 'Healthcare', 'type' => CategoryType::Expense, 'color' => '#14b8a6', 'icon' => 'heart-pulse'],
        ['name' => 'Entertainment', 'type' => CategoryType::Expense, 'color' => '#ec4899', 'icon' => 'tv'],
        ['name' => 'Shopping', 'type' => CategoryType::Expense, 'color' => '#f97316', 'icon' => 'shopping-bag'],
        ['name' => 'Education', 'type' => CategoryType::Expense, 'color' => '#6366f1', 'icon' => 'graduation-cap'],
        ['name' => 'Insurance', 'type' => CategoryType::Expense, 'color' => '#0ea5e9', 'icon' => 'shield'],
        ['name' => 'Other Expense', 'type' => CategoryType::Expense, 'color' => '#64748b', 'icon' => 'receipt'],
    ];

    public function seedForTenant(Tenant $tenant): void
    {
        foreach (self::DEFINITIONS as $definition) {
            Category::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $definition['name'],
                    'type' => $definition['type'],
                    'is_system' => true,
                ],
                [
                    'color' => $definition['color'],
                    'icon' => $definition['icon'],
                    'is_active' => true,
                ],
            );
        }
    }
}
