# Authentication API

Base path: `/api/v1/auth`

All responses use the [standard envelope](./README.md). Sanctum bearer tokens authenticate protected routes.

## Register

```http
POST /api/v1/auth/register
Content-Type: application/json
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Display name |
| `email` | string | Yes | Unique email |
| `password` | string | Yes | Min 8 characters |
| `password_confirmation` | string | Yes | Must match password |
| `device_name` | string | No | Mobile device label for tracking |

**Response `201`**

```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "data": {
    "user": {
      "id": 1,
      "name": "API User",
      "email": "api@example.com",
      "email_verified": false,
      "email_verified_at": null,
      "profile": { "avatar_url": null, "phone": null, "timezone": null, "locale": null, "bio": null },
      "created_at": "...",
      "updated_at": "..."
    },
    "token": "1|...",
    "token_type": "Bearer"
  },
  "meta": {}
}
```

Creates a user profile, issues a Sanctum token, tracks the device, records login history, and sends a verification email.

## Login

```http
POST /api/v1/auth/login
Content-Type: application/json
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | Account email |
| `password` | string | Yes | Account password |
| `device_name` | string | No | Device label |
| `device_id` | string | No | Client device identifier |

**Response `200`** — Same `data` shape as register (`user`, `token`, `token_type`).

Failed credentials return `422` with `data.errors.email`.

## Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Revokes the current token and removes the associated device record.

**Response `200`**

```json
{
  "success": true,
  "message": "Logged out successfully.",
  "data": {},
  "meta": {}
}
```

## Forgot Password

```http
POST /api/v1/auth/forgot-password
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `email` | string | Yes |

**Response `200`** — `success: true` with status message. Sends reset link via email.

## Reset Password

```http
POST /api/v1/auth/reset-password
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `token` | string | Yes |
| `email` | string | Yes |
| `password` | string | Yes |
| `password_confirmation` | string | Yes |

**Response `200`** — `success: true` on success. Invalid token returns `422`.

## Profile

### Get Profile

```http
GET /api/v1/auth/profile
Authorization: Bearer {token}
```

**Response `200`**

```json
{
  "success": true,
  "message": "Profile retrieved successfully.",
  "data": {
    "user": { "...": "..." }
  },
  "meta": {}
}
```

### Update Profile

```http
PUT /api/v1/auth/profile
Authorization: Bearer {token}
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `name` | string | No |
| `email` | string | No |
| `avatar_url` | string (url) | No |
| `phone` | string | No |
| `timezone` | string | No |
| `locale` | string | No |
| `bio` | string | No |

Changing `email` resets `email_verified` to `false`.

## Email Verification

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/auth/email/status` | Check verification status |
| POST | `/api/v1/auth/email/resend` | Resend verification email |
| GET | `/api/v1/auth/email/verify/{id}/{hash}` | Verify email (signed URL) |

Profile and verification routes require `auth:sanctum` only (no verified middleware), so newly registered mobile users can update their profile before verifying.

## Related Endpoints

These require a verified email (`auth:sanctum` + `verified`):

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/devices` | List tracked devices |
| GET | `/api/v1/login-history` | Login history |
| GET | `/api/v1/sessions` | Active sessions |

## Rate Limiting

Auth-sensitive routes use the `api-auth` limiter (10/min per email + IP). See [rate-limiting.md](./rate-limiting.md).
