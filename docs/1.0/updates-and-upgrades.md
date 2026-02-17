# Updates & Upgrades

This page describes how new versions of Partner Manager are released, how clients are notified, what control clients have over the update process, and how new features move from development through to acceptance.

## Terminology

| Term | Definition | Example |
|------|-----------|---------|
| **Patch** | Bug fix or security fix within the same minor version | 2.1.0 → 2.1.1 |
| **Update** | Minor version with small improvements or non-breaking changes | 2.1.x → 2.2.0 |
| **Upgrade** | Major version with new features, possible workflow changes, or breaking changes | 2.x → 3.0.0 |

All three follow the same deployment mechanics. The difference is in scope, communication, and how much client review is expected before acceptance.

## Distribution Model

Partner Manager is distributed as **pre-built Docker images** — not source code. Clients never receive, access, or need application source code at any point.

### What the Client Receives

| What | Format | Contains Source Code? |
|------|--------|----------------------|
| **Application image** | Docker image (`ghcr.io/myservice/partner-manager:2.1.0`) | **No** |
| **Agent image** | Docker image (`ghcr.io/myservice/pm-agent:1.2.0`) | **No** |
| **Configuration files** | `docker-compose.yml`, `.env` template, `config.yaml` | N/A (not application code) |
| **Certificates** | PEM files | N/A |

### What's Inside the Docker Image

The Docker image is a compiled, production-optimized artifact. It goes through a multi-stage build process that strips development files and compiles assets:

```
Source Code (development)
        │
        ▼
┌─────────────────────────────────────────────┐
│  Build Stage (discarded after build)         │
│                                             │
│  - Install production dependencies only     │
│  - Compile frontend assets (CSS, JS)        │
│  - Pre-compile application templates        │
│  - Optimize autoloader                      │
│  - Generate route and config caches         │
│  - Strip development tooling                │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  Production Image (what the client runs)     │
│                                             │
│  ✓ Compiled, cached application files       │
│  ✓ Pre-built frontend bundles (CSS, JS)     │
│  ✓ Production runtime only                  │
│  ✓ Security-hardened base image             │
│                                             │
│  ✗ No development dependencies              │
│  ✗ No test suites                           │
│  ✗ No build tools                           │
│  ✗ No source control history                │
│  ✗ No documentation source                  │
│  ✗ No CI/CD configuration                   │
└─────────────────────────────────────────────┘
```

### Why Docker Images, Not Source Code

| Concern | How Docker Images Address It |
|---------|------------------------------|
| **Intellectual property** | Clients receive a compiled artifact, not readable source code |
| **Consistency** | Every client runs the exact same tested build — no "works on my machine" issues |
| **Security** | No build tools, dev dependencies, or test data in production. Smaller attack surface. |
| **Simplicity** | Clients run `docker compose up -d` — no build steps, no dependency installation, no compilation |
| **Integrity** | Every image is signed with Ed25519. The agent verifies the signature before deploying. Tampered images are rejected. |
| **Versioning** | Each image is tagged with a specific version (`2.1.0`). Pinned, immutable, and reproducible. |

### Client Access to the Container

Clients do not need to — and should not — access the inside of the running container. The application is operated entirely through its external interfaces:

| Interface | Purpose |
|-----------|---------|
| **Web UI** (ports 80/443) | Application usage |
| **Environment variables** (`.env`) | Configuration |
| **Docker commands** (`docker compose`) | Start, stop, view logs |
| **Agent** | Remote management, updates, monitoring |

> [!IMPORTANT]
> The Docker image is a sealed artifact. Clients do not modify files inside the container. Configuration is injected through environment variables, and updates are delivered as new image versions — never as patches applied to a running container.

### Container Registry Access

Images are hosted on a private container registry. Client hosts authenticate to pull images:

| Property | Detail |
|----------|--------|
| **Registry** | Private registry (e.g., GitHub Container Registry, ECR, ACR) |
| **Authentication** | Registry credentials provided during onboarding |
| **Access scope** | Pull-only — clients cannot push or modify images |
| **Image signing** | Every image is signed with Ed25519; the agent verifies before deploying |

## Release Lifecycle

Every release follows the same pipeline — from development through to client deployment:

