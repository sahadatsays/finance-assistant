<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Attachment;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Contracts\AttachmentStorage;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttachmentService
{
    private const PENDING_CACHE_PREFIX = 'pending_upload:';

    public function __construct(
        private AttachmentStorage $storage,
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @return array{
     *     id: string,
     *     original_name: string,
     *     mime_type: string,
     *     size: int,
     *     expires_at: string
     * }
     */
    public function storePendingUpload(Tenant $tenant, User $user, UploadedFile $file): array
    {
        $this->validateFile($file);

        $uploadId = (string) Str::uuid();
        $path = $this->storage->store($file, "pending/{$tenant->id}/{$uploadId}");
        $ttlHours = (int) config('api.attachments.pending_ttl_hours', 24);

        $payload = [
            'tenant_id' => $tenant->id,
            'uploaded_by' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
        ];

        Cache::put(
            self::PENDING_CACHE_PREFIX."{$tenant->id}:{$uploadId}",
            $payload,
            now()->addHours($ttlHours),
        );

        return [
            'id' => $uploadId,
            'original_name' => $payload['original_name'],
            'mime_type' => $payload['mime_type'],
            'size' => $payload['size'],
            'expires_at' => now()->addHours($ttlHours)->toIso8601String(),
        ];
    }

    public function storeForTransaction(
        Tenant $tenant,
        Transaction $transaction,
        User $user,
        UploadedFile $file,
    ): Attachment {
        $this->validateFile($file);
        $this->assertTransactionBelongsToTenant($tenant, $transaction);

        $path = $this->storage->store(
            $file,
            "attachments/{$tenant->id}/{$transaction->id}",
        );

        return $this->createAttachmentRecord($tenant, $transaction, $user, [
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
        ]);
    }

    public function attachPendingUpload(
        Tenant $tenant,
        Transaction $transaction,
        User $user,
        string $uploadId,
    ): Attachment {
        $this->assertTransactionBelongsToTenant($tenant, $transaction);

        $pending = Cache::pull(self::PENDING_CACHE_PREFIX."{$tenant->id}:{$uploadId}");

        if (! is_array($pending)) {
            throw new InvalidArgumentException('Upload not found or has expired.');
        }

        if ((int) $pending['uploaded_by'] !== $user->id) {
            throw new InvalidArgumentException('Upload does not belong to the current user.');
        }

        if (! $this->storage->exists($pending['path'])) {
            throw new InvalidArgumentException('Uploaded file is no longer available.');
        }

        $destination = "attachments/{$tenant->id}/{$transaction->id}/".basename($pending['path']);

        if (! $this->storage->move($pending['path'], $destination)) {
            throw new InvalidArgumentException('Failed to attach uploaded file.');
        }

        return $this->createAttachmentRecord($tenant, $transaction, $user, [
            'original_name' => $pending['original_name'],
            'path' => $destination,
            'mime_type' => $pending['mime_type'],
            'size' => (int) $pending['size'],
        ]);
    }

    public function findForTenant(Tenant $tenant, int $attachmentId): ?Attachment
    {
        return Attachment::query()
            ->where('tenant_id', $tenant->id)
            ->find($attachmentId);
    }

    /**
     * @return array{url: string, expires_at: string}
     */
    public function signedDownloadUrl(Attachment $attachment): array
    {
        $ttlMinutes = (int) config('api.attachments.signed_url_ttl_minutes', 30);
        $expiresAt = now()->addMinutes($ttlMinutes);

        return [
            'url' => URL::temporarySignedRoute(
                'api.attachments.file',
                $expiresAt,
                ['attachment' => $attachment->id],
            ),
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    public function delete(Attachment $attachment, User $user): void
    {
        if ($this->storage->exists($attachment->path)) {
            $this->storage->delete($attachment->path);
        }

        $attachment->delete();

        $this->activityLog->log(
            "Attachment \"{$attachment->original_name}\" was deleted.",
            logName: 'finance',
            subject: $attachment,
            causer: $user,
            tenant: $attachment->tenant,
        );
    }

    private function createAttachmentRecord(
        Tenant $tenant,
        Transaction $transaction,
        User $user,
        array $data,
    ): Attachment {
        $attachment = Attachment::query()->create([
            'tenant_id' => $tenant->id,
            'transaction_id' => $transaction->id,
            'original_name' => $data['original_name'],
            'path' => $data['path'],
            'mime_type' => $data['mime_type'],
            'size' => $data['size'],
            'uploaded_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Attachment \"{$attachment->original_name}\" was added to transaction #{$transaction->id}.",
            logName: 'finance',
            subject: $attachment,
            causer: $user,
            tenant: $tenant,
        );

        return $attachment;
    }

    private function validateFile(UploadedFile $file): void
    {
        $maxSizeKb = (int) config('api.attachments.max_size_kb', 5120);
        $allowedMimes = config('api.attachments.allowed_mimes', ['pdf', 'jpg', 'jpeg', 'png', 'webp']);

        if ($file->getSize() > ($maxSizeKb * 1024)) {
            throw new InvalidArgumentException('File exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedMimes, true)) {
            throw new InvalidArgumentException('File type is not allowed.');
        }
    }

    private function assertTransactionBelongsToTenant(Tenant $tenant, Transaction $transaction): void
    {
        if ($transaction->tenant_id !== $tenant->id) {
            throw new InvalidArgumentException('Transaction not found.');
        }
    }
}
