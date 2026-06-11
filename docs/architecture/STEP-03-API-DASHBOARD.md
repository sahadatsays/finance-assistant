# STEP 03 — API Dashboard

Expose tenant finance dashboard data for mobile clients.

## Endpoint

| Method | Path | Route name |
|--------|------|------------|
| GET | `/api/v1/dashboard` | `api.dashboard.show` |

## Architecture

```
GET /api/v1/dashboard
        │
        ▼
DashboardController (Api\V1)
        │
        ├─ TenantContextService (X-Tenant-Id / tenant_id / session)
        ├─ TenantDashboardService::forApi() [cached]
        └─ DashboardResource tree
```

## Resources

| Resource | Purpose |
|----------|---------|
| `DashboardResource` | Root payload wrapper |
| `TenantSummaryResource` | Workspace id, name, slug, currency |
| `DashboardMetricsResource` | Income, expense, savings, net worth |
| `BudgetStatusResource` | Budget utilization |
| `SavingsGoalProgressResource` | Goal summary + list |
| `SavingsGoalResource` | Individual goal progress |
| `DashboardChartsResource` | Charts with `month_label` for mobile |

## Query Optimizations

- Monthly income/expense combined into one grouped query
- Account balances grouped by type in one query
- Chart monthly totals use a single combined query (replaces two per chart)

## Caching

Configured in `config/api.php` under `dashboard`:

- `cache_enabled` — toggle caching
- `cache_ttl` — seconds (default 300)
- Key: `api.dashboard.{tenant_id}.{Y-m}`

## Tenant Context for API

`TenantContextService` resolves workspace from:

1. `X-Tenant-Id` header
2. `tenant_id` query parameter
3. Session `current_tenant_id` (web)
4. First accessible tenant (fallback)

## Files

```
app/Http/Controllers/Api/V1/DashboardController.php
app/Http/Resources/Api/Dashboard/*
app/Services/Finance/TenantDashboardService.php  (forApi, forgetApiCache)
routes/api/v1/dashboard.php
tests/Feature/Api/DashboardApiTest.php
docs/api/v1/dashboard.md
```
