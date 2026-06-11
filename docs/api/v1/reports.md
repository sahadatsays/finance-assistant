# Reports API

Base path: `/api/v1/reports`

Financial reporting and export endpoints for summary metrics, trends, and breakdowns.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email. All tenant members can view and export reports.

## Summary

```http
GET /api/v1/reports/summary?from=2026-06-01&to=2026-06-30
```

Returns income, expense, net, savings, net worth, and budget status for the period.

## Monthly

```http
GET /api/v1/reports/monthly?months=6
```

Returns per-month income, expense, and net values.

## Category

```http
GET /api/v1/reports/category?from=2026-06-01&to=2026-06-30
```

Returns expense breakdown by category with amounts and percentages.

## Cashflow

```http
GET /api/v1/reports/cashflow?months=6
```

Returns inflow, outflow, and net cashflow per month.

## Net Worth

```http
GET /api/v1/reports/net-worth?months=6
```

Returns current net worth, account balances, and historical net worth trend.

## Export

```http
POST /api/v1/reports/export
```

```json
{
  "report": "summary",
  "format": "json",
  "from": "2026-01-01",
  "to": "2026-06-30",
  "months": 6
}
```

| Field | Values |
|-------|--------|
| `report` | `summary`, `monthly`, `category`, `cashflow`, `net-worth` |
| `format` | `json`, `csv`, `pdf` |

Returns a downloadable file stream.
