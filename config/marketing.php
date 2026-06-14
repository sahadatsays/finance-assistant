<?php

return [

    'demo_credentials' => [
        'email' => 'owner@acme.com',
        'password' => 'password',
    ],

    'seo' => [
        'home' => [
            'title' => 'Personal Finance Software for Individuals & Teams',
            'description' => 'Track spending, build budgets, hit savings goals, and see your net worth — free to start. Multi-tenant finance platform for households and small teams.',
        ],
        'features' => [
            'title' => 'Features — Budgets, Goals, Reports & More',
            'description' => 'Everything you need to manage money in one place. Dashboards, budgets, savings goals, reports, bills, investments, and mobile sync.',
        ],
        'pricing' => [
            'title' => 'Pricing — Free, Pro & Business Plans',
            'description' => 'Simple pricing that grows with you. Start free, upgrade when you need budgets, exports, or team features.',
        ],
        'about' => [
            'title' => 'About Us — Our Mission',
            'description' => 'We believe everyone deserves clarity about their money. Learn about Finance Assistant and our mission.',
        ],
        'contact' => [
            'title' => 'Contact Us',
            'description' => 'Get in touch with the Finance Assistant team for sales, support, or general inquiries.',
        ],
        'blog' => [
            'title' => 'Blog — Personal Finance Tips & Product Updates',
            'description' => 'Guides, budgeting tips, and product news from the Finance Assistant team.',
        ],
        'help' => [
            'title' => 'Help Center',
            'description' => 'Find answers to common questions about accounts, budgets, goals, billing, and security.',
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'description' => 'How Finance Assistant collects, uses, and protects your personal and financial data.',
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'description' => 'Terms and conditions for using the Finance Assistant SaaS platform.',
        ],
        'login' => [
            'title' => 'Log In',
            'description' => 'Sign in to your Finance Assistant account to manage your finances.',
        ],
        'register' => [
            'title' => 'Create Your Free Account',
            'description' => 'Start managing your money for free. No credit card required.',
        ],
    ],

    'feature_labels' => [
        'accounts' => 'Accounts & balances',
        'transactions' => 'Transactions & categories',
        'basic_reports' => 'Basic reports',
        'budgets' => 'Budget planning & alerts',
        'reports' => 'Advanced reports',
        'exports' => 'CSV & PDF exports',
        'api_access' => 'REST API access',
    ],

    'feature_categories' => [
        [
            'id' => 'dashboard',
            'title' => 'Dashboard & Overview',
            'description' => 'Real-time metrics for income, expenses, and net worth with interactive charts.',
            'items' => ['Cash flow visualization', 'Category breakdown', 'Multi-tenant workspace switching'],
            'plans' => ['free', 'pro', 'business'],
        ],
        [
            'id' => 'transactions',
            'title' => 'Accounts & Transactions',
            'description' => 'Track every dollar across bank, cash, and credit accounts.',
            'items' => ['Income, expense & transfer entries', 'Categories, tags & receipts', 'CSV export'],
            'plans' => ['free', 'pro', 'business'],
        ],
        [
            'id' => 'budgets',
            'title' => 'Budget Management',
            'description' => 'Set monthly budgets and get alerts before you overspend.',
            'items' => ['Monthly & category budgets', 'Utilization tracking', 'Budget vs actual reports'],
            'plans' => ['pro', 'business'],
        ],
        [
            'id' => 'goals',
            'title' => 'Savings Goals',
            'description' => 'Set targets, track contributions, and forecast completion dates.',
            'items' => ['Goal progress tracking', 'Contribution history', 'Completion forecasts'],
            'plans' => ['pro', 'business'],
        ],
        [
            'id' => 'reports',
            'title' => 'Reports & Insights',
            'description' => 'Understand your finances with summary, monthly, and cash flow reports.',
            'items' => ['Net worth history', 'Category & monthly reports', 'JSON, CSV, PDF export'],
            'plans' => ['free', 'pro', 'business'],
        ],
        [
            'id' => 'mobile',
            'title' => 'Mobile & API',
            'description' => 'Sync data across devices with delta sync and a full REST API.',
            'items' => ['Offline mobile sync', 'Device notifications', 'Sanctum-authenticated API'],
            'plans' => ['business'],
        ],
    ],

    'testimonials' => [
        [
            'quote' => 'I finally stopped juggling spreadsheets. Everything is in one dashboard.',
            'name' => 'Sarah M.',
            'role' => 'Individual user',
        ],
        [
            'quote' => 'We manage household spending together without stepping on each other\'s toes.',
            'name' => 'James & Priya K.',
            'role' => 'Pro plan household',
        ],
        [
            'quote' => 'Our small team tracks shared expenses with clear accountability.',
            'name' => 'Alex T.',
            'role' => 'Business plan team lead',
        ],
    ],

    'pricing_faq' => [
        [
            'question' => 'Can I use Finance Assistant for free forever?',
            'answer' => 'Yes. The Free plan includes accounts, transactions, and basic reports for one user at no cost.',
        ],
        [
            'question' => 'What happens when I hit plan limits?',
            'answer' => 'You will be prompted to upgrade when you need features or user seats beyond your current plan.',
        ],
        [
            'question' => 'Can I change plans anytime?',
            'answer' => 'Yes. Upgrade or downgrade your plan at any time from your workspace settings.',
        ],
        [
            'question' => 'Is there a trial for Pro?',
            'answer' => 'You can start on the Free plan and upgrade to Pro when you need budgets, exports, and multi-user access.',
        ],
        [
            'question' => 'Do you offer refunds?',
            'answer' => 'If you are not satisfied, contact us within 14 days of a paid upgrade for a full refund.',
        ],
    ],

    'blog_categories' => [
        'Guides',
        'Budgeting',
        'Saving',
        'Product Updates',
    ],

    'blog_posts' => [
        [
            'slug' => 'how-to-start-budgeting',
            'title' => 'How to Start Budgeting in 30 Minutes',
            'excerpt' => 'A practical guide to setting up your first budget without overwhelm.',
            'body' => "## Why start with 30 minutes?\n\nYou do not need a perfect spreadsheet to take control of your money. A simple first budget gives you clarity on where cash goes each month.\n\n## Step 1: List your income\n\nAdd salary, side income, and any recurring deposits. Use take-home pay when possible.\n\n## Step 2: Track fixed expenses\n\nRent, utilities, subscriptions, and loan payments usually stay predictable.\n\n## Step 3: Set flexible limits\n\nGroceries, dining, and entertainment are where most people adjust first.\n\n## Step 4: Review weekly\n\nSpend five minutes each week comparing actual spending to your plan. Small corrections beat big surprises.",
            'category' => 'Guides',
            'date' => '2026-05-15',
            'read_time' => '6 min',
        ],
        [
            'slug' => '50-30-20-rule-explained',
            'title' => 'The 50/30/20 Rule Explained',
            'excerpt' => 'Learn how to split your income between needs, wants, and savings.',
            'category' => 'Budgeting',
            'date' => '2026-05-01',
            'read_time' => '5 min',
        ],
        [
            'slug' => 'track-net-worth',
            'title' => 'Why Tracking Net Worth Changes Everything',
            'excerpt' => 'Net worth is the single number that tells you if you are moving forward.',
            'category' => 'Guides',
            'date' => '2026-04-20',
            'read_time' => '4 min',
        ],
        [
            'slug' => 'introducing-finance-assistant',
            'title' => 'Introducing Finance Assistant',
            'excerpt' => 'Meet the multi-tenant personal finance platform built for individuals and teams.',
            'category' => 'Product Updates',
            'date' => '2026-04-01',
            'read_time' => '3 min',
        ],
    ],

    'help_categories' => [
        [
            'slug' => 'getting-started',
            'title' => 'Getting Started',
            'description' => 'Create your account and add your first transaction.',
            'articles' => [
                ['slug' => 'create-account', 'title' => 'Create your account'],
                ['slug' => 'verify-email', 'title' => 'Verify your email'],
                ['slug' => 'first-transaction', 'title' => 'Add your first transaction'],
            ],
        ],
        [
            'slug' => 'accounts',
            'title' => 'Accounts & Transactions',
            'description' => 'Manage accounts, categories, and transaction history.',
            'articles' => [
                ['slug' => 'add-account', 'title' => 'Add a bank or cash account'],
                ['slug' => 'import-export', 'title' => 'Import and export transactions'],
            ],
        ],
        [
            'slug' => 'budgets',
            'title' => 'Budgets & Goals',
            'description' => 'Set budgets, track goals, and monitor progress.',
            'articles' => [
                ['slug' => 'create-budget', 'title' => 'Create a monthly budget'],
                ['slug' => 'savings-goal', 'title' => 'Set up a savings goal'],
            ],
        ],
        [
            'slug' => 'billing',
            'title' => 'Billing & Plans',
            'description' => 'Compare plans and manage your subscription.',
            'articles' => [
                ['slug' => 'compare-plans', 'title' => 'Compare Free, Pro, and Business'],
                ['slug' => 'upgrade-plan', 'title' => 'Upgrade your plan'],
            ],
        ],
        [
            'slug' => 'security',
            'title' => 'Security & Privacy',
            'description' => 'How we protect your data and secure your account.',
            'articles' => [
                ['slug' => 'data-protection', 'title' => 'How we protect your data'],
                ['slug' => 'two-factor-auth', 'title' => 'Enable two-factor authentication'],
            ],
        ],
    ],

    'contact_subjects' => [
        'general' => 'General inquiry',
        'sales' => 'Sales',
        'support' => 'Support',
        'partnership' => 'Partnership',
    ],

];
