# Step 5 — Tenant Dashboard

**Status:** Implemented  
**UI:** Vuexy-inspired finance dashboard (React + Inertia + Recharts)

---

## Access

| URL | Auth |
|-----|------|
| `/dashboard` | Session auth + tenant membership |
| `POST /tenant/switch/{tenant}` | Switch active workspace |

---

## Dashboard Cards

| Card | Source |
|------|--------|
| Total Income | Sum of income transactions (current month) |
| Total Expense | Sum of expense transactions (current month) |
| Total Savings | Savings account balances + goal progress |
| Budget Status | Spent vs budgeted % (current period) |
| Net Worth | Sum of all active account balances |

---

## Charts

| Widget | Type | Data |
|--------|------|------|
| Income vs Expense | Bar | Last 6 months |
| Category Breakdown | Donut | Expenses by category (this month) |
| Monthly Trend | Line | Net income per month |

---

## Widgets

| Widget | Data |
|--------|------|
| Recent Transactions | Last 8 transactions |
| Budget Alerts | Budgets at ≥80% or over limit |
| Savings Goals | Active goals with progress bars |

---

## Finance Tables

| Table | Purpose |
|-------|---------|
| `accounts` | Bank/cash/savings accounts |
| `categories` | Income/expense categories |
| `transactions` | Financial entries |
| `budgets` | Budget periods |
| `budget_lines` | Per-category allocations |
| `goals` | Savings goals |

All tables include `tenant_id` for multi-tenant isolation.

---

## Tenant Context

- Session key: `current_tenant_id`
- Defaults to user's first accessible tenant
- `TenantContextService` resolves workspace for web routes
- Suspended tenants excluded for non-admin users

---

## Demo Data

`FinanceDemoSeeder` seeds Acme Corporation with accounts, transactions, budgets, and goals.

**Login:** `owner@acme.com` / `password`