```
Development       Internal QA       Release         Client Review      Deployment
    │                  │                │                  │                │
    │  Code changes    │                │                  │                │
    │  committed       │                │                  │                │
    │─────────────────>│                │                  │                │
    │                  │  Automated     │                  │                │
    │                  │  test suite    │                  │                │
    │                  │  Security scan │                  │                │
    │                  │───────────────>│                  │                │
    │                  │               │  Build image      │                │
    │                  │               │  Sign (Ed25519)   │                │
    │                  │               │  Publish to       │                │
    │                  │               │  registry         │                │
    │                  │               │  Publish release   │                │
    │                  │               │  notes            │                │
    │                  │               │─────────────────>│                │
    │                  │               │                  │  Client reviews │
    │                  │               │                  │  release notes  │
    │                  │               │                  │  Client accepts │
    │                  │               │                  │  or defers      │
    │                  │               │                  │────────────────>│
    │                  │               │                  │                │  Agent
    │                  │               │                  │                │  verifies
    │                  │               │                  │                │  signature
    │                  │               │                  │                │  + deploys
```

### What Happens at Each Stage

| Stage | What Happens | Who |
|-------|-------------|-----|
| **Development** | Code changes are committed, peer-reviewed, and merged | MyService engineering |
| **Internal QA** | Automated test suite runs, security scans execute, manual QA where appropriate | MyService QA |
| **Release** | Docker image is built, signed with Ed25519 private key, published to container registry, release notes written | MyService CI/CD pipeline |
| **Client review** | Client is notified, reviews release notes, decides whether to accept | Client + MyService operator |
| **Deployment** | Operator triggers deployment, agent verifies signature, pulls image, restarts app | Agent (automated) |

## How Clients Are Notified

### In-App Notification

The primary notification method is a **banner inside the application itself**. When a new version is available, administrators see a notification banner at the top of every page:

```
┌──────────────────────────────────────────────────────────────────────┐
│  ⬇ Version 2.2.0 is available. New scorecard templates, PDF    [Review Update]  │
│    export improvements.                                              │
└──────────────────────────────────────────────────────────────────────┘
```

This works through the agent — no new network connections from the application:

```
Control Plane                     Agent                      App
     │                              │                          │
     │  Heartbeat response includes │                          │
     │  "update_available" field    │                          │
     │─────────────────────────────>│                          │
     │                              │                          │
     │                              │── writes ──────────────>│
     │                              │   version-check.json     │
     │                              │   (shared volume)        │
     │                              │                          │
     │                              │                    App reads file
     │                              │                    Admin sees banner
     │                              │                          │
     │                              │                    Admin clicks
     │                              │                    "Review Update"
     │                              │                          │
     │                              │                    Reviews release notes
     │                              │                    Clicks "Accept & Deploy"
     │                              │                          │
     │                              │<── reads ───────────────│
     │                              │   update-accepted.json   │
     │                              │                          │
     │  Agent includes acceptance   │                          │
     │  in next heartbeat           │                          │
     │<─────────────────────────────│                          │
     │                              │                          │
     │  Control plane queues        │                          │
     │  deploy command              │                          │
     │─────────────────────────────>│                          │
     │                              │                          │
     │                              │  Verify, pull, restart   │
     │                              │                          │
     │                              │               App restarts on
     │                              │               new version
```

The admin can review the update details — version number, release type, summary, and changelog — directly within the application before accepting. Once accepted, the agent deploys the new version within 60 seconds.

### Additional Notification Channels

For significant releases, clients are also notified through external channels:

| Release Type | Notification Method | Lead Time |
|-------------|---------------------|-----------|
| **Security patch** | In-app banner + direct communication (email + call if critical) | Immediate — deployed as soon as client confirms |
| **Patch** | In-app banner + email notification with release notes | Available immediately, deployed at client's convenience |
| **Update** | In-app banner + email notification | Minimum 5 business days before suggested deployment |
| **Upgrade** | In-app banner + dedicated communication: email, feature walkthrough, optional demo | Minimum 15 business days before suggested deployment |

### Release Notes

Every release includes structured release notes covering:

- **Version number** and release date
- **Summary** of changes in plain language
- **New features** with descriptions and screenshots where applicable
- **Bug fixes** with references to reported issues
- **Security fixes** (described without disclosing exploit details)
- **Breaking changes** (upgrades only) with migration guidance
- **Database changes** — whether the release includes schema migrations
- **Known issues** and workarounds if applicable

