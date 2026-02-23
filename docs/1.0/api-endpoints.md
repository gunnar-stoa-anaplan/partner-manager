# API Endpoints

This page lists all available REST API endpoints. All endpoints require a valid Sanctum bearer token with the appropriate abilities.

## Token Management

### Create Token

```
POST /api/v1/tokens
```

**Ability required:** Authenticated user session

**Body:**

```json
{
    "name": "My Integration",
    "abilities": ["partners:read", "imports:write"]
}
```

**Response:** Returns the plaintext token (only shown once).

### List Tokens

```
GET /api/v1/tokens
```

**Ability required:** Authenticated user session

### Revoke Token

```
DELETE /api/v1/tokens/{id}
```

**Ability required:** Authenticated user session

---

## Partners

### List Partners

```
GET /api/v1/partners
```

**Ability required:** `partners:read`

Returns a paginated list of all partners with basic information.

### Get Partner

```
GET /api/v1/partners/{id}
```

**Ability required:** `partners:read`

Returns detailed information for a specific partner.

### Get Partner Pipeline

```
GET /api/v1/partners/{id}/pipeline
```

**Ability required:** `partners:read`

Returns the opportunity pipeline for a specific partner.

### Get Partner Projects

```
GET /api/v1/partners/{id}/projects
```

**Ability required:** `partners:read`

Returns active projects for a specific partner.

---

## Imports

### Import Opportunities

```
POST /api/v1/imports/opportunities
```

**Ability required:** `imports:write`

Accepts a CSV or JSON payload of opportunity records to import.

### Import Projects

```
POST /api/v1/imports/projects
```

**Ability required:** `imports:write`

Accepts a CSV or JSON payload of project records to import.

### Import Contacts

```
POST /api/v1/imports/contacts
```

**Ability required:** `imports:write`

Accepts a CSV or JSON payload of contact records to import.

### Get Import Status

```
GET /api/v1/imports/{id}
```

**Ability required:** `imports:read`

Returns the status and results of a specific import job.

---

## Enablement

### List Calendar Events

```
GET /api/v1/enablement/calendar
```

**Ability required:** `enablement:read`

Returns upcoming enablement events and sessions.

### List Enablement Events

```
GET /api/v1/enablement/events
```

**Ability required:** `enablement:read`

Returns all enablement events with registration details.

> [!NOTE]
> All collection endpoints support pagination via `page` and `per_page` query parameters. The default page size is 15 records.
