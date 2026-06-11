# Finance Assistant — Architecture Documentation

Enterprise architecture for a **multi-tenant SaaS personal finance platform**.

| Document | Description |
|----------|-------------|
| [STEP-01-WEB-FOUNDATION.md](./STEP-01-WEB-FOUNDATION.md) | Step 1: Folder, SaaS, roles, database, standards, modules, routes, services, repositories, activity log |
| [STEP-03-TENANT-MANAGEMENT.md](./STEP-03-TENANT-MANAGEMENT.md) | Step 3: Tenant CRUD, status, subscriptions, users, Super Admin API |
| [STEP-04-SUPER-ADMIN-DASHBOARD.md](./STEP-04-SUPER-ADMIN-DASHBOARD.md) | Step 4: Super Admin dashboard, charts, plans, settings, activity logs |
| [STEP-05-TENANT-DASHBOARD.md](./STEP-05-TENANT-DASHBOARD.md) | Step 5: Tenant finance dashboard, metrics, charts, widgets |
| [STEP-06-CATEGORIES-MODULE.md](./STEP-06-CATEGORIES-MODULE.md) | Step 6: Category management, system/custom categories, CRUD |
| [STEP-07-TRANSACTIONS-MODULE.md](./STEP-07-TRANSACTIONS-MODULE.md) | Step 7: Transactions, transfers, tags, attachments, export |

## Technology Stack (Target)

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.4+ |
| Database | MySQL 8 |
| Cache / Queue | Redis |
| Auth | Laravel Fortify (web) + Sanctum (API) |
| Frontend | Vue 3 + Vuexy Admin Template + Tailwind CSS |
| Background | Laravel Queue + Scheduler |

## Current vs Target State

| Area | Current (starter kit) | Target (Step 1 foundation) |
|------|----------------------|----------------------------|
| Frontend | Inertia + React 19 | Vue 3 + Vuexy (API-driven SPA) |
| Tenancy | None | Single-DB, row-level `tenant_id` |
| Roles | Single user type | Super Admin, Tenant Owner, Tenant User |
| Database | SQLite (dev) | MySQL 8 (all environments) |
| Cache/Queue | Database drivers | Redis |
| Module structure | Flat `app/` | Domain modules under `app/Modules/` |

## Implementation Order (Post-Architecture)

1. **Infrastructure** — MySQL, Redis, env, Horizon
2. **Tenancy core** — Tenant model, middleware, global scopes
3. **RBAC** — Roles, permissions, policies
4. **Activity log** — Audit trail package + observers
5. **Module scaffold** — First domain module (e.g. `Finance`)
6. **Frontend migration** — Vuexy shell + Sanctum client
