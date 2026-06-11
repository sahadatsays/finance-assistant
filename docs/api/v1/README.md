# API v1 Overview

## Response Envelope

All API responses use a consistent JSON envelope:

```json
{
  "success": true,
  "message": "",
  "data": {},
  "meta": {}
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | `true` for successful requests, `false` for errors |
| `message` | string | Human-readable summary |
| `data` | object/array | Payload (empty object `{}` when none) |
| `meta` | object | Pagination, filters, or auxiliary metadata |

### Success Example

```json
{
  "success": true,
  "message": "API is healthy.",
  "data": {
    "status": "ok",
    "timestamp": "2026-06-11T12:00:00+00:00"
  },
  "meta": {}
}
```

### Paginated Example

```json
{
  "success": true,
  "message": "",
  "data": [],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 72,
      "from": 1,
      "to": 15
    }
  }
}
```

## Headers

| Header | Description |
|--------|-------------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` for request bodies |
| `Authorization` | `Bearer {token}` for authenticated routes |

## API Resources

Domain data is transformed through Laravel API Resources (`app/Http/Resources/` and module resources). Controllers return resources via `ApiController::success()` which resolves them into the `data` field.

## Request Validation

API form requests extend `App\Http\Requests\Api\ApiFormRequest`, which returns validation errors in the standard envelope. See [errors.md](./errors.md).

## Logging

API requests are logged to `storage/logs/api.log` (configurable via `API_LOG_CHANNEL`). Each entry includes method, path, status, duration, user ID, and IP.
