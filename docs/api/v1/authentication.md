# Authentication

The API uses [Laravel Sanctum](https://laravel.com/docs/sanctum) token-based authentication.

## Obtain a Token

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "owner@acme.com",
  "password": "password",
  "device_name": "My App"
}
```

The login response currently returns the legacy shape (`user`, `token`). Future endpoints will migrate to the standard envelope.

## Authenticated Requests

```http
GET /api/v1/profile
Authorization: Bearer {token}
Accept: application/json
```

## Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Revokes the current access token.

## Auth Rate Limiting

Login, register, and password reset routes use a stricter `api-auth` limiter (10 requests/minute per email + IP). See [rate-limiting.md](./rate-limiting.md).

## Email Verification

Routes under `auth:sanctum` + `verified` middleware require a verified email address. Unverified users receive a `403` with the standard error envelope.
