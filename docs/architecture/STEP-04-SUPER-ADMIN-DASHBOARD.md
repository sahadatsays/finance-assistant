# Step 4 — Super Admin Dashboard

**Status:** Implemented  
**UI:** Vuexy-inspired admin panel (React + Inertia + Recharts)

---

## Access

| URL | Auth |
|-----|------|
| `/admin` | Session auth + `is_platform_admin` |
| `/api/v1/admin/dashboard` | Sanctum + platform admin |

**Super Admin:** `admin@financeassistant.com` / `password` (after `PlatformSeeder`)

---

## Dashboard Metrics

| Metric | Source |
|--------|--------|
| Total Tenants | `tenants` count |
| Active Tenants | status = `active` |
| Trial Tenants | status = `trial` |
| Revenue (MRR) | Sum of `plans.price_monthly` for active subscriptions |
| Total Users | Users excluding platform admins |
| New Registrations | Users created in last 30 days |

---

## Charts (Recharts)

| Widget | Type | Data |
|--------|------|------|
| Tenant Growth | Area | Tenants created per month (12 mo) |
| User Registrations | Bar | New users per month |
| Revenue (MRR) | Line | Active subscription MRR by month |
| Tenant Statistics | Donut | Count by status |

---

## Admin Modules

| Module | Route | Features |
|--------|-------|----------|
| Dashboard | `/admin` | Metrics + charts |
| Tenant Management | `/admin/tenants` | List, create, suspend, activate |
| Subscription Plans | `/admin/plans` | View tiers, create plans |
| System Settings | `/admin/settings` | App name, support email, trial days, flags |
| Activity Logs | `/admin/activity-logs` | Platform audit trail |
| Website Management | `/admin/website` | CMS: homepage, pages, nav, footer, testimonials, FAQs, plans, blog, SEO, media |

---

## Backend Services

```
app/Services/Platform/
├── AdminDashboardService.php   # Metrics + chart data
├── ActivityLogService.php        # Audit logging
└── PlatformSettingService.php    # Key-value settings
```

---

## Database

| Table | Purpose |
|-------|---------|
| `activity_logs` | Platform audit events |
| `platform_settings` | System configuration |

---

## Activity Logging

Events logged automatically for:
- Tenant created / suspended / activated
- Plan created / updated
- Platform settings updated
