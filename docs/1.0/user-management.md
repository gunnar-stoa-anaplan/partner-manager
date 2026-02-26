# User Management

> [!NOTE]
> Available to: Administrator

User Management allows administrators to create and manage internal users and portal contacts. For team-level views and organizational hierarchy, see [Team Management](team-management). For bulk user imports from a directory system, see [Directory Sync](directory-sync).

## Internal Users

### Creating a User

1. Navigate to **Administration > User Management**
2. Click **New User**
3. Enter name, email, and select a role
4. Optionally set a manager for reporting hierarchy
5. Click **Save**

The user will receive an email invitation to set their password.

### User Roles

| Role | Access Level |
|------|-------------|
| **Administrator** | Full access to all features, configuration, and system administration |
| **CoE** | Strategy design, enablement programs, knowledge base, AI agents, workshops, branding |
| **PSM** | Partner management, deal registration, analytics for assigned partners |
| **Manager** | All PSM capabilities plus team oversight and reporting hierarchy access |
| **Integration** | Integration Hub access only — connectors, field mappings, import/export jobs |

See [Roles & Permissions](roles-and-permissions) for a detailed breakdown of each role's capabilities.

### Partner Assignment

PSMs are assigned to partners. A PSM can only see and manage the partners assigned to them. Administrators can reassign partners between PSMs from the partner profile or the [Team Management](team-management) view.

### Directory-Synced Users

Users imported through [Directory Sync](directory-sync) are marked with a sync indicator. Their core fields (name, email, role, manager) are protected and can only be updated through subsequent directory imports. Other fields (avatar, notes) remain manually editable.

## Portal Contacts

### Enabling Portal Access

1. Find the contact under the partner's contact directory
2. Click **Edit**
3. Toggle **Portal Access** on
4. Select a **Portal Role** (Standard, Team Lead, Mentor, Coordinator)
5. Save

> [!WARNING]
> Enabling portal access sends a credential setup email to the contact. Verify the email address is correct before enabling.

### Bulk Import

Contacts can be imported in bulk via CSV:

1. Click **Import Contacts**
2. Download the template CSV
3. Fill in the contact details
4. Upload the completed file
5. Review and confirm the import

## Deactivating Users

Users and portal contacts can be deactivated without deleting their data. Deactivated users cannot log in but their historical activity (tasks, reviews, assignments) is preserved.

> [!WARNING]
> A user cannot be deleted if they have partners assigned to them. Reassign their partners first, then deactivate the account.
