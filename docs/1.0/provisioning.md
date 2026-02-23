# Provisioning

The Provisioning section manages Anaplan workspace assignments for partners — connecting partners to the Anaplan environments they need for planning and analytics.

## Anaplan Integration

Partner Manager integrates with Anaplan to provision and manage workspaces. The integration supports multiple Anaplan environments (e.g., IFP, OWP, SPM).

## Viewing Workspaces

Navigate to **Administration > Provisioning** to see all configured Anaplan workspaces and their partner assignments.

<!-- ![Provisioning view](images/provisioning.png) -->

## Assigning Workspaces

1. Select a workspace
2. Click **Assign Partner**
3. Select the partner to assign
4. Confirm the assignment

## Workspace Details

Each workspace entry includes:

- Workspace name and ID
- Anaplan environment (IFP, OWP, SPM)
- Assigned partners
- Models and modules available
- Version configuration

## Anaplan API Connection

Partner Manager connects to Anaplan's REST API for:

- Listing available workspaces and models
- Retrieving module and list metadata
- Synchronizing workspace assignments

> [!IMPORTANT]
> Anaplan API credentials are configured per environment via environment variables. Contact your administrator to set up or rotate Anaplan credentials.

## Anaplan Overview

The Anaplan Overview page provides a read-only dashboard of all Anaplan workspace activity across your partner portfolio, including workspace utilization and model access patterns.
