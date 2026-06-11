# Transactions API

Base path: `/api/v1/transactions`

Manage income, expense, and transfer transactions for the active workspace. Supports tags, notes, and file attachments.

## Authentication

```
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}   (optional)
```

Requires `auth:sanctum` and verified email. All tenant members can view, create, update, and delete transactions.

## List Transactions

```http
GET /api/v1/transactions
```

### Query parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Match notes, amount, category, or tag name |
| `type` | string | `income`, `expense`, or `transfer` |
| `category_id` | integer | Filter by category |
| `account_id` | integer | Filter by source or destination account |
| `tag_id` | integer | Filter by tag |
| `date_from` | date | Start of date range (`Y-m-d`) |
| `date_to` | date | End of date range (`Y-m-d`) |
| `amount_min` | number | Minimum amount |
| `amount_max` | number | Maximum amount |
| `sort` | string | `occurred_at`, `amount`, `created_at`, `type` (default: `occurred_at`) |
| `direction` | string | `asc` or `desc` (default: `desc`) |
| `page` | integer | Page number |
| `per_page` | integer | Items per page (1–100, default 15) |

### Response `200`

```json
{
  "success": true,
  "message": "Transactions retrieved successfully.",
  "data": [
    {
      "id": 1,
      "type": "expense",
      "amount": 85.5,
      "notes": "Weekly groceries",
      "occurred_at": "2026-06-01T00:00:00+00:00",
      "account": { "id": 1, "name": "Main Checking" },
      "transfer_account": null,
      "category": { "id": 5, "name": "Groceries", "color": "#f59e0b" },
      "tags": [{ "id": 1, "name": "food" }],
      "attachments": []
    }
  ],
  "meta": {
    "pagination": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 },
    "filters": { "sort": "occurred_at", "direction": "desc" }
  }
}
```

## Show Transaction

```http
GET /api/v1/transactions/{id}
```

## Create Transaction

```http
POST /api/v1/transactions
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `type` | string | Yes (`income` / `expense` / `transfer`) |
| `account_id` | integer | Yes |
| `transfer_account_id` | integer | Required for transfers |
| `category_id` | integer | Required for income/expense |
| `amount` | number | Yes (min 0.01) |
| `occurred_at` | date | Yes |
| `notes` | string | No |
| `tags` | string[] | No (tag names; created if missing) |
| `attachment` | file | No (multipart: pdf, jpg, png, webp; max 5MB) |

**Response `201`**

For file uploads, send `multipart/form-data` instead of JSON.

## Update Transaction

```http
PUT /api/v1/transactions/{id}
```

Same fields as create (all optional except those required by type when provided). Passing `tags` replaces existing tags.

## Delete Transaction

```http
DELETE /api/v1/transactions/{id}
```

Reverses account balance effects and removes attachments.

## Transaction types

| Type | Category required | Notes |
|------|-------------------|-------|
| `income` | Yes (income category) | Credits account |
| `expense` | Yes (expense category) | Debits account |
| `transfer` | No | Requires `transfer_account_id` |
