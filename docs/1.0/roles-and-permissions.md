# Roles & Permissions

Partner Manager uses role-based access control to ensure users only see and do what they are authorized for. There are separate role systems for the PSM application and the Partner Portal.

## PSM Application Roles

### Admin

Administrators have full access to the platform, including:

- All partner data across the portfolio
- Path and stage configuration
- User management
- Blueprint and enablement event administration
- Workshop lab creation and management
- AI agent configuration and oversight (see **System Administration > AI Agents**)
- Scorecard weight configuration
- API token management
- Anaplan provisioning

### PSM (Partner Success Manager)

PSMs are the primary users of the platform. They can:

- View and manage their assigned partners
- Create and track tasks, action plans, and goals
- Conduct QBR sessions and path reviews
- Access analytics and benchmarking for their portfolio
- Register partners for enablement events
- Use AI-powered partner insights and content generation (when AI Agents is enabled)

### Manager

Managers have read access across multiple PSM portfolios for oversight and reporting.

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

Admins can impersonate a portal contact to see the portal from their perspective. This is useful for troubleshooting and support. The impersonation state is visible in the UI and can be exited at any time.

> [!NOTE]
> Impersonation is logged and only available to admin users. It does not grant the admin the ability to perform actions as the contact — it only provides a read-only view of what the contact would see.

## Feature-Level Gating

In addition to role-based access, individual features are gated by feature flags that are activated through the Feature Activation system. Each feature has separate admin and end-user flags:

| Feature | Admin Flag | End-User Flag |
|---------|-----------|---------------|
| Enablement Events | `enablement_events_admin` | `enablement_events_user` |
| Workshop Labs | `workshop_labs_admin` | `workshop_labs_user` |
| AI Agents | `ai_agents_admin` | `ai_agents_user` |

Admin flags control access to creation and configuration screens. End-user flags control access to interactive features such as AI-powered content generation and partner insights. See [Feature Activation](provisioning.md) for how flags are progressively unlocked through activation paths.
