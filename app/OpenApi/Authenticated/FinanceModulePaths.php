<?php

namespace App\OpenApi\Authenticated;

use OpenApi\Attributes as OA;

/**
 * Module-level path documentation for authenticated finance APIs.
 * Category endpoints are fully documented on CategoryController.
 */
class FinanceModulePaths
{
    #[OA\Get(
        path: '/dashboard',
        operationId: 'getDashboard',
        summary: 'Tenant finance dashboard',
        description: 'Returns metrics, charts, and widgets for the active tenant workspace.',
        tags: ['Dashboard'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/XTenantId')],
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
    public function dashboard(): void {}

    #[OA\Get(path: '/transactions', operationId: 'listTransactions', summary: 'List transactions', tags: ['Transactions'], security: [['sanctum' => []], ['tenant' => []]], parameters: [new OA\Parameter(ref: '#/components/parameters/XTenantId'), new OA\Parameter(ref: '#/components/parameters/Page'), new OA\Parameter(ref: '#/components/parameters/PerPage')], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200), new OA\Response(ref: '#/components/responses/Unauthorized', response: 401)])]
    #[OA\Post(path: '/transactions', operationId: 'createTransaction', summary: 'Create transaction', tags: ['Transactions'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 201), new OA\Response(ref: '#/components/responses/ValidationError', response: 422)])]
    #[OA\Get(path: '/budgets', operationId: 'listBudgets', summary: 'List budgets', tags: ['Budgets'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/budgets/{id}/analysis', operationId: 'budgetAnalysis', summary: 'Budget analysis', tags: ['Budgets'], security: [['sanctum' => []], ['tenant' => []]], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200), new OA\Response(ref: '#/components/responses/NotFound', response: 404)])]
    #[OA\Get(path: '/goals', operationId: 'listGoals', summary: 'List savings goals', tags: ['Savings Goals'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Post(path: '/goals/{id}/contribute', operationId: 'contributeToGoal', summary: 'Add goal contribution', tags: ['Savings Goals'], security: [['sanctum' => []], ['tenant' => []]], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(ref: '#/components/responses/Success', response: 201), new OA\Response(ref: '#/components/responses/ValidationError', response: 422)])]
    #[OA\Get(path: '/reports/summary', operationId: 'reportSummary', summary: 'Financial summary report', tags: ['Reports'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Post(path: '/reports/export', operationId: 'exportReport', summary: 'Export report', description: 'Export reports as JSON, CSV, or PDF.', tags: ['Reports'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200), new OA\Response(ref: '#/components/responses/ValidationError', response: 422)])]
    #[OA\Get(path: '/bills/upcoming', operationId: 'upcomingBills', summary: 'Upcoming bills', tags: ['Bills'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/accounts', operationId: 'listAccounts', summary: 'List accounts', tags: ['Accounts'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/net-worth', operationId: 'getNetWorth', summary: 'Current net worth', tags: ['Accounts'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/investments', operationId: 'listInvestments', summary: 'List investments', tags: ['Investments'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/portfolio/performance', operationId: 'portfolioPerformance', summary: 'Portfolio performance', tags: ['Investments'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/notifications', operationId: 'listNotifications', summary: 'List notifications', tags: ['Notifications'], security: [['sanctum' => []], ['tenant' => []]], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    #[OA\Get(path: '/sync/transactions', operationId: 'syncTransactions', summary: 'Delta sync transactions', description: 'Returns changed records since the `since` timestamp for offline mobile sync.', tags: ['Mobile Sync'], security: [['sanctum' => []], ['tenant' => []]], parameters: [new OA\Parameter(name: 'since', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time'))], responses: [new OA\Response(ref: '#/components/responses/Success', response: 200)])]
    public function modules(): void {}
}
