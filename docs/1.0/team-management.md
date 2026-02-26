# Team Management

> [!NOTE]
> Available to: Administrator, CoE, Manager (limited)

Team Management provides tools for managing users, organizational hierarchy, and partner assignments. It is the operational backbone for how people and partners are organized within the platform.

## Team Members

Navigate to **Administration > Team Management** to view the user directory.

### User Directory

The directory shows all users in the system with:

- Name, email, and avatar
- Role (Administrator, CoE, PSM, Manager, Integration)
- Manager assignment (reporting hierarchy)
- Active/inactive status
- Whether the user was imported via [Directory Sync](directory-sync)

### Creating Users

1. Click **Create User**
2. Enter:
   - **Name** and **Email**
   - **Password** (or generate an invitation)
   - **Role** — select the appropriate role
   - **Manager** — set the reporting relationship
   - **Avatar** — optional photo upload

### Editing Users

Click any user to edit their profile, change their role, update their manager, or reset their password.

> [!NOTE]
> Users imported through Directory Sync have protected fields that cannot be manually edited. These fields are managed by the directory source. See [Directory Sync](directory-sync) for details.

### Deactivating Users

Users can be deactivated rather than deleted. Deactivated users cannot log in but their historical data (tasks, reviews, assignments) is preserved.

> [!WARNING]
> A user cannot be deleted if they have partners assigned to them. Reassign their partners first, then deactivate the account.

## Reporting Hierarchy

The reporting hierarchy defines manager-to-report relationships across the organization:

- Each user can have one manager
- The hierarchy is displayed as a visual org chart
- Managers automatically gain oversight of their reports' partner portfolios

### Setting Hierarchy

When creating or editing a user, select their **Manager** from the dropdown. This builds the hierarchy automatically.

### How Hierarchy Affects Access

| Role | Hierarchy Impact |
|------|-----------------|
| **Manager** | Sees partners assigned to their direct reports |
| **Administrator** | Sees all partners regardless of hierarchy |
| **PSM** | Sees only their own assigned partners |

## Partner Assignments

Partner assignments determine which PSMs are responsible for which partners:

- View current assignments from the Team Management section or individual partner profiles
- Reassign partners between PSMs when team structures change
- CoE users can view assignments across the organization

> [!TIP]
> When a PSM leaves or changes roles, use the partner reassignment workflow to transfer their portfolio cleanly. This preserves all historical data while updating the active ownership.
