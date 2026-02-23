# API Overview

Partner Manager exposes a RESTful API at `/api/v1` for programmatic access. The API enables external systems to integrate with Partner Manager for data imports, partner data retrieval, and enablement information.

## Base URL

All API endpoints are prefixed with `/api/v1`. For example:

```
https://your-instance.example.com/api/v1/partners
```

## What the API Supports

| Area | Capabilities |
|------|-------------|
| **Imports** | Create import records for opportunities, projects, and contacts |
| **Partners** | Read partner data, pipeline information, and project details |
| **Enablement** | Access calendar events and enablement data |
| **Tokens** | Manage API tokens (create, list, revoke) |

## Response Format

All API responses return JSON. Successful responses follow this structure:

```json
{
    "data": {
        "id": 1,
        "name": "Partner Name",
        "tier": "Gold"
    }
}
```

Collection endpoints return paginated results:

```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "total": 42
    }
}
```

## Error Handling

Errors return appropriate HTTP status codes with a JSON error body:

| Code | Meaning |
|------|---------|
| `401` | Unauthenticated — missing or invalid token |
| `403` | Forbidden — token lacks required ability |
| `404` | Not found |
| `422` | Validation error — invalid request data |
| `500` | Server error |

## Rate Limiting

API requests may be subject to rate limiting in production environments. If rate-limited, you will receive a `429 Too Many Requests` response with a `Retry-After` header.

> [!NOTE]
> See [Authentication](api-authentication) for how to obtain and use API tokens, and [Endpoints](api-endpoints) for the full endpoint reference.
