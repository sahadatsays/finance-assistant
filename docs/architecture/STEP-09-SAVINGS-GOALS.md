# Step 9 — Savings Goals

**Status:** Implemented

---

## Goal Types

| Type | Value | Default Color |
|------|-------|---------------|
| Emergency Fund | `emergency_fund` | Red |
| Travel | `travel` | Violet |
| Education | `education` | Blue |
| Purchase | `purchase` | Cyan |
| Custom | `custom` | Green |

---

## Features

| Feature | Description |
|---------|-------------|
| **Target Amount** | Total savings target per goal |
| **Contributions** | Manual deposits with amount, notes, and date |
| **Progress Tracking** | Percentage, remaining, status (`on_track`, `behind`, `completed`) |
| **Forecast** | Required monthly, average monthly, projected completion date |

---

## Routes

| Method | Route | Action |
|--------|-------|--------|
| GET | `/goals` | Dashboard + list |
| POST | `/goals` | Create |
| PUT | `/goals/{id}` | Update |
| DELETE | `/goals/{id}` | Delete |
| POST | `/goals/{id}/contributions` | Add contribution |
| DELETE | `/goals/{id}/contributions/{id}` | Remove contribution |
| GET | `/goals/export` | CSV report |

---

## Permissions

`GoalPolicy` — owners manage goals; all members can view, contribute, and export.

---

## Services

- `GoalService` — CRUD, contribution management
- `GoalAnalyticsService` — progress, forecast, dashboard, report
- `GoalExportService` — CSV export
- `TenantDashboardService` — reuses `GoalAnalyticsService` for savings widget

---

## Tables

| Table | Purpose |
|-------|---------|
| `goals` | Goal header (type, target, current, date) |
| `goal_contributions` | Individual contribution records |

**Login:** `owner@acme.com` / `password` → `/goals`
