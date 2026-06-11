# STEP 02 — API Foundation

Enterprise REST API foundation for exposing Finance Assistant business logic to mobile and third-party clients.

## Goals

- Versioned API under `/api/v1`
- Standardized JSON response envelope
- Laravel Sanctum authentication (existing)
- API Resources for data transformation
- Rate limiting and request logging
- Centralized exception handling
- Documentation structure for incremental endpoint rollout

## Architecture

```
Client
  │
  ▼
/api/v1/*  (routes/api.php)
  │
  ├─ throttle:api (ApiServiceProvider)
  ├─ LogApiRequest middleware
  │
  ▼
Controller (extends Api\V1\ApiController)
  │
  ├─ ApiResponse helper (success / error / paginated)
  ├─ Form Request (extends Api\ApiFormRequest)
  └─ JsonResource (existing module resources)
  │
  ▼
Service / Repository (existing business logic)
```

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── ApiController.php      # Base controller
│   │   ├── HealthController.php   # Foundation
│   │   └── VersionController.php    # Foundation
│   ├── Middleware/
│   │   └── LogApiRequest.php
│   └── Requests/Api/
│       └── ApiFormRequest.php       # Validation → standard envelope
├── Providers/
│   └── ApiServiceProvider.php       # Rate limiters
└── Support/Api/
    ├── ApiResponse.php              # Response helpers
    └── ApiExceptionRenderer.php     # Exception → envelope

config/api.php                         # Version, limits, logging

routes/
├── api.php                            # Entry point
└── api/v1/
    └── foundation.php                 # Version + health routes

docs/api/                              # Public API documentation
```

## Response Envelope

```json
{
  "success": true,
  "message": "",
  "data": {},
  "meta": {}
}
```

Implemented in `App\Support\Api\ApiResponse` and consumed via `ApiController` protected methods.

## Route Structure

| File | Prefix | Middleware |
|------|--------|------------|
| `routes/api.php` | `/api` | `throttle:api`, `LogApiRequest` |
| `routes/api/v1/foundation.php` | `/api/v1` | Public |
| Auth routes in `api.php` | `/api/v1/auth` | `throttle:api-auth` on sensitive routes |
| `routes/tenant.php` | `/api/v1/tenants` | `auth:sanctum`, `verified`, tenant middleware |
| `routes/admin.php` | `/api/v1/admin` | `auth:sanctum`, `verified`, `platform-admin` |

Future finance modules will add `routes/api/v1/finance.php` (or per-module route files) required from `api.php`.

## Versioning Strategy

1. **URL versioning** — All routes live under `/api/v1`. Config in `config/api.php` lists supported versions.
2. **Stable v1** — Current production API. Breaking changes require a new version prefix (`/api/v2`).
3. **Foundation first** — New endpoints use `ApiController` + envelope from day one.
4. **Legacy migration** — Existing auth/tenant/admin controllers retain their response shapes until migrated incrementally.

## Exception Handling

`ApiExceptionRenderer` registered in `bootstrap/app.php` maps:

| Exception | Status | Message |
|-----------|--------|---------|
| `ValidationException` | 422 | With `data.errors` |
| `AuthenticationException` | 401 | Unauthenticated |
| `AuthorizationException` | 403 | Forbidden |
| `ModelNotFoundException` | 404 | Resource not found |
| `NotFoundHttpException` | 404 | Endpoint not found |
| `HttpExceptionInterface` | varies | Exception message |
| Other (production) | 500 | Generic message |

## Rate Limiting

| Limiter | Scope | Default |
|---------|-------|---------|
| `api` | All API routes | 60/min guest, 120/min auth |
| `api-auth` | Login, register, password | 10/min per email+IP |

## API Logging

`LogApiRequest` logs to `storage/logs/api.log` with method, path, status, duration, user ID, and IP. Optional request/response body logging via env flags.

## Request Validation

Extend `ApiFormRequest` for new API endpoints. Validation failures return:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": { "errors": { "field": ["..."] } },
  "meta": {}
}
```

Existing `ValidationException` handling covers legacy `FormRequest` classes until migrated.

## Foundation Endpoints (Step 2)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1` | Version metadata |
| GET | `/api/v1/health` | Health check |

No finance feature endpoints in this step.

## Next Steps (Step 3+)

1. Add finance route files under `routes/api/v1/`
2. Migrate auth responses to standard envelope
3. Extend OpenAPI spec per resource
4. Add API feature tests per module
