# Roles & Permissions

Partner Manager uses role-based access control to ensure users only see and do what they are authorized for. There are separate role systems for the main application and the Partner Portal.

## Application Roles

### Administrator

Administrators (both `admin` and `super_admin` in the system) have the broadest access, including:

- All partner data across the entire portfolio
- System configuration, branding, and alert thresholds
- User management and directory sync
- Feature activation (feature flags and activation paths)
- Team management and reporting hierarchy
- Scorecard weight and goal template configuration
- API token management
- Blueprint and enablement event administration
- All features available to other roles

> [!NOTE]
> The `super_admin` role has identical capabilities to `admin`, with the additional ability to access the Integration Hub and all CoE features. In practice, super admins see everything in the platform.

### CoE (Center of Excellence)

The CoE role is designed for strategic enablement leadership. CoE users manage the frameworks and programs that the rest of the organization executes against:

- **Strategy Workbench** — Create and manage fiscal-year plans with targets, budgets, initiatives, scenarios, and reviews
- **Partner Paths** — Define maturity paths with stages, gates, and task templates
- **Goals & Scorecards** — Create goal templates and scorecard configurations
- **Knowledge Base** — Ingest documents, manage embeddings, and curate the organizational knowledge repository
- **AI Agents** — Configure product expert agents, partner advisors, and enablement designers
- **Workshops & Labs** — Design and deliver workshops with Zoom integration, create hands-on labs with checkpoints
- **Enablement Events** — Create events, manage sessions, and track registrations
- **Launch Programs** — Set up cohort-based product launch programs with milestones
- **Blueprints** — Create structured learning paths with outcomes and content items
- **Branding** — Configure application name, logo, colors, and themes
- **Lead Assignment Rules** — Define routing rules for automatic lead distribution
- **Provisioning** — Manage workspace provisioning and assignments

> [!TIP]
> CoE users land on the Strategy Dashboard after login. Their navigation is focused on the design and management tools — they do not see the field-user views like Partners, Idea Room, or Analytics.

### PSM (Partner Success Manager)

PSMs are the primary field users of the platform. They execute the frameworks the CoE designs:

- View and manage their assigned partners
- Access the Partner Dashboard with detailed tabs (Pipeline, Enablement, Projects, Resources, and more)
- Create and track tasks, action plans, and goals
- Conduct QBR sessions and path reviews
- Access analytics, benchmarking, and portfolio views
- Register partners for enablement events
- Use Idea Room for collaborative ideation
- Manage business development leads and deal registrations
- Use AI-powered partner insights (when AI Agents is enabled)

### Manager

Managers have the same access as PSMs, plus team oversight capabilities:

- View partners across multiple PSM portfolios
- Access the reporting hierarchy and team member views
- Manage partner assignments within their team
- All PSM capabilities

### Integration

The Integration role is a specialized, restricted role for data operations:

- **Integration Hub** — Set up connectors, manage connections, configure field mappings, and run import/export jobs
- No access to partner data, analytics, or other platform features

> [!NOTE]
> Integration users land on the Integration Hub Dashboard. Their navigation shows only the integration tools.

## Portal Roles

Partner contacts access the platform through the Partner Portal with capability-based roles:

### Standard Contact

- View scorecard and goals
- Register for enablement events
- Track blueprint progress
- Submit deal registrations

### Team Lead

All standard capabilities, plus:

- View team member progress
- Manage contact information for their organization

### Mentor

All standard capabilities, plus:

- Access mentoring tools and peer comparison data

### Coordinator

All standard capabilities, plus:

- Manage event registrations for their organization
- Import contacts

## Impersonation

Administrators can impersonate a portal contact to see the portal from their perspective. This is useful for troubleshooting and support. The impersonation state is visible in the UI and can be exited at any time.

> [!NOTE]
> Impersonation is logged and only available to administrators. It provides a read-only view of what the contact would see.

## Feature-Level Gating

In addition to role-based access, individual features are gated by feature flags managed through the [Feature Activation](feature-activation) system. Each module has separate admin and end-user flags:

| Feature | Admin Flag | End-User Flag |
|---------|-----------|---------------|
| Partner Management | `partner_management_admin` | `partner_management_user` |
| Deal Registration | `deal_registration_admin` | `deal_registration_user` |
| Enablement Events | `enablement_events_admin` | `enablement_events_user` |
| Workshop Delivery | `workshop_delivery_admin` | `workshop_delivery_user` |
| Workshop Labs | `workshop_labs_admin` | `workshop_labs_user` |
| Launch Programs | `launch_programs_admin` | `launch_programs_user` |
| AI Agents | `ai_agents_admin` | `ai_agents_user` |
| Knowledge Base | `knowledge_base_admin` | `knowledge_base_user` |
| Project Health | `project_health_admin` | `project_health_user` |
| QBR Workbench | `qbr_admin` | `qbr_user` |
| Action Plans | `action_plans_admin` | `action_plans_user` |
| Benchmarking | `benchmark_admin` | `benchmark_user` |
| Provisioning | `provisioning_admin` | `provisioning_user` |
| Idea Room | `idea_room` | — |
| Strategy | `strategy` | — |
| Analytics | `analytics` | — |
| Directory Sync | `directory_sync_admin` | — |

Admin flags control access to creation and configuration screens. End-user flags control access to interactive features. Administrators and CoE users bypass feature gating — they can access all enabled modules regardless of flag state.

## Activation Paths

Beyond simple on/off flags, Partner Manager supports **activation paths** — phased learning progressions that gradually unlock features for individual users. Each path has three phases:

| Phase | Purpose |
|-------|---------|
| **Tell** | Educational content — read articles, watch videos |
| **Show** | Guided tours and demonstrations |
| **Do** | Hands-on tasks that prove competency |

Users must complete each phase before the feature becomes fully accessible. Progress is tracked per-user and visible on the Progression Dashboard. See [Feature Activation](feature-activation) for details.

## Role Access Summary

| Capability | Administrator | CoE | PSM | Manager | Integration |
|-----------|:---:|:---:|:---:|:---:|:---:|
| Partner Management | Y | — | Y | Y | — |
| Idea Room | Y | — | Y | Y | — |
| Business Development | Y | — | Y | Y | — |
| Deal Registration | Y | — | Y | Y | — |
| Analytics & Benchmarking | Y | — | Y | Y | — |
| Strategy Workbench | Y | Y | — | — | — |
| Knowledge Base | Y | Y | — | — | — |
| AI Agents | Y | Y | — | — | — |
| Workshops & Labs | Y | Y | — | — | — |
| Enablement Admin | Y | Y | — | — | — |
| Launch Programs | Y | Y | — | — | — |
| Branding | Y | Y | — | — | — |
| Integration Hub | Y | — | — | — | Y |
| System Administration | Y | — | — | — | — |
| Feature Activation | Y | Y* | — | — | — |
| Team Management | Y | Y | — | Y | — |
| User Management | Y | — | — | — | — |

*CoE access to Feature Activation requires the `feature_activation_coe` flag to be active.
