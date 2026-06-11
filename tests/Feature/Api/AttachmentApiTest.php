<?php

use App\Models\Finance\Attachment;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

function attachmentApiToken(User $user): string
{
    return $user->createToken('mobile')->plainTextToken;
}

test('user can upload pdf and image files', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $pdfResponse = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.uploads.store'), [
            'file' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ]);

    $pdfResponse->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'upload' => ['id', 'original_name', 'mime_type', 'size', 'expires_at'],
            ],
        ]);

    $imageResponse = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.uploads.store'), [
            'file' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

    $imageResponse->assertCreated()
        ->assertJsonPath('data.upload.mime_type', 'image/jpeg');
});

test('user can attach pending upload to transaction', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $uploadResponse = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.uploads.store'), [
            'file' => UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf'),
        ]);

    $uploadId = $uploadResponse->json('data.upload.id');

    $response = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.transactions.attachments.store', $transaction), [
            'upload_id' => $uploadId,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.attachment.original_name', 'invoice.pdf')
        ->assertJsonPath('data.attachment.transaction_id', $transaction->id)
        ->assertJsonStructure([
            'data' => [
                'attachment' => ['id', 'url', 'url_expires_at'],
            ],
        ]);

    $this->assertDatabaseHas('attachments', [
        'transaction_id' => $transaction->id,
        'original_name' => 'invoice.pdf',
    ]);
});

test('user can attach file directly to transaction', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $response = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.transactions.attachments.store', $transaction), [
            'file' => UploadedFile::fake()->image('photo.png'),
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.attachment.mime_type', 'image/png');
});

test('user can view attachment with secure download url', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $createResponse = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.transactions.attachments.store', $transaction), [
            'file' => UploadedFile::fake()->create('statement.pdf', 80, 'application/pdf'),
        ]);

    $attachmentId = $createResponse->json('data.attachment.id');

    $response = $this->withToken(attachmentApiToken($owner))
        ->getJson(route('api.attachments.show', $attachmentId));

    $response->assertSuccessful()
        ->assertJsonPath('data.attachment.id', $attachmentId)
        ->assertJsonStructure([
            'data' => [
                'attachment' => ['url', 'url_expires_at'],
            ],
        ]);

    $downloadUrl = $response->json('data.attachment.url');

    $this->get($downloadUrl)->assertSuccessful();
});

test('user can delete attachment via api', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $tenant = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();
    $transaction = Transaction::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $createResponse = $this->withToken(attachmentApiToken($owner))
        ->postJson(route('api.transactions.attachments.store', $transaction), [
            'file' => UploadedFile::fake()->create('temp.pdf', 20, 'application/pdf'),
        ]);

    $attachmentId = $createResponse->json('data.attachment.id');

    $this->withToken(attachmentApiToken($owner))
        ->deleteJson(route('api.attachments.destroy', $attachmentId))
        ->assertSuccessful();

    expect(Attachment::query()->find($attachmentId))->toBeNull();
});

test('attachment from another tenant returns not found', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    $otherTenant = Tenant::query()->where('slug', '!=', 'acme-corp')->first();

    if ($otherTenant === null) {
        $this->markTestSkipped('No secondary tenant seeded.');
    }

    $foreignTransaction = Transaction::query()->where('tenant_id', $otherTenant->id)->first();

    if ($foreignTransaction === null) {
        $this->markTestSkipped('No transaction in secondary tenant.');
    }

    $foreignAttachment = Attachment::query()->where('tenant_id', $otherTenant->id)->first();

    if ($foreignAttachment === null) {
        $createResponse = $this->withToken(attachmentApiToken(
            User::query()->whereHas('tenants', fn ($q) => $q->where('tenants.id', $otherTenant->id))->first()
                ?? User::query()->where('email', 'owner@startup.com')->firstOrFail()
        ))
            ->postJson(route('api.transactions.attachments.store', $foreignTransaction), [
                'file' => UploadedFile::fake()->create('foreign.pdf', 20, 'application/pdf'),
            ]);

        $foreignAttachment = Attachment::query()->find($createResponse->json('data.attachment.id'));
    }

    $this->withToken(attachmentApiToken($owner))
        ->getJson(route('api.attachments.show', $foreignAttachment))
        ->assertNotFound();
});
