# Categories API

Base path: `/api/v1/categories`

Manage income and expense categories for the active workspace. Supports system categories (seeded per tenant) and custom categories created by tenant owners.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email. Tenant owners can create, update, and delete custom categories. All members can list and view.

## List Categories

```http
GET /api/v1/categories
```

### Query parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | Filter: `income` or `expense` |
| `kind` | string | Filter: `system` or `custom` |
| `archived` | boolean | `true` for archived only; default active |
| `search` | string | Partial name match |
| `page` | integer | Page number (default 1) |
| `per_page` | integer | Items per page (1–100, default 15) |

### Response `200`

```json
{
  "success": true,
  "message": "Categories retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Salary",
      "type": "income",
      "color": "#10b981",
      "icon": "wallet",
      "kind": "system",
      "is_system": true,
      "is_active": true,
      "archived_at": null,
      "transactions_count": 2
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 16
    },
    "filters": {
      "type": null,
      "kind": null,
      "archived": false,
      "search": null
    }
  }
}
```

## Show Category

```http
GET /api/v1/categories/{id}
```

## Create Category

```http
POST /api/v1/categories
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `name` | string | Yes |
| `type` | string | Yes (`income` / `expense`) |
| `color` | string | Yes (hex `#RRGGBB`) |
| `icon` | string | No (from allowed icon list) |

**Response `201`**

## Update Category

```http
PUT /api/v1/categories/{id}
```

| Field | Type | Required |
|-------|------|----------|
| `name` | string | No |
| `color` | string | No |
| `icon` | string | No |

System categories can be renamed and restyled but not deleted.

## Delete Category

```http
DELETE /api/v1/categories/{id}
```

- System categories: `403 Forbidden`
- Categories with transactions: `422` — archive instead
- Custom categories without transactions: `200`

## Category kinds

| Kind | Description |
|------|-------------|
| `system` | Seeded defaults per tenant |
| `custom` | Created by tenant owner |
