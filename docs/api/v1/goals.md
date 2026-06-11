# Savings Goals API

Base path: `/api/v1/goals`

Manage savings goals with progress tracking, forecasts, and contributions.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email. All members can view goals and add contributions. Tenant owners can create, update, and delete.

## List Goals

```http
GET /api/v1/goals
```

### Query parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | Goal type (`emergency_fund`, `travel`, `education`, `purchase`, `custom`) |
| `search` | string | Filter by goal name |
| `sort` | string | `target_date`, `target_amount`, `current_amount`, `name`, `created_at` |
| `direction` | string | `asc` or `desc` |
| `page` | integer | Page number |
| `per_page` | integer | Items per page (1–100, default 15) |

Each goal includes `progress`, `forecast`, `contributions`, and `contribution_trend`.

### Progress fields

| Field | Description |
|-------|-------------|
| `current` | Amount saved so far |
| `target` | Target amount |
| `remaining` | Amount left to reach target |
| `percentage` | Completion percentage (0–100) |
| `status` | `on_track`, `warning`, `behind`, or `completed` |

### Forecast fields

| Field | Description |
|-------|-------------|
| `remaining` | Amount still needed |
| `days_remaining` | Days until target date (if set) |
| `required_monthly` | Monthly savings needed to meet target date |
| `projected_completion` | Estimated completion date based on contribution trend |
| `is_behind` | Whether the goal is behind schedule |

## Show Goal

```http
GET /api/v1/goals/{id}
```

## Create Goal

```http
POST /api/v1/goals
```

Tenant owner only.

```json
{
  "name": "Vacation Fund",
  "type": "travel",
  "target_amount": 5000,
  "target_date": "2026-12-01",
  "color": "#3b82f6",
  "initial_contribution": 500
}
```

## Update Goal

```http
PUT /api/v1/goals/{id}
```

Tenant owner only.

## Delete Goal

```http
DELETE /api/v1/goals/{id}
```

Tenant owner only. Soft-deletes by deactivating the goal.

## Add Contribution

```http
POST /api/v1/goals/{id}/contribute
```

All tenant members can contribute.

```json
{
  "amount": 250,
  "notes": "Monthly savings",
  "contributed_at": "2026-06-11"
}
```

### Response `201`

Returns the new contribution and updated goal with recalculated `progress` and `forecast`.
