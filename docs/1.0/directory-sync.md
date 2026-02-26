# Directory Sync

> [!NOTE]
> Available to: Administrator. Requires the `directory_sync_admin` feature flag to be active.

Directory Sync enables hybrid user management by importing users from external directory systems (Active Directory, Okta, or any system that can export user data). This ensures Partner Manager's user directory stays in sync with your organization's authoritative identity source.

## How It Works

Directory Sync uses a **CSV-based import** approach:

1. Export users from your directory system (AD, Okta, HR system, etc.) as a CSV file
2. Upload the CSV to Partner Manager
3. The system creates new users or updates existing ones based on email matching
4. Synced fields are marked as **protected** — they can only be updated via subsequent directory imports, not manually

This hybrid approach works with any directory system that can produce a CSV export, without requiring direct API integration.

## Setting Up Directory Sync

### First-Time Setup

1. Navigate to **Administration > Directory Sync**
2. Review the expected CSV format:
   - `name` — Full name
   - `email` — Email address (used as the unique identifier)
   - `role` — Partner Manager role (admin, coe, psm, manager, integration)
   - `manager_email` — Email of the user's manager (for reporting hierarchy)
   - Additional fields as needed
3. Export a test file from your directory system
4. Upload and review the preview before committing

### Running a Sync

1. Navigate to **Administration > Directory Sync**
2. Click **Upload CSV**
3. Select your exported CSV file
4. Review the preview:
   - **New users** — accounts that will be created
   - **Updated users** — existing accounts with changed data
   - **Unchanged** — accounts that match current data
5. Confirm to execute the import
6. Monitor progress on the sync job status page

## Protected Fields

When a user is imported via Directory Sync, certain fields become **protected**:

- Name, email, role, and manager relationship are managed by the directory source
- These fields display a sync indicator in the user edit form
- Manual edits to protected fields are blocked — the next sync would overwrite them anyway

Fields not present in the CSV (such as avatar or custom notes) remain manually editable.

## Sync History

Each sync run is logged with:

- Upload date and administrator who initiated it
- Rows processed: created, updated, unchanged, and errored
- Error details for any rows that failed (e.g. invalid role, missing email)

## Best Practices

- **Schedule regular exports** from your directory system and upload them periodically to keep Partner Manager in sync
- **Start with a small test file** containing a few users to validate the mapping before importing your full directory
- **Use manager_email** to automatically build the [reporting hierarchy](team-management) during import
- **Review the error log** after each sync to catch data quality issues in the source export

> [!TIP]
> Directory Sync complements manual user management — you can have a mix of synced and manually-created users. Synced users are identified by a badge in the user directory.
