# Portal Overview

The Partner Portal is a self-service interface that gives partner contacts direct access to their own data, progress, and tools — without needing to go through their PSM for every request.

## Accessing the Portal

Partners access the portal at `/portal` using their own credentials. Portal accounts are separate from the main application — they use the `Contact` model with the `portal` authentication guard.

<!-- ![Portal dashboard](images/portal-dashboard.png) -->

## Portal Features

Depending on their role, a portal contact can:

- **View their scorecard** — See how their organization is performing
- **Track goals** — Monitor progress against fiscal-year targets
- **Browse blueprints** — Follow learning paths and track completion
- **Register for events** — Sign up for enablement sessions and workshops (requires `enablement_events_user`)
- **Submit deal registrations** — Register new opportunities for PSM approval
- **View projects** — See delivery status and team composition
- **Access labs** — Participate in hands-on training environments (requires `workshop_labs_user`)
- **Manage contacts** — Update their organization's contact directory

## Portal Dashboard

The portal dashboard provides a personalized view for each contact:

- Active blueprints and progress
- Upcoming events they're registered for
- Recent deal registration statuses
- Goal progress summary
- Announcements from the PSM team

## Portal vs PSM Application

| Feature | PSM Application | Partner Portal |
|---------|----------------|---------------|
| Partner data | Full edit access | Read-only view |
| Tasks | Full management | Not visible |
| Analytics | Full portfolio | Own data only |
| Blueprints | Assign and track | Self-paced learning |
| Events | Create and manage | Register and attend |
| Deal Registration | Review and approve | Submit and track status |

> [!NOTE]
> Portal access must be enabled per contact. See [Portal Roles](portal-roles) for how to configure portal access.
