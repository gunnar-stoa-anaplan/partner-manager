# API Endpoints

This page lists all available REST API endpoints. See [Authentication](api-authentication) for token setup and [API Overview](api-overview) for response format conventions.

## Authentication

### Login

```
POST /api/v1/auth/login
```

No token required. Authenticates with user credentials and returns a Sanctum bearer token.

**Body:**

```json
{
    "email": "user@example.com",
    "password": "your-password",
    "device_name": "My Integration",
    "abilities": ["partners:read", "imports:write"]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User email address |
| `password` | string | Yes | User password |
| `device_name` | string | Yes | Name to identify this token (e.g. integration name) |
| `abilities` | array | No | Token abilities to grant. Defaults to all abilities if omitted |

**Response (201):**

```json
{
    "message": "Authenticated successfully.",
    "token": "1|abc123...",
    "abilities": ["partners:read", "imports:write"]
}
```

**Error (401):** Returned when credentials are incorrect.

---

## Token Management

Tokens are managed via authenticated user session (no specific ability required).

### Create Token

```
POST /api/v1/tokens
```

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

### Revoke Token

```
DELETE /api/v1/tokens/{id}
```

---

## Partners

**Ability required:** `partners:read`

### List Partners

```
GET /api/v1/partners
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Filter by partner name |
| `geography` | string | Filter by geography |
| `region` | string | Filter by region |
| `psm` | string | Filter by PSM name |
| `per_page` | int | Results per page (max 100, default 25) |

Returns a paginated list of partners.

### Get Partner

```
GET /api/v1/partners/{id}
```

Returns detailed information for a specific partner.

### Get Partner Pipeline

```
GET /api/v1/partners/{id}/pipeline
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `stage` | string | Filter by pipeline stage |
| `fiscal_year` | string | Filter by fiscal year |
| `revenue_type` | string | Filter by revenue type |
| `per_page` | int | Results per page |

Returns a paginated list of opportunities for the partner.

### Get Partner Projects

```
GET /api/v1/partners/{id}/projects
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by project status |
| `per_page` | int | Results per page |

Returns a paginated list of projects for the partner.

### Get Partner Advisor Insights

```
GET /api/v1/partners/{partner}/advisor
```

Runs the AI Partner Advisor agent and returns structured insights for the given partner, including health assessment, risks, opportunities, and recommended actions.

> [!NOTE]
> This endpoint requires the `ai_agents_user` feature flag to be enabled.

---

## Imports

### Import Opportunities

```
POST /api/v1/imports/opportunities
```

**Ability required:** `imports:write`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `opportunities` | array | Yes | Array of opportunity records |
| `snapshot_date` | date | No | Date of the data snapshot |

Each opportunity record supports: `partner_name`, `opportunity_name`, `account_name`, `stage`, `amount`, `close_date`, `fiscal_quarter`, `fiscal_year`, `type`, `revenue_type`, `product_area`, `probability`, `owner`, `lead_source`, `salesforce_id`.

### Import Projects

```
POST /api/v1/imports/projects
```

**Ability required:** `imports:write`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `partner_name` | string | Yes | Name of the partner |
| `projects` | array | Yes | Array of project records |

Each project record supports: `name`, `status`, `acv`, `start_date`, `end_date`, `percent_complete`, `client`, `delivery_team`, `initiative`, `budget`.

### Import Contacts

```
POST /api/v1/imports/contacts
```

**Ability required:** `imports:write`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `partner_name` | string | Yes | Name of the partner |
| `contacts` | array | Yes | Array of contact records |

Each contact record supports: `first_name`, `last_name`, `email`, `title`, `phone`.

### Get Import Status

```
GET /api/v1/imports/{id}
```

**Ability required:** `imports:read`

Returns the status and results of a specific import job.

---

## Enablement

**Ability required:** `enablement:read`

### List Calendar Events

```
GET /api/v1/enablement/calendar
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `start` | date | Filter events starting after this date |
| `end` | date | Filter events ending before this date |
| `type` | string | Filter by event type |
| `geography` | string | Filter by geography |
| `product_area` | string | Filter by product area |

### List Enablement Events

```
GET /api/v1/enablement/events
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | Filter by event type |
| `category` | string | Filter by category |
| `product_area` | string | Filter by product area |
| `per_page` | int | Results per page (max 100, default 25) |

### Get Enablement Event

```
GET /api/v1/enablement/events/{id}
```

Returns full details for a specific enablement event, including sessions and registrations.

### Generate Enablement Content (AI)

```
POST /api/v1/enablement/designer
```

Runs the AI Enablement Designer agent to generate event content based on a prompt.

> [!NOTE]
> Requires the `enablement_events_user` feature flag.

### Generate Lab Content (AI)

```
POST /api/v1/enablement/lab-designer
```

Runs the AI Lab Designer agent to generate workshop lab content based on a prompt.

> [!NOTE]
> Requires the `workshop_labs_user` feature flag.

---

## Integration Hub

These endpoints manage data integrations through configured connector profiles. Requires the `integration_hub` feature flag.

### List Connections

```
GET /api/v1/integration-hub/connections
```

Returns all configured integration connections with their status and sync metadata.

### Push Data

