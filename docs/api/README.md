# Finance Assistant API Documentation

REST API documentation for the Finance Assistant platform.

## Base URL

```
{APP_URL}/api/v1
```

## Versions

| Version | Status | Documentation |
|---------|--------|---------------|
| v1 | Stable | [v1 docs](./v1/README.md) |

## Quick Links

- [Overview & response format](./v1/README.md)
- [Authentication](./v1/authentication.md)
- [Error handling](./v1/errors.md)
- [Rate limiting](./v1/rate-limiting.md)
- [OpenAPI skeleton](./v1/openapi.yaml)

## Foundation Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1` | No | API version and metadata |
| GET | `/api/v1/health` | No | Health check |

Feature endpoints (finance, categories, transactions, etc.) will be documented here as they are exposed.
