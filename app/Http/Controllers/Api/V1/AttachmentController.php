<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Attachment\StoreTransactionAttachmentRequest;
use App\Models\Finance\Attachment;
use App\Modules\Finance\Contracts\AttachmentStorage;
use App\Modules\Finance\Resources\AttachmentResource;
use App\Modules\Finance\Services\AttachmentService;
use App\Modules\Finance\Services\TransactionService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private AttachmentService $attachments,
        private AttachmentStorage $storage,
        private TransactionService $transactions,
    ) {}

    public function show(Request $request, int $attachment): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->attachments->findForTenant($tenant, $attachment);

        if ($model === null) {
            return $this->error('Attachment not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['attachment' => new AttachmentResource($model)],
            message: 'Attachment retrieved successfully.',
        );
    }

    public function file(int $attachment): StreamedResponse|JsonResponse
    {
        $model = Attachment::query()->find($attachment);

        if ($model === null) {
            return $this->error('Attachment not found.', 404);
        }

        if (! $this->storage->exists($model->path)) {
            return $this->error('Attachment file not found.', 404);
        }

        return $this->storage->download($model->path, $model->original_name, $model->mime_type);
    }

    public function storeForTransaction(
        StoreTransactionAttachmentRequest $request,
        int $transaction,
    ): JsonResponse {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Attachment::class, $tenant]);

        $transactionModel = $this->transactions->findForTenant($tenant, $transaction);

        if ($transactionModel === null) {
            return $this->error('Transaction not found.', 404);
        }

        $this->authorize('update', $transactionModel);

        try {
            if ($request->hasFile('file')) {
                $attachment = $this->attachments->storeForTransaction(
                    $tenant,
                    $transactionModel,
                    $request->user(),
                    $request->file('file'),
                );
            } else {
                $attachment = $this->attachments->attachPendingUpload(
                    $tenant,
                    $transactionModel,
                    $request->user(),
                    (string) $request->validated('upload_id'),
                );
            }
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['attachment' => new AttachmentResource($attachment)],
            message: 'Attachment added successfully.',
            status: 201,
        );
    }

    public function destroy(Request $request, int $attachment): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->attachments->findForTenant($tenant, $attachment);

        if ($model === null) {
            return $this->error('Attachment not found.', 404);
        }

        $this->authorize('delete', $model);

        $this->attachments->delete($model, $request->user());

        return $this->success(message: 'Attachment deleted successfully.');
    }
}
