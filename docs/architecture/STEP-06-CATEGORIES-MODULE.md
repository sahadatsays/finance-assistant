# Step 6 — Categories Module

**Status:** Implemented

---

## Category Types

| Type | Description |
|------|-------------|
| **Income** | Revenue categories (Salary, Freelance, etc.) |
| **Expense** | Spending categories (Groceries, Transport, etc.) |

## Category Kinds

| Kind | Description |
|------|-------------|
| **System** | Seeded defaults per tenant (`is_system = true`) |
| **Custom** | User-created categories |

---

## Fields

| Field | Notes |
|-------|-------|
| `name` | Unique per tenant + type |
| `icon` | Lucide icon name |
| `color` | Hex color `#RRGGBB` |
| `type` | `income` or `expense` |

---

## CRUD

| Action | Route | Permission |
|--------|-------|------------|
| List | `GET /categories` | Tenant member |
| Create | `POST /categories` | Tenant owner |
| Edit | `PUT /categories/{category}` | Tenant owner |
| Delete | `DELETE /categories/{category}` | Tenant owner (custom only, no transactions) |
| Archive | `POST /categories/{category}/archive` | Tenant owner |
| Restore | `POST /categories/{category}/restore` | Tenant owner |

**System category rules:**
- Cannot delete
- Name locked on edit (color/icon only)
- Can archive

---

## Permissions

`CategoryPolicy` + `TenantUserRole::canManageCategories()`:
- **Tenant Owner:** full manage access
- **Tenant User:** view only
- **Platform Admin:** bypass all checks

---

## Activity Logs

All mutations logged to `activity_logs` with `log_name = finance`.

---

## System Categories

Seeded automatically when a tenant is created via `SystemCategoryService`.

**Login:** `owner@acme.com` / `password` → `/categories`
