# Budgets API

Base path: `/api/v1/budgets`

Manage monthly and weekly expense budgets with per-category allocations.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email. All members can view budgets and analysis. Tenant owners can create, update, and delete.

## List Budgets

```http
GET /api/v1/budgets
```

### Query parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `period_type` | string | `monthly` or `weekly` |
| `sort` | string | `period_start`, `period_end`, `amount`, `name`, `created_at` |
| `direction` | string | `asc` or `desc` |
| `page` | integer | Page number |
| `per_page` | integer | Items per page (1–100, default 15) |

Each budget includes `utilization`, `categories`, and `lines` in the response.

## Show Budget

```http
GET /api/v1/budgets/{id}
```

## Budget Analysis

```http
GET /api/v1/budgets/{id}/analysis
```

### Response `200`

```json
{
  "success": true,
  "data": {
    "analysis": {
      "budget": {
        "id": 1,
        "name": "Monthly Budget",
        "period_type": "monthly",
        "period_start": "2026-06-01",
        "period_end": "2026-06-30"
      },
      "allocated": 3500,
      "spent": 2180.5,
      "remaining": 1319.5,
      "percentage": 62.3,
      "status": "on_track",
      "categories": [
        {
          "category_id": 5,
          "category": "Groceries",
          "color": "#f59e0b",
          "allocated": 600,
          "spent": 450,
          "remaining": 150,
          "percentage": 75,
          "status": "warning"
        }
      ]
    }
  }
}
```

| Field | Description |
|-------|-------------|
| `allocated` | Total budget amount |
| `spent` | Expenses in the budget period |
| `remaining` | Allocated minus spent |
| `percentage` | Utilization percentage |
| `status` | `on_track`, `warning`, or `over_budget` |

## Create Budget

```http
POST /api/v1/budgets
```

| Field | Type | Required |
|-------|------|----------|
| `name` | string | Yes |
| `period_type` | string | Yes (`monthly` / `weekly`) |
| `period_start` | date | No (defaults to current period) |
| `amount` | number | No (sum of lines if omitted) |
| `lines` | array | Yes (min 1) |
| `lines.*.category_id` | integer | Yes (expense category) |
| `lines.*.amount` | number | Yes |

**Response `201`**

## Update Budget

```http
PUT /api/v1/budgets/{id}
```

Same fields as create (all optional). Updating `lines` replaces all category allocations.

## Delete Budget

```http
DELETE /api/v1/budgets/{id}
```

Removes the budget and its category lines.
