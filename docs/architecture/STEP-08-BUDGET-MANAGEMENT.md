# Step 8 — Budget Management

**Status:** Implemented

---

## Budget Types

| Type | Period | Description |
|------|--------|-------------|
| **Monthly** | Calendar month | Full-month spending plan |
| **Weekly** | Calendar week | Short-term spending plan |
| **Category** | Per budget line | Allocated amount per expense category |

Each budget has one or more **category lines** (`budget_lines`) that define per-category limits.

---

## Dashboard & Analytics

| Widget | Source |
|--------|--------|
| Monthly Utilization | Active monthly budget spent vs total |
| Weekly Utilization | Active weekly budget spent vs total |
| Budget Utilization Trend | Last 6 monthly budgets (Recharts bar chart) |
| Category Progress | Spent vs budgeted per category (horizontal bar chart) |
| Overspending Alerts | Overall and per-category warnings (≥80%) and over-budget (≥100%) |

**Status thresholds:** `on_track` (<80%), `warning` (80–99%), `over_budget` (≥100%)

---

## Routes

| Method | Route | Action |
|--------|-------|--------|
| GET | `/budgets` | Dashboard + list |
| POST | `/budgets` | Create |
| PUT | `/budgets/{id}` | Update |
| DELETE | `/budgets/{id}` | Delete |
| GET | `/budgets/export` | CSV report |

---

## Permissions

`BudgetPolicy` — tenant owners manage (create/update/delete); all members can view and export.

---

## Services

- `BudgetService` — CRUD, period resolution, category line sync
- `BudgetAnalyticsService` — utilization, category progress, alerts, trend, report
- `BudgetExportService` — CSV export of budget report
- `TenantDashboardService` — reuses `BudgetAnalyticsService` for dashboard budget metrics

---

## Tables

| Table | Purpose |
|-------|---------|
| `budgets` | Budget header (period, amount, type) |
| `budget_lines` | Per-category allocations |

**Login:** `owner@acme.com` / `password` → `/budgets`
