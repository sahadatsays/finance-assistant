# Swagger / OpenAPI Documentation

Interactive API documentation powered by [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) and OpenAPI 3.0.

## Swagger UI URLs (local / development only)

| Documentation | URL |
|---------------|-----|
| Public APIs | `/api/documentation/public` |
| Authenticated APIs | `/api/documentation` |
| Admin APIs | `/api/documentation/admin` |

Swagger UI is **disabled in production** (`SWAGGER_UI_ENABLED=false` or non-local `APP_ENV`).

## Generate documentation

```bash
composer docs:swagger
# or
php artisan l5-swagger:generate --all
php artisan l5-swagger:generate public
php artisan l5-swagger:generate authenticated
php artisan l5-swagger:generate admin
```

Generated files are stored in `storage/api-docs/`.

## Authentication in Swagger UI

See **[authentication.md](./authentication.md)** for the full Sanctum bearer workflow.

Quick steps:

1. Open **Public** docs → `POST /auth/login` → copy `data.token`
2. Open **Authenticated** docs → click **Authorize** → paste token (no `Bearer` prefix)
3. Test protected endpoints (lock icon) — token is applied automatically

Optional: set `X-Tenant-Id` for multi-tenant workspace context.

## Project structure

```
app/OpenApi/
├── Shared/          # Security schemes, shared parameters
├── Schemas/         # Reusable models (User, Tenant, Category, ...)
├── Responses/       # Reusable error/success responses
├── PublicApi/       # Public + auth registration paths
├── Authenticated/   # Tenant finance module paths
└── Admin/           # Platform admin paths

config/swagger.php   # Centralized Swagger configuration
config/l5-swagger.php
```

## Annotation standards

Every new endpoint must include:

| Field | Requirement |
|-------|-------------|
| `summary` | Short action title |
| `description` | Business context and auth notes |
| `tags` | Module name matching `config/swagger.php` groups |
| `security` | `[['sanctum' => []]]` for protected routes |
| `parameters` | Include `XTenantId` for tenant-scoped routes |
| `requestBody` | JSON schema with validation rules |
| `responses` | 200/201 success + 401, 403, 404, 422, 500 as applicable |

### Controller example

See `CategoryController` for full CRUD documentation on controller methods.

### Module path example

Add operations to `app/OpenApi/Authenticated/FinanceModulePaths.php` or create a new `*Paths.php` file in the appropriate documentation folder.

### Reusable components

- Schemas: `app/OpenApi/Schemas/`
- Responses: `app/OpenApi/Responses/ApiResponses.php`

Register new schema files in `config/swagger.php` annotation paths if placed outside `app/OpenApi/Schemas`.

## Best practices for future modules

1. Add a `@OA\Tag` in the relevant `OpenApiDefinition.php`.
2. Create or extend a `*Paths.php` class for module endpoints.
3. Add reusable schemas before referencing them with `ref`.
4. Document validation rules in `requestBody` properties (`required`, `maxLength`, `enum`).
5. Run `composer docs:swagger` before committing API changes.
6. Keep public, authenticated, and admin operations in separate documentation sets.

## Environment variables

| Variable | Default | Description |
|----------|---------|-------------|
| `SWAGGER_UI_ENABLED` | `true` in local/dev | Enable Swagger UI routes |
| `SWAGGER_GENERATE_ALWAYS` | `true` in local | Regenerate on each request |
| `SWAGGER_OPEN_API_VERSION` | `3.0.0` | OpenAPI specification version |
| `L5_SWAGGER_CONST_HOST` | `APP_URL` | Server URL in generated spec |
