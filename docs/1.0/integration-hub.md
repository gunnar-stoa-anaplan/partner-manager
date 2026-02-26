# Integration Hub

> [!NOTE]
> Available to: Integration, Administrator

The Integration Hub provides multi-connector data synchronization between Partner Manager and external systems. It handles connector configuration, field mapping, and automated import/export job management.

## Dashboard

The Integration Hub Dashboard shows:

- **Connection count** and status (active, inactive, error)
- **Recent jobs** with success/failure indicators
- **Rows processed** — total imported and exported across all jobs
- **Success rate** — percentage of jobs completing without errors

## Connectors

### Connector Catalog

Navigate to **Integration > Integration Hub > Connectors** to browse available connector types. Each connector represents a data source or destination:

| Connector Type | Description |
|---------------|-------------|
| **CSV** | Upload and import CSV files manually |
| **Salesforce** | Connect to Salesforce CRM for opportunity and account sync |
| Additional connectors | Available based on your deployment configuration |

### Setting Up a Connector

1. Select a connector type from the catalog
2. Click **Setup** to configure:
   - **Credentials** — API keys, OAuth tokens, or connection strings
   - **Auto-mapping** — The system can suggest field mappings based on predefined templates
3. Test the connection to verify credentials and access

## Connections

Navigate to **Integration > Integration Hub > Connections** to manage active connections.

- **Create** new connections from configured connectors
- **Test** connectivity to verify the connection is healthy
- **Edit** credentials or configuration
- **Deactivate** connections that are no longer needed

## Field Mappings

Navigate to **Integration > Integration Hub > Field Mappings** to configure how source data maps to Partner Manager's data model.

### Creating a Mapping

1. Select the source connection and target model (e.g. Partners, Opportunities, Resources)
2. Map source fields to target fields:
   - **Direct mapping** — source field maps 1:1 to a target field
   - **Transform** — apply a transformation (e.g. date format conversion, string normalization)
   - **Default value** — set a fallback value when the source field is empty

### Mapping Templates

Pre-built mapping templates provide versioned, reusable configurations for common data models. Templates can be:

- Applied to new connections as a starting point
- Customized per connection
- Updated when the data model changes

## Import Jobs

Navigate to **Integration > Integration Hub > Import Jobs** to manage inbound data flows.

### Running an Import

1. Select a connection and mapping configuration
2. Choose the data scope (full refresh or incremental)
3. Execute the import
4. Monitor progress in real-time: rows read, rows imported, errors

### Job History

Every import job is logged with:

- Start and end time
- Rows processed, created, updated, and skipped
- Error details with row-level diagnostics
- The ability to **retry** failed jobs

## Export Jobs

Configure outbound data flows to push Partner Manager data to external systems:

- Define export scope (which models and fields to include)
- Schedule recurring exports or run on demand
- Monitor export job history

> [!TIP]
> Start with a small test import to validate your field mappings before running a full data load. Review the job error log to catch mapping issues early.