## What Is Automatic vs. Manual

### Nothing Is Deployed Without Client Awareness

The most important principle: **no version change is ever applied to a client instance without explicit action**. The agent does not auto-update the application.

| Action | Automatic? | Details |
|--------|-----------|---------|
| Application version changes | **No** | Always requires explicit acceptance — either by the client admin (via the in-app update page) or by the MyService operator (via the control plane) |
| Database migrations | **No** | Run as part of the deployment process; included in release notes for review |
| Agent updates | **No** | Agent image is pinned in `docker-compose.yml`; updated only during coordinated deployments |
| Certificate renewal | **Yes** | Agent auto-renews mTLS certificates 30 days before expiry — no version change involved |
| Health monitoring | **Yes** | Heartbeats continue automatically — operational, not a version change |

### What the Agent Does Automatically

The agent performs only two things without operator intervention:

1. **Heartbeats** — health telemetry sent every 30 seconds (operational monitoring)
2. **Certificate renewal** — mTLS certificate renewed automatically before expiry

Neither of these changes the application version, modifies client data, or alters application behavior.

## Client Control Over Updates

### Acceptance Workflow

Clients have full control over when and whether updates are applied:

```
New Release Published
        │
        ▼
Client Notified (email + release notes)
        │
        ▼
Client Reviews Release Notes
        │
        ├── Questions? ──> Discuss with MyService contact
        │
        ├── Not ready? ──> Defer (no action taken, current version continues)
        │
        ├── Accept ──────> Confirm preferred deployment window
        │                       │
        │                       ▼
        │                  Operator schedules deployment
        │                       │
        │                       ▼
        │                  Agent deploys during agreed window
        │                       │
        │                       ▼
        │                  Client verifies application
        │
        └── Decline ─────> Current version continues indefinitely
```

### Deployment Windows

Clients can specify when deployments should occur:

| Option | Description |
|--------|-------------|
| **Immediate** | Deploy as soon as the client confirms acceptance |
| **Scheduled** | Deploy during a specific maintenance window (e.g., Saturday 02:00–04:00 UTC) |
| **Next business day** | Deploy during the next business day's off-peak hours |

Deployments are typically completed within 60 seconds — the agent pulls the new image, verifies the signature, and restarts the app container. Brief downtime (5–15 seconds) occurs during the container restart.

### Version Pinning

Clients always run a specific, pinned version. There is no "latest" or "auto-update" channel in production:

```
┌─────────────────────────────────────────────┐
│  Fleet Dashboard — Version Distribution      │
│                                             │
│  Version   Instances   Status               │
│  ────────  ──────────  ──────               │
│  v2.2.0    12          Current release      │
│  v2.1.1    5           Previous (supported) │
│  v2.1.0    2           Previous (supported) │
│  v2.0.3    1           Approaching EOL      │
└─────────────────────────────────────────────┘
```

Clients are never forced to upgrade. However, older versions will eventually reach end-of-life (see Version Support Policy below).

### Can a Client Skip Versions?

| Scenario | Supported? | Details |
|----------|-----------|---------|
| Skip a patch (2.1.0 → 2.1.2, skipping 2.1.1) | **Yes** | Patches are cumulative |
| Skip an update (2.1.x → 2.3.0, skipping 2.2.0) | **Yes** | Updates are cumulative within a major version |
| Skip a major upgrade (2.x → 4.x, skipping 3.x) | **Case by case** | May require stepping through major versions if migrations are sequential |

## How New Features Are Published

### Feature Development Process

```
Feature Request / Roadmap Item
        │
        ▼
Design & Development
        │
        ▼
Internal Testing & QA
        │
        ▼
Release Candidate ──────────> Selected clients (optional preview)
        │                            │
        │                     Feedback collected
        │                            │
        ▼                            ▼
General Availability Release
        │
        ▼
Release Notes Published
        │
        ▼
Clients Notified
        │
        ▼
Clients Accept at Their Own Pace
```

### Feature Visibility

New features are introduced in one of two ways:

| Approach | Description | Client Action Required |
|----------|-------------|----------------------|
| **Available immediately** | Feature is active once the update is deployed. Typically used for enhancements to existing workflows. | Accept and deploy the update |
| **Opt-in activation** | Feature is deployed but disabled by default. Client enables it through application settings when ready. | Deploy the update, then enable the feature in settings when ready |