```
POST /api/v1/integration-hub/connections/{connection}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `rows` | array | Yes | Array of data records (min 1) |
| `target_model` | string | Yes | Target model for the data |
| `snapshot_date` | date | No | Date of the data snapshot |

Pushes data into Partner Manager through a configured connection.

### Pull Data

```
POST /api/v1/integration-hub/connections/{connection}/pull
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `model` | string | Yes | Model to pull: `partners`, `contacts`, `opportunities`, `resources`, `projects`, `leads` |
| `filters` | array | No | Optional filter criteria |
| `limit` | int | No | Max records to return (1–1000) |

Pulls data out of Partner Manager through a configured connection.

### Get Job Status

```
GET /api/v1/integration-hub/jobs/{job}
```

Returns the status, progress, and error log for an integration job.

---

## Idea Room

All Idea Room endpoints require authentication and the `idea_room` feature flag. Base path: `/api/v1/idea-room`.

### Ideas

#### List Ideas

```
GET /api/v1/idea-room/ideas
```

#### Get Idea

```
GET /api/v1/idea-room/ideas/{idea}
```

#### Get Constellation Graph

```
GET /api/v1/idea-room/ideas/constellation
```

Returns a graph structure with `nodes` and `edges` representing idea relationships.

#### Route Idea to Room

```
POST /api/v1/idea-room/ideas/{idea}/route-to-room
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `room_uuid` | string | Conditional | UUID of existing room (required without `create_new`) |
| `create_new` | boolean | Conditional | Create a new room (required without `room_uuid`) |

### Rooms

#### List Rooms

```
GET /api/v1/idea-room/rooms
```

#### Get Room

```
GET /api/v1/idea-room/rooms/{room}
```

#### Create Room for Idea

```
POST /api/v1/idea-room/ideas/{idea}/rooms
```

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Room name |
| `agent_persona` | string | AI agent persona |

#### Update Room

```
PATCH /api/v1/idea-room/rooms/{room}
```

### Messages

#### List Room Messages

```
GET /api/v1/idea-room/rooms/{room}/messages
```

#### Send Message (AI Chat)

```
POST /api/v1/idea-room/rooms/{room}/messages
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message` | string | Yes | The message to send |

Returns an AI-generated response. Returns `503` if the AI service is unavailable.

### Artifacts

#### Create Artifact for Idea

```
POST /api/v1/idea-room/ideas/{idea}/artifacts
```

#### Create Artifact for Room

```
POST /api/v1/idea-room/rooms/{room}/artifacts
```

| Field | Type | Description |
|-------|------|-------------|
| `type` | string | Artifact type |
| `title` | string | Artifact title |
| `content` | string | Artifact content |
| `metadata` | array | Additional metadata |

#### Get Artifact

```
GET /api/v1/idea-room/artifacts/{artifact}
```

#### Download Artifact

```
GET /api/v1/idea-room/artifacts/{artifact}/download
```

#### Update Artifact

```
PATCH /api/v1/idea-room/artifacts/{artifact}
```

Creates a new version of the artifact with the updated content.

#### Delete Artifact

```
DELETE /api/v1/idea-room/artifacts/{artifact}
```

### Agent Memories

#### List Room Memories

```
GET /api/v1/idea-room/rooms/{room}/memories
```

Returns agent memories for the room, ordered by importance.

#### Delete Memory

```
DELETE /api/v1/idea-room/rooms/{room}/memories/{memory}
```

### Dashboard

#### Get Dashboard Stats

```
GET /api/v1/idea-room/dashboard/stats
```

Returns: `active_rooms`, `spark_unrouted`, `growing`, `developed`, `total_ideas`, `recent_room`.

### Gallery

#### List Templates

```
GET /api/v1/idea-room/gallery/templates
```

#### Get Gallery Config

```
GET /api/v1/idea-room/gallery/config
```

#### Set Template

```
POST /api/v1/idea-room/gallery/template
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `slug` | string | Yes | Template slug (max 64 chars) |

### Chat

#### Send Chat Message

```
POST /api/v1/idea-room/chat
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message` | string | Yes | The message to send |
| `conversation_history` | array | No | Prior conversation context |

#### Get Chat History

```
GET /api/v1/idea-room/chat/history
```

---

## Internal Endpoints

These endpoints are used for agent-to-agent communication and the control plane. They do not require authentication tokens — access is controlled at the network level.

### Application Metrics

```
GET /api/v1/internal/metrics
```

Returns operational metrics: partner count, user count, app version, error rates, job queue status, database health, storage, cache statistics, and activation data.

### Signal Aggregate

```
GET /api/v1/internal/signal-aggregate
```

Returns aggregated signal engine data.

### Store Signal Benchmarks

```
POST /api/v1/internal/signal-benchmarks
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `cohort_size` | int | Yes | Number of items in the cohort |
| `metrics` | array | Yes | Benchmark metric data |
| `computed_date` | date | Yes | Date the benchmarks were computed |

### Activation Aggregate

```
GET /api/v1/internal/activation-aggregate
```

Returns aggregated activation path progress data.

### Store Activation Benchmarks

```
POST /api/v1/internal/activation-benchmarks
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `cohort_size` | int | Yes | Number of items in the cohort |
| `metrics` | array | Yes | Benchmark metric data |
| `computed_date` | date | Yes | Date the benchmarks were computed |

---

> [!NOTE]
> All collection endpoints support pagination via `page` and `per_page` query parameters. The default page size is 15 records unless otherwise noted.
