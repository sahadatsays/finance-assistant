# Step 7 — Transactions Module

**Status:** Implemented

---

## Transaction Types

| Type | Behavior |
|------|----------|
| **Income** | Credits selected account |
| **Expense** | Debits selected account |
| **Transfer** | Debits source, credits destination |

---

## Fields

| Field | Required | Notes |
|-------|----------|-------|
| Category | Income/Expense | Must match transaction type |
| Amount | Yes | > 0 |
| Date | Yes | `occurred_at` |
| Notes | No | Free text |
| Tags | No | Comma-separated, auto-created |
| Attachments | No | PDF, JPG, PNG, WebP (max 5MB) |
| Account | Yes | Source account |
| Transfer Account | Transfer only | Destination account |

---

## Routes

| Method | Route | Action |
|--------|-------|--------|
| GET | `/transactions` | List with search/filter |
| POST | `/transactions` | Create |
| PUT | `/transactions/{id}` | Edit |
| DELETE | `/transactions/{id}` | Delete |
| GET | `/transactions/export` | CSV export |

---

## Search & Filters

- **Search:** notes, amount, category name, tag name
- **Filter:** type, category, account, tag, date range

---

## Permissions

`TransactionPolicy` — all tenant members can view, create, edit, delete, and export.

---

## Business Logic

- `TransactionService` updates account balances on create/update/delete
- Transfers move funds between two accounts atomically
- Tags synced via `TagService`
- Attachments stored in `storage/app/attachments/{tenant_id}/`
- Activity logs recorded for all mutations (`log_name = finance`)

---

## Tables

| Table | Purpose |
|-------|---------|
| `transactions` | Core transaction records |
| `tags` | Tenant-scoped labels |
| `tag_transaction` | Many-to-many pivot |
| `attachments` | File metadata |

**Login:** `owner@acme.com` / `password` → `/transactions`