> [!NOTE]
> Breaking changes to existing workflows are reserved for major upgrades only and are always communicated well in advance with migration guidance.

## Database Migrations

Some releases include database schema changes (new tables, new columns, index modifications). These are handled automatically during the deployment process.

### How Migrations Work

```
Agent receives deploy command
        │
        ▼
Verify Ed25519 signature on new image
        │
        ▼
Pull new image from registry
        │
        ▼
Stop current app container
        │
        ▼
Start new app container ──────> Migrations run automatically on boot
        │                              │
        │                        New tables created
        │                        New columns added
        │                        Indexes optimized
        │                              │
        ▼                              ▼
Application serving on new version
```

### Migration Safety

| Concern | How It's Handled |
|---------|-----------------|
| **Data loss** | Migrations only add or modify — they never drop tables or delete data without explicit documentation |
| **Reversibility** | Every migration has a rollback counterpart. If a deployment fails, the previous version is restored. |
| **Long-running migrations** | Flagged in release notes so clients can plan for extended maintenance windows if needed |
| **Review before deployment** | Migration details are included in release notes — clients know exactly what schema changes will occur |

## Rollback

If a deployment causes issues, the operator can roll back to the previous version:

```
Operator                      Control Plane                   Agent
   │                               │                            │
   │  "Rollback inst_abc123"       │                            │
   │──────────────────────────────>│                            │
   │                               │  Queue rollback command    │
   │                               │  (previous image tag)      │
   │                               │                            │
   │                               │  (next heartbeat)          │
   │                               │<── heartbeat ──────────────│
   │                               │── commands: [rollback] ───>│
   │                               │                            │
   │                               │         Pull previous image│
   │                               │         Run down-migrations│
   │                               │         Restart app        │
   │                               │                            │
   │                               │<── command report ─────────│
   │                               │                            │
   │  Dashboard: "Rolled back"     │                            │
   │<──────────────────────────────│                            │
```

Rollbacks restore both the application code and the database schema to the previous state.

## Version Support Policy

| Status | Definition | Support Level |
|--------|-----------|---------------|
| **Current** | The latest release | Full support — bug fixes, security patches, new features |
| **Supported** | Previous minor/major versions within support window | Security patches and critical bug fixes |
| **End of Life (EOL)** | Versions past the support window | No patches. Clients are encouraged to upgrade. |

### Support Window

| Version Type | Supported For |
|-------------|---------------|
| **Major version** | 12 months after the next major version is released |
| **Minor version** | Until the next minor version is released |
| **Patches** | Superseded immediately by the next patch |

> [!WARNING]
> Running an EOL version does not cause the application to stop working. The instance continues to operate normally. However, no further security patches will be issued for EOL versions, so upgrading is strongly recommended.

## Security Patches

Security patches receive expedited handling:

| Severity | Response Time | Client Communication | Deployment |
|----------|-------------|---------------------|------------|
| **Critical** (active exploitation or data risk) | Patch released within 24 hours | Direct call + email to all clients | Strongly recommended immediately |
| **High** (exploitable vulnerability) | Patch released within 72 hours | Email to all clients | Recommended within 1 week |
| **Medium** (limited risk) | Included in next scheduled release | Noted in release notes | Normal deployment cycle |
| **Low** (theoretical or minimal impact) | Included in next scheduled release | Noted in release notes | Normal deployment cycle |

Even for critical security patches, the deployment still requires client acceptance — the agent will never apply changes automatically. However, MyService will communicate urgency directly and work with the client to deploy as quickly as possible.

## Summary

| Question | Answer |
|----------|--------|
| Is anything automatically deployed? | **No.** Every application update requires explicit action. |
| Can the client defer or decline an update? | **Yes.** Clients control their own deployment schedule. |
| Can the client skip versions? | **Yes** for patches and updates. Case-by-case for major upgrades. |
| How is the client notified? | Email, release notes, and direct communication for major changes. |
| Are new features forced on? | **No.** Features are either part of accepted updates or opt-in via settings. |
| Can a deployment be rolled back? | **Yes.** The operator can revert to the previous version at any time. |
| Does the agent auto-update itself? | **No.** Agent version is pinned and updated only during coordinated deployments. |
| What happens to EOL versions? | The application keeps running. No further patches are issued. |
