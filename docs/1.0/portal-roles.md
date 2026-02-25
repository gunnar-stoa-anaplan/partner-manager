# Portal Roles

Portal roles control what each partner contact can see and do within the Partner Portal. Roles are capability-based — each role unlocks specific features.

## Available Roles

### Standard Contact

The default role for all portal users:

- View scorecard and goals
- Browse and complete blueprints
- Register for enablement events (requires `enablement_events_user`)
- Submit deal registrations
- View project status
- Participate in labs (requires `workshop_labs_user`)

### Team Lead

All standard capabilities, plus:

- View team member progress across blueprints
- Access team-level analytics
- Manage contact information for their organization

### Mentor

All standard capabilities, plus:

- Access mentoring dashboards
- View peer comparison data
- Track mentee progress

### Coordinator

All standard capabilities, plus:

- Manage event registrations for their organization (requires `enablement_events_user`)
- Bulk import contacts
- Coordinate lab participation for their team (requires `workshop_labs_user`)

## Assigning Roles

1. Navigate to **Administration > User Management**
2. Find the contact
3. Enable **Portal Access**
4. Select the appropriate **Portal Role**
5. Save

<!-- ![Portal role assignment](images/portal-role-assignment.png) -->

> [!WARNING]
> Enabling portal access sends an invitation to the contact with instructions to set up their password. Make sure the contact's email address is correct before enabling access.

## Custom Capabilities

Beyond roles, individual capabilities can be toggled per contact using the `portal.can` middleware system. This allows fine-grained control for edge cases where a contact needs a specific capability that doesn't match a standard role.

## Impersonation

Admins can impersonate any portal contact to see the portal from their perspective. This is useful for troubleshooting and verifying that the correct content is visible.

Navigate to the contact's profile and click **Impersonate** to enter their portal view. Click **Exit Impersonation** to return to the admin view.
