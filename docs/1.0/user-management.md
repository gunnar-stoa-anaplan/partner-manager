# User Management

User Management allows administrators to create and manage both internal users (PSMs, admins) and portal contacts (partner users).

## Internal Users

### Creating a User

1. Navigate to **Administration > User Management**
2. Click **New User**
3. Enter name, email, and role (Admin, PSM, or Manager)
4. Click **Save**

The user will receive an email invitation to set their password.

### User Roles

| Role | Access Level |
|------|-------------|
| **Admin** | Full access to all features and configuration |
| **PSM** | Access to assigned partners and their data |
| **Manager** | Read access across multiple PSM portfolios |

### Partner Assignment

PSMs are assigned to partners. A PSM can only see and manage the partners assigned to them. Admins can reassign partners between PSMs.

## Portal Contacts

### Enabling Portal Access

1. Find the contact under the partner's contact directory
2. Click **Edit**
3. Toggle **Portal Access** on
4. Select a **Portal Role** (Standard, Team Lead, Mentor, Coordinator)
5. Save

<!-- ![Portal access toggle](images/portal-access.png) -->

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

Users and portal contacts can be deactivated without deleting their data. Deactivated users cannot log in but their historical activity is preserved.
