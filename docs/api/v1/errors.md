# Error Responses

All API errors return the standard envelope with `success: false`.

## HTTP Status Codes

| Status | Meaning |
|--------|---------|
| 400 | Bad request |
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Not found |
| 422 | Validation failed |
| 429 | Rate limit exceeded |
| 500 | Server error |

## Examples

### 401 Unauthenticated

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "data": {},
  "meta": {}
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "Endpoint not found.",
  "data": {},
  "meta": {}
}
```

### 422 Validation Error

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": {
    "errors": {
      "email": ["The email field is required."],
      "password": ["The password field is required."]
    }
  },
  "meta": {}
}
```

### 429 Rate Limited

```json
{
  "success": false,
  "message": "Too Many Attempts.",
  "data": {},
  "meta": {}
}
```

When `APP_DEBUG=true`, 500 responses may include exception details in `data` for debugging.
