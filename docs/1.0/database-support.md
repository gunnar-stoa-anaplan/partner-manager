# Database Support

Partner Manager is designed to run on multiple relational database engines. The application uses a database abstraction layer and a cross-database portability helper to ensure queries work consistently across supported engines.

## Supported Engines

| Engine | Minimum Version | Status | Notes |
|--------|:--------------:|:------:|-------|
| MySQL | 8.0+ | Fully supported | Default for new deployments |
| PostgreSQL | 15+ | Fully supported | Required for knowledge base |
| Oracle | 19c+ | Supported | Requires OCI8 driver package |
| SQL Server | 2019+ | Supported | Via built-in `sqlsrv` driver |
| SQLite | 3.35+ | Supported | Best for development and testing |

## Configuration

Set the database engine via the `DB_CONNECTION` environment variable:

```env
DB_CONNECTION=mysql      # MySQL (default)
DB_CONNECTION=pgsql      # PostgreSQL
DB_CONNECTION=oracle     # Oracle (requires OCI8 driver)
DB_CONNECTION=sqlsrv     # SQL Server
DB_CONNECTION=sqlite     # SQLite
```

Each engine has its own configuration block in the database configuration file. Connection credentials are set via their respective environment variables (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## Knowledge Base Connection

The AI knowledge base always requires a PostgreSQL database, regardless of which engine the application uses. This is because the knowledge base relies on the `pgvector` extension for vector similarity search.

The knowledge base connection is configured separately via the `KNOWLEDGE_BASE_*` environment variables.

> [!NOTE]
> If you are deploying on PostgreSQL, you can point both the default and knowledge base connections at the same PostgreSQL instance (in separate schemas or databases).

## Engine-Specific Notes

### MySQL

MySQL is the default and most tested engine. All features work out of the box.

- Requires `utf8mb4` character set for full Unicode support
- InnoDB is the required storage engine (default in MySQL 8.0+)
- The admin dashboard's "Database & System Health" section displays MySQL-specific metrics (buffer pool, InnoDB stats). These metrics are not available on other engines.

### PostgreSQL

PostgreSQL is a strong choice for production deployments, especially when the knowledge base runs on the same server.

- Supports all application features
- Case-sensitive string comparisons by default (MySQL is case-insensitive). The application handles this correctly, but custom queries should use `ILIKE` instead of `LIKE` for case-insensitive searches.
- JSON column support is native and performant

### Oracle

Oracle Database is supported via a community OCI8 driver package.

- Requires the OCI8 extension and Oracle Instant Client on the server
- Tested with Oracle 19c and Oracle 21c
- Column/table identifiers longer than 30 characters may require Oracle 12c+ (128-char limit)
- Boolean columns are stored as `NUMBER(1)` — the driver handles the mapping automatically
- The portability helper uses `TO_CHAR` for date formatting and `TRUNC` for date extraction

> [!NOTE]
> Oracle licensing is managed separately by the client. Partner Manager does not include or distribute any Oracle software.

### SQL Server

SQL Server is supported via the built-in `sqlsrv` driver.

- Requires the `sqlsrv` and `pdo_sqlsrv` extensions on the server
- Tested with SQL Server 2019 and Azure SQL Database

### SQLite

SQLite is ideal for local development, CI pipelines, and automated testing.

- No separate server process required
- Limited concurrency — not recommended for production deployments with multiple users
- Some aggregate functions behave slightly differently (e.g., `GROUP BY` strictness)

## Portability Architecture

The application avoids database-specific SQL by using:

1. **ORM abstraction** — All standard CRUD operations use the query builder and model layer, which generate engine-appropriate SQL automatically.

2. **Cross-database portability helper** — A helper class generates engine-specific SQL fragments for operations that differ between databases:

   | Operation | Purpose |
   |-----------|---------|
   | Custom ordering | Ordering via `CASE WHEN` (replaces MySQL's `FIELD()`) |
   | Date formatting | `DATE_FORMAT` / `TO_CHAR` / `strftime` depending on engine |
   | Date extraction | `DATE()` / `CAST AS DATE` / `TRUNC` depending on engine |
   | Year-week grouping | `YEARWEEK` / `TO_CHAR` / `strftime` depending on engine |
   | Minimum of values | `LEAST` / `MIN` depending on engine |

3. **Engine-agnostic foreign key handling** — Seeders use a cross-database method to temporarily disable foreign key constraints, instead of MySQL-specific statements.

4. **String columns instead of ENUM** — All status and type columns use `VARCHAR(50)` instead of `ENUM` for full cross-database compatibility. Validation is handled at the application layer.

## Schema Guidelines

All database schemas are engine-agnostic. When extending the schema:

- Use `VARCHAR(50)` instead of `ENUM` for status and type columns
- Avoid raw SQL in schema changes — use the built-in schema builder
- Test schema changes against SQLite (the CI pipeline does this automatically)

## Further Reading

- [Deployment Options](deployment-options) — Self-hosted vs managed service
- [Deployment](deployment) — Step-by-step deployment guide
- [Architecture](architecture) — System architecture overview
