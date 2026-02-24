# API Authentication

The Partner Manager API uses **Laravel Sanctum** bearer tokens for authentication. Each token is scoped with specific abilities that control what the token can access.

## Creating a Token

### Via API (Login Endpoint)

External integrations can obtain a token programmatically by calling the login endpoint:

```bash
curl -X POST https://your-instance.example.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "user@example.com", "password": "your-password", "device_name": "My Integration", "abilities": ["partners:read"]}'
```

The response includes a `token` field with the bearer token. See [Endpoints](api-endpoints#authentication) for full request/response details.

### Via Web Interface

Admins can also create API tokens from the Partner Manager interface:

1. Navigate to **Administration > API Tokens**
2. Click **Create Token**
3. Enter a descriptive name for the token
4. Select the abilities (permissions) this token should have
5. Click **Create**

The token will be displayed once. Copy it immediately — it cannot be retrieved again.

> [!WARNING]
> Store API tokens securely. Treat them like passwords. If a token is compromised, revoke it immediately from the API Tokens page.

## Token Abilities

Each token can be scoped with one or more abilities:

| Ability | Permission |
|---------|-----------|
| `imports:write` | Create import records (opportunities, projects, contacts) |
| `imports:read` | Read import status and results |
| `partners:read` | Read partner data, pipeline, and projects |
| `enablement:read` | Access enablement events and calendar data |

## Using the Token

Include the token in the `Authorization` header of every API request:

```
Authorization: Bearer your-api-token-here
```

Example using cURL:

```bash
curl -X GET https://your-instance.example.com/api/v1/partners \
  -H "Authorization: Bearer your-api-token-here" \
  -H "Accept: application/json"
```

## Token Lifecycle

- Tokens do not expire by default
- Admins can revoke tokens at any time
- Revoking a token is immediate — all subsequent requests with that token will return `401`

## CSRF Protection

API routes are exempt from CSRF token requirements when using Sanctum bearer tokens. If you are making API calls from a browser-based frontend, use Sanctum's cookie-based authentication instead.

> [!TIP]
> Create separate tokens for each integration. If one integration is decommissioned or compromised, you can revoke just that token without affecting others.
