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

## Authentication Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/v1/auth/register` | No | Register and receive token |
| POST | `/api/v1/auth/login` | No | Login and receive token |
| POST | `/api/v1/auth/logout` | Bearer | Revoke current token |
| POST | `/api/v1/auth/forgot-password` | No | Send password reset link |
| POST | `/api/v1/auth/reset-password` | No | Reset password with token |
| GET | `/api/v1/auth/profile` | Bearer | Get authenticated user profile |
| PUT | `/api/v1/auth/profile` | Bearer | Update profile |

See [authentication.md](./v1/authentication.md) for request/response details.

## Dashboard

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/dashboard` | Bearer + verified | Finance metrics and charts |

See [dashboard.md](./v1/dashboard.md).

Finance feature endpoints will be documented here as they are exposed.
