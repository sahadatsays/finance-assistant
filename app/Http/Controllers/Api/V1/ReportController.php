<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Report\ExportReportRequest;
use App\Http\Requests\Api\Report\ReportFilterRequest;
use App\Modules\Finance\Reports\Report;
use App\Modules\Finance\Services\ReportExportService;
use App\Modules\Finance\Services\ReportService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private ReportService $reports,
        private ReportExportService $export,
    ) {}

    public function summary(ReportFilterRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Report::class, $tenant]);

        return $this->success(
            data: ['report' => $this->reports->summary(
                $tenant,
                $this->parseDate($request->validated('from')),
                $this->parseDate($request->validated('to')),
            )],
            message: 'Report summary retrieved successfully.',
        );
    }

    public function monthly(ReportFilterRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Report::class, $tenant]);

        return $this->success(
            data: ['report' => $this->reports->monthly(
                $tenant,
                (int) $request->validated('months', 6),
            )],
            message: 'Monthly report retrieved successfully.',
        );
    }

    public function category(ReportFilterRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Report::class, $tenant]);

        return $this->success(
            data: ['report' => $this->reports->category(
                $tenant,
                $this->parseDate($request->validated('from')),
                $this->parseDate($request->validated('to')),
            )],
            message: 'Category report retrieved successfully.',
        );
    }

    public function cashflow(ReportFilterRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Report::class, $tenant]);

        return $this->success(
            data: ['report' => $this->reports->cashflow(
                $tenant,
                (int) $request->validated('months', 6),
            )],
            message: 'Cashflow report retrieved successfully.',
        );
    }

    public function netWorth(ReportFilterRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Report::class, $tenant]);

        return $this->success(
            data: ['report' => array_merge(
                $this->reports->netWorth($tenant),
                $this->reports->netWorthHistory($tenant, (int) $request->validated('months', 6)),
            )],
            message: 'Net worth report retrieved successfully.',
        );
    }

    public function export(ExportReportRequest $request): JsonResponse|StreamedResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('export', [Report::class, $tenant]);

        try {
            return $this->export->export($tenant, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }
    }

    private function parseDate(?string $value): ?Carbon
    {
        return $value !== null ? Carbon::parse($value) : null;
    }
}
