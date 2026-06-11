<?php

namespace App\OpenApi\Authenticated;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.0.0')]
#[OA\Info(
    version: '1.0.0',
    title: 'Finance Assistant API — Authenticated',
    description: 'Tenant-scoped finance APIs for the Finance Assistant multi-tenant SaaS platform. Requires Laravel Sanctum bearer token. Use the Authorize button with `Bearer {token}` from the login response.',
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\Tag(name: 'Authentication', description: 'Login and session management')]
#[OA\Tag(name: 'Categories', description: 'Income and expense categories')]
#[OA\Tag(name: 'Transactions', description: 'Income, expense, and transfer transactions')]
#[OA\Tag(name: 'Budgets', description: 'Budget planning and analysis')]
#[OA\Tag(name: 'Savings Goals', description: 'Savings goals with progress and forecasts')]
#[OA\Tag(name: 'Attachments', description: 'Receipt and document uploads')]
#[OA\Tag(name: 'Reports', description: 'Financial reports and exports')]
#[OA\Tag(name: 'Bills', description: 'Bill reminders and payments')]
#[OA\Tag(name: 'Accounts', description: 'Bank and cash accounts')]
#[OA\Tag(name: 'Investments', description: 'Investment holdings and portfolio')]
#[OA\Tag(name: 'Notifications', description: 'In-app notifications and device tokens')]
#[OA\Tag(name: 'Mobile Sync', description: 'Delta sync endpoints for Flutter mobile clients')]
#[OA\Tag(name: 'Dashboard', description: 'Tenant finance dashboard')]
class OpenApiDefinition {}
