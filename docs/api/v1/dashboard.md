# Dashboard API

Base path: `/api/v1/dashboard`

Returns finance dashboard metrics and charts for the authenticated user's active workspace.

## Authentication

```
Authorization: Bearer {token}
Accept: application/json
```

Requires `auth:sanctum` and verified email.

## Workspace Selection

Mobile clients can pass the active tenant via:

| Method | Value |
|--------|-------|
| Header | `X-Tenant-Id: {tenant_id}` |
| Query | `?tenant_id={tenant_id}` |

If omitted, the API uses the user's first accessible workspace.

## Request

```http
GET /api/v1/dashboard
Authorization: Bearer {token}
X-Tenant-Id: 1
```

## Response `200`

```json
{
  "success": true,
  "message": "Dashboard retrieved successfully.",
  "data": {
    "tenant": {
      "id": 1,
      "name": "Acme Corporation",
      "slug": "acme-corp",
      "currency": "USD"
    },
    "metrics": {
      "total_income": 6400,
      "total_expense": 2180.5,
      "total_savings": 8500,
      "net_worth": 12450,
      "budget_status": {
        "spent": 2180.5,
        "budgeted": 3500,
        "percentage": 62.3,
        "status": "on_track"
      },
      "savings_goal_progress": {
        "summary": {
          "total_saved": 3200,
          "total_target": 10000,
          "percentage": 32,
          "active_count": 2,
          "completed_count": 0
        },
        "goals": [
          {
            "id": 1,
            "name": "Emergency Fund",
            "current_amount": 2500,
            "target_amount": 5000,
            "percentage": 50,
            "color": "#10b981",
            "target_date": "2026-12-31",
            "status": "on_track"
          }
        ]
      }
    },
    "charts": {
      "income_vs_expense": [
        { "month": "2026-01", "month_label": "Jan 2026", "income": 6400, "expense": 2180.5 }
      ],
      "monthly_trend": [
        { "month": "2026-01", "month_label": "Jan 2026", "net": 4219.5 }
      ],
      "category_breakdown": [
        { "category": "Groceries", "amount": 450, "color": "#f59e0b" }
      ]
    }
  },
  "meta": {
    "period": "2026-06",
    "cache_enabled": true,
    "cache_ttl": 300
  }
}
```

## Metrics

| Field | Description |
|-------|-------------|
| `total_income` | Income transactions for the current month |
| `total_expense` | Expense transactions for the current month |
| `total_savings` | Savings account balances + goal contributions |
| `net_worth` | Sum of all active account balances |
| `budget_status` | Current monthly budget utilization |
| `savings_goal_progress` | Summary and per-goal progress |

## Charts

| Chart | Description |
|-------|-------------|
| `income_vs_expense` | Last 6 months income and expense bars |
| `monthly_trend` | Last 6 months net income line |
| `category_breakdown` | Current month expenses by category |

## Caching

Dashboard responses are cached per tenant and month.

```
API_DASHBOARD_CACHE_ENABLED=true
API_DASHBOARD_CACHE_TTL=300
```

Cache key format: `api.dashboard.{tenant_id}.{Y-m}`

Use `TenantDashboardService::forgetApiCache($tenant)` to invalidate after data mutations.

## Errors

| Status | Message |
|--------|---------|
| 401 | Unauthenticated |
| 403 | No workspace / suspended tenant / not a member |
