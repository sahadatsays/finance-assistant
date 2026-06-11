# Swagger UI Authentication (Laravel Sanctum)

This guide explains how to authenticate API requests inside Swagger UI using Laravel Sanctum bearer tokens.

## OpenAPI Security Scheme

All authenticated and admin OpenAPI documents define:

```yaml
components:
  securitySchemes:
    sanctum:
      type: http
      scheme: bearer
      bearerFormat: Sanctum
    tenant:
      type: apiKey
      in: header
      name: X-Tenant-Id
```

Protected documentation sets apply global security:

```yaml
security:
  - sanctum: []
```

Public endpoints explicitly set `security: []` so they do not show a lock icon.

## Sanctum Configuration

| Setting | Value |
|---------|-------|
| Token type | Personal access token |
| Header | `Authorization: Bearer {token}` |
| Login | `POST /api/v1/auth/login` |
| Logout | `POST /api/v1/auth/logout` |
| Profile | `GET /api/v1/auth/profile` |
| Demo user | `owner@acme.com` / `password` |

Configuration lives in `config/sanctum.php` (token expiration, guards) and `config/swagger.php` (`sanctum` section for OpenAPI metadata).

## Login Example

**Request** — Public docs → `POST /auth/login`

```json
{
  "email": "owner@acme.com",
  "password": "password",
  "device_name": "swagger-ui"
}
```

**Response**

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": { "id": 1, "name": "...", "email": "owner@acme.com" },
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  },
  "meta": {}
}
```

Copy the `data.token` value.

## Logout Example

**Request** — Authenticated docs → `POST /auth/logout` (with bearer token)

**Response**

```json
{
  "success": true,
  "message": "Logged out successfully.",
  "data": {},
  "meta": {}
}
```

The current token is revoked and cannot be reused.

## Protected Endpoint Example

**Request** — Authenticated docs → `GET /auth/profile`

```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz
```

**Response**

```json
{
  "success": true,
  "message": "Profile retrieved successfully.",
  "data": {
    "user": { "id": 1, "name": "...", "email": "owner@acme.com" }
  },
  "meta": {}
}
```

Tenant-scoped routes (e.g. `GET /categories`) also accept optional `X-Tenant-Id`.

## Testing Workflow Inside Swagger UI

### Step 1 — Obtain a token

1. Open **Public** documentation: `/api/documentation/public`
2. Expand **Authentication** → `POST /auth/login`
3. Click **Try it out**
4. Use demo credentials or your own user
5. Click **Execute**
6. Copy `data.token` from the response body

### Step 2 — Authorize

1. Open **Authenticated** documentation: `/api/documentation`
2. Click the **Authorize** button (lock icon, top right)
3. In the `sanctum` field, paste the token **without** the `Bearer` prefix
4. Click **Authorize**, then **Close**

Authorization persists across page reloads when `L5_SWAGGER_UI_PERSIST_AUTHORIZATION=true` (default).

### Step 3 — Test protected endpoints

1. Expand any endpoint with a lock icon (e.g. `GET /auth/profile`, `GET /categories`)
2. Click **Try it out** → **Execute**
3. Swagger UI sends `Authorization: Bearer {token}` automatically

### Step 4 — Logout (optional)

1. Execute `POST /auth/logout`
2. Click **Authorize** → **Logout** to clear the stored token

## Documentation URLs

| Set | URL | Auth required in UI |
|-----|-----|---------------------|
| Public | `/api/documentation/public` | No (login here) |
| Authenticated | `/api/documentation` | Yes (global Sanctum) |
| Admin | `/api/documentation/admin` | Yes (global Sanctum) |

## Regenerate specs

```bash
composer docs:swagger
```
