# Attachments API

Base paths: `/api/v1/uploads`, `/api/v1/attachments`, `/api/v1/transactions/{id}/attachments`

Upload and manage transaction receipt files (images and PDFs) with secure, time-limited download URLs.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email for upload and management endpoints. File downloads use signed URLs and do not require a bearer token.

## Storage

Files are stored via the `AttachmentStorage` abstraction (default disk: `local`, configurable via `ATTACHMENT_DISK`). Supported types: PDF, JPG, JPEG, PNG, WEBP (max 5 MB).

## Upload File

```http
POST /api/v1/uploads
Content-Type: multipart/form-data
```

| Field | Type | Description |
|-------|------|-------------|
| `file` | file | PDF or image (max 5 MB) |

Returns a pending `upload` object with `id` (UUID), metadata, and `expires_at` (24 hours by default). Use `upload_id` when attaching to a transaction.

## Attach to Transaction

```http
POST /api/v1/transactions/{id}/attachments
Content-Type: multipart/form-data
```

Provide either:

| Field | Type | Description |
|-------|------|-------------|
| `file` | file | Upload and attach in one step |
| `upload_id` | uuid | Attach a previously uploaded pending file |

## Show Attachment

```http
GET /api/v1/attachments/{id}
```

Returns attachment metadata plus a temporary signed `url` and `url_expires_at` (default 30 minutes).

## Download File

```http
GET /api/v1/attachments/{id}/file?signature=...&expires=...
```

Signed URL returned from the show endpoint. Streams the file without requiring authentication.

## Delete Attachment

```http
DELETE /api/v1/attachments/{id}
```

Removes the database record and deletes the stored file.
