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

## Swagger / OpenAPI (local & development)

| Documentation | Swagger UI |
|---------------|------------|
| Public APIs | `/api/documentation/public` |
| Authenticated APIs | `/api/documentation` |
| Admin APIs | `/api/documentation/admin` |

Generate specs: `composer docs:swagger`. See [swagger/README.md](./swagger/README.md).

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

## Categories

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/categories` | Bearer + verified | List categories (filter + paginate) |
| GET | `/api/v1/categories/{id}` | Bearer + verified | Show category |
| POST | `/api/v1/categories` | Bearer + verified (owner) | Create custom category |
| PUT | `/api/v1/categories/{id}` | Bearer + verified (owner) | Update category |
| DELETE | `/api/v1/categories/{id}` | Bearer + verified (owner) | Delete custom category |

See [categories.md](./v1/categories.md).

## Transactions

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/transactions` | Bearer + verified | List transactions (filter, sort, paginate) |
| GET | `/api/v1/transactions/{id}` | Bearer + verified | Show transaction |
| POST | `/api/v1/transactions` | Bearer + verified | Create transaction |
| PUT | `/api/v1/transactions/{id}` | Bearer + verified | Update transaction |
| DELETE | `/api/v1/transactions/{id}` | Bearer + verified | Delete transaction |

See [transactions.md](./v1/transactions.md).

## Budgets

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/budgets` | Bearer + verified | List budgets (filter, paginate) |
| GET | `/api/v1/budgets/{id}` | Bearer + verified | Show budget |
| GET | `/api/v1/budgets/{id}/analysis` | Bearer + verified | Allocated, spent, remaining, percentage |
| POST | `/api/v1/budgets` | Bearer + verified (owner) | Create budget |
| PUT | `/api/v1/budgets/{id}` | Bearer + verified (owner) | Update budget |
| DELETE | `/api/v1/budgets/{id}` | Bearer + verified (owner) | Delete budget |

See [budgets.md](./v1/budgets.md).

## Savings Goals

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/goals` | Bearer + verified | List goals (filter, paginate) |
| GET | `/api/v1/goals/{id}` | Bearer + verified | Show goal with progress and forecast |
| POST | `/api/v1/goals` | Bearer + verified (owner) | Create goal |
| PUT | `/api/v1/goals/{id}` | Bearer + verified (owner) | Update goal |
| DELETE | `/api/v1/goals/{id}` | Bearer + verified (owner) | Delete goal |
| POST | `/api/v1/goals/{id}/contribute` | Bearer + verified | Add contribution |

See [goals.md](./v1/goals.md).

## Attachments

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/v1/uploads` | Bearer + verified | Upload file (pending) |
| POST | `/api/v1/transactions/{id}/attachments` | Bearer + verified | Attach file or pending upload |
| GET | `/api/v1/attachments/{id}` | Bearer + verified | Show attachment with secure URL |
| GET | `/api/v1/attachments/{id}/file` | Signed URL | Download file |
| DELETE | `/api/v1/attachments/{id}` | Bearer + verified | Delete attachment |

See [attachments.md](./v1/attachments.md).

## Reports

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/reports/summary` | Bearer + verified | Income, expense, net, net worth |
| GET | `/api/v1/reports/monthly` | Bearer + verified | Monthly income and expense trend |
| GET | `/api/v1/reports/category` | Bearer + verified | Expense breakdown by category |
| GET | `/api/v1/reports/cashflow` | Bearer + verified | Monthly cashflow |
| GET | `/api/v1/reports/net-worth` | Bearer + verified | Net worth and history |
| POST | `/api/v1/reports/export` | Bearer + verified | Export JSON, CSV, or PDF |

See [reports.md](./v1/reports.md).

## Bills

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/bills` | Bearer + verified | List bills |
| GET | `/api/v1/bills/upcoming` | Bearer + verified | Upcoming bills |
| POST | `/api/v1/bills` | Bearer + verified | Create bill |
| PUT | `/api/v1/bills/{id}` | Bearer + verified | Update bill |
| DELETE | `/api/v1/bills/{id}` | Bearer + verified | Delete bill |
| POST | `/api/v1/bills/{id}/mark-paid` | Bearer + verified | Mark bill paid |

## Accounts & Net Worth

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/accounts` | Bearer + verified | List accounts |
| POST | `/api/v1/accounts` | Bearer + verified (owner) | Create account |
| PUT | `/api/v1/accounts/{id}` | Bearer + verified (owner) | Update account |
| DELETE | `/api/v1/accounts/{id}` | Bearer + verified (owner) | Delete account |
| GET | `/api/v1/net-worth` | Bearer + verified | Current net worth |
| GET | `/api/v1/net-worth/history` | Bearer + verified | Net worth history |

## Investments

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/investments` | Bearer + verified | List investments |
| POST | `/api/v1/investments` | Bearer + verified | Create investment |
| PUT | `/api/v1/investments/{id}` | Bearer + verified | Update investment |
| DELETE | `/api/v1/investments/{id}` | Bearer + verified | Delete investment |
| GET | `/api/v1/portfolio/performance` | Bearer + verified | Portfolio performance |
| GET | `/api/v1/portfolio/allocation` | Bearer + verified | Portfolio allocation |

## Notifications

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/notifications` | Bearer + verified | List notifications |
| POST | `/api/v1/notifications/read` | Bearer + verified | Mark notifications read |
| POST | `/api/v1/device-token` | Bearer + verified | Register push token |
| DELETE | `/api/v1/device-token` | Bearer + verified | Remove push token |

## Mobile Sync (Flutter)

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/sync/transactions?since=` | Bearer + verified | Delta sync transactions |
| GET | `/api/v1/sync/budgets?since=` | Bearer + verified | Delta sync budgets |
| GET | `/api/v1/sync/goals?since=` | Bearer + verified | Delta sync goals |
| GET | `/api/v1/sync/dashboard?since=` | Bearer + verified | Dashboard snapshot |
| GET | `/api/v1/sync/notifications?since=` | Bearer + verified | Delta sync notifications |

All sync responses include `items`, `deleted_ids`, `synced_at`, and `meta.server_time` for offline/mobile clients.
