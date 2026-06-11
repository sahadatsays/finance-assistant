# STEP 02b — API Authentication

REST authentication module for mobile and third-party clients.

## Endpoints

| Method | Path | Controller |
|--------|------|------------|
| POST | `/api/v1/auth/register` | `Auth\RegisterController` |
| POST | `/api/v1/auth/login` | `Auth\LoginController` |
| POST | `/api/v1/auth/logout` | `Auth\LogoutController` |
| POST | `/api/v1/auth/forgot-password` | `Auth\ForgotPasswordController` |
| POST | `/api/v1/auth/reset-password` | `Auth\ResetPasswordController` |
| GET | `/api/v1/auth/profile` | `Auth\ProfileController@show` |
| PUT | `/api/v1/auth/profile` | `Auth\ProfileController@update` |

Email verification (bonus):

| GET | `/api/v1/auth/email/status` |
| POST | `/api/v1/auth/email/resend` |
| GET | `/api/v1/auth/email/verify/{id}/{hash}` |

## Architecture

```
routes/api/v1/auth.php
        │
        ▼
Api\Auth\*Controller (extends Api\V1\ApiController)
        │
        ├─ ApiFormRequest (validation → standard envelope)
        ├─ AuthTokenResource (login/register payload)
        ├─ UserResource + UserProfileResource
        │
        ▼
Services: DeviceTrackingService, LoginHistoryService
```

## Mobile-Friendly Response Shapes

### Auth token responses (register/login)

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": { "id": 1, "email_verified": true, "profile": {} },
    "token": "1|...",
    "token_type": "Bearer"
  },
  "meta": {}
}
```

### Profile responses

```json
{
  "success": true,
  "data": { "user": {} },
  "meta": {}
}
```

## Features

- **Sanctum** — Bearer token per device
- **Device tracking** — `DeviceTrackingService` on register/login; removed on logout
- **Login history** — Success via controller; failures via `RecordFailedLoginAttempt` listener
- **Email verification** — Sent on register; status/resend/verify endpoints available
- **Profile** — Under `/auth/profile`, accessible without verified email

## Route File

Auth routes live in `routes/api/v1/auth.php`, included from `routes/api.php`.

Profile was moved from `/api/v1/profile` to `/api/v1/auth/profile` (route names: `api.auth.profile.*`).
