# Rate Limiting

## General API Limit

All `/api/*` routes are throttled via the `api` limiter:

| Caller | Default limit | Key |
|--------|---------------|-----|
| Guest | 60/min | IP address |
| Authenticated | 120/min | User ID |

Configure via environment:

```
API_RATE_LIMIT_AUTHENTICATED=120
API_RATE_LIMIT_GUEST=60
```

## Auth Endpoints

Stricter limits apply to authentication routes (`api-auth`):

| Routes | Limit | Key |
|--------|-------|-----|
| login, register, forgot/reset password | 10/min | email + IP |

```
API_RATE_LIMIT_AUTH=10
```

## Response Headers

Rate-limited responses include standard Laravel headers:

- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After` (when limit exceeded)
