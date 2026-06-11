<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Attachment\StoreUploadRequest;
use App\Models\Finance\Attachment;
use App\Modules\Finance\Services\AttachmentService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class UploadController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private AttachmentService $attachments,
    ) {}

    public function store(StoreUploadRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('upload', [Attachment::class, $tenant]);

        try {
            $upload = $this->attachments->storePendingUpload(
                $tenant,
                $request->user(),
                $request->file('file'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['upload' => $upload],
            message: 'File uploaded successfully.',
            status: 201,
        );
    }
}
