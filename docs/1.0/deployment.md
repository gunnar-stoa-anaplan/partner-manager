# Deployment

This page describes the end-to-end process of deploying Partner Manager into a client's infrastructure — from provisioning through first heartbeat.

## Pre-Requisites

Before deployment, the client must provision three things in their own infrastructure:

| Requirement | Examples | Purpose |
|-------------|----------|---------|
| **Compute host** | EC2 instance, Azure VM, on-prem server | Runs the two Docker containers |
| **Database** | RDS MySQL, Azure SQL, Cloud SQL, self-hosted | Application data storage |
| **Outbound HTTPS** | Firewall rule to `fleet.myservice.com:443` | Agent → control plane communication |

### Compute Host Requirements

| Resource | Minimum | Recommended |
|----------|---------|-------------|
| **CPU** | 2 vCPU | 4 vCPU |
| **Memory** | 2 GB | 4 GB |
| **Disk** | 20 GB | 50 GB |
| **Docker** | 24.0+ | Latest stable |
| **Docker Compose** | v2.20+ | Latest stable |

### Database Requirements

| Property | Requirement |
|----------|-------------|
| **Engines** | MySQL 8.0+, PostgreSQL 15+, SQL Server 2019+ |
| **Storage** | 1 GB minimum, grows with usage |
| **Connectivity** | Accessible from the compute host |

### Network Requirements

| Direction | Destination | Port | Protocol | Purpose |
|-----------|-------------|------|----------|---------|
| **Inbound** | Compute host | 80, 443 | HTTPS | Users accessing the application |
| **Outbound** | `fleet.myservice.com` | 443 | HTTPS (mTLS) | Agent → control plane |
| **Outbound** | `ghcr.io` | 443 | HTTPS | Pulling container images |
| **Internal** | Database host | 3306 / 5432 / 1433 | TCP | App → database |

> [!NOTE]
> The control plane never initiates connections to client infrastructure. Only outbound HTTPS from the agent is required.

## Instance Registration

A MyService operator registers the new client in the control plane before deployment begins.

```
Operator                          Control Plane
   │                                   │
   │  1. Create Instance               │
   │   - client_name: "Acme Corp"      │
   │   - region: "us-east-1"           │
   │                                   │
   │──────────────────────────────────>│
   │                                   │
   │                    2. Generate instance_id (inst_abc123)
   │                    3. Generate mTLS client certificate
   │                       - Signed by CA private key
   │                       - Fingerprint bound to instance_id
   │                       - 1-year validity
   │                                   │
   │<── Deployment package ────────────│
   │   - instance_id                   │
   │   - config.yaml                   │
   │   - ca.pem (CA public cert)       │
   │   - client.pem (client cert)      │
   │   - client-key.pem (private key)  │
   │   - docker-compose.yml            │
```

The operator delivers the deployment package to the client through a secure channel (encrypted file transfer, secure portal, etc.).

### Deployment Package Contents

| File | Purpose | Sensitive? |
|------|---------|------------|
| `docker-compose.yml` | Container orchestration | No |
| `config.yaml` | Agent configuration (instance ID, control plane URL, intervals) | No |
| `ca.pem` | CA public certificate — verifies the control plane's identity | No |
| `client.pem` | Client certificate — identifies this instance to the control plane | No |
| `client-key.pem` | Client private key — proves ownership of the certificate | **Yes** |

> [!WARNING]
> The `client-key.pem` file is the only sensitive item in the deployment package. It must be transmitted securely and stored with restrictive file permissions (0400). If compromised, the operator should immediately revoke the certificate from the control plane.

## File Layout

The client places the deployment files on the compute host:

```
/opt/partner-manager/
├── docker-compose.yml          # Container orchestration
├── .env                        # Database credentials + APP_KEY
├── config.yaml                 # Agent configuration
└── certs/
    ├── ca.pem                  # CA certificate (verifies control plane)
    ├── client.pem              # Client certificate (identifies this instance)
    └── client-key.pem          # Client private key (never transmitted)
```

## Configuration

### Application Environment (`.env`)

The client creates a `.env` file with their own database credentials and application key. These values are **never** shared with MyService or transmitted to the control plane.

```
# Application
APP_ENV=production
APP_KEY=base64:...

# Database (client-managed)
DB_CONNECTION=mysql
DB_HOST=mydb.abc123.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=partner_manager
DB_USERNAME=pm_app
DB_PASSWORD=client-secret-password

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=notifications@example.com
MAIL_PASSWORD=mail-password
```

> [!IMPORTANT]
> Generate `APP_KEY` locally on the client host. This key encrypts session data and any application-layer encrypted fields. It must never leave the client's infrastructure.

### Agent Configuration (`config.yaml`)

This file is provided in the deployment package with values pre-filled by the operator:

```yaml
# /opt/partner-manager/config.yaml

instance_id: "inst_abc123"
control_plane_url: "https://fleet.myservice.com"
heartbeat_interval: "30s"

tls:
  ca_cert_path: "/etc/pm-agent/certs/ca.pem"
  client_cert_path: "/etc/pm-agent/certs/client.pem"
  client_key_path: "/etc/pm-agent/certs/client-key.pem"

docker:
  host: "unix:///var/run/docker.sock"
  app_container: "partner-manager-app-1"
```

### Docker Compose (`docker-compose.yml`)

```yaml
services:
  app:
    image: ghcr.io/myservice/partner-manager:latest
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    environment:
      APP_ENV: production
      APP_KEY: "${APP_KEY}"
      DB_CONNECTION: "${DB_CONNECTION:-mysql}"
      DB_HOST: "${DB_HOST}"
      DB_PORT: "${DB_PORT:-3306}"
      DB_DATABASE: "${DB_DATABASE}"
      DB_USERNAME: "${DB_USERNAME}"
      DB_PASSWORD: "${DB_PASSWORD}"
    volumes:
      - app-storage:/var/www/html/storage
      - agent-status:/var/lib/pm-agent/status

  agent:
    image: ghcr.io/myservice/pm-agent:latest
    restart: unless-stopped
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./config.yaml:/etc/pm-agent/config.yaml:ro
      - ./certs:/etc/pm-agent/certs:ro
      - agent-status:/var/lib/pm-agent/status
    depends_on:
      - app

volumes:
  app-storage:
  agent-status:
```

The `agent-status` volume is shared between both containers. The agent writes version-check information to it, and the app reads it to show update notifications to administrators. When the admin accepts an update, the app writes an acceptance file that the agent picks up on its next heartbeat cycle.

## Launch

With all files in place, deployment is a single command:

```bash
cd /opt/partner-manager
docker compose up -d
```

### What Happens on First Boot

```
docker compose up -d
        │
        ├── Pull ghcr.io/myservice/partner-manager:latest
        ├── Pull ghcr.io/myservice/pm-agent:latest
        │
        ├── Start app container
        │     │
        │     ├── Connect to client database
        │     ├── Run migrations (creates tables on first boot)
        │     └── Begin serving on ports 80 and 443
        │
        └── Start agent container (after app)
              │
              ├── Load config.yaml
              ├── Initialize mTLS client with certs/
              └── Enter heartbeat loop (every 30 seconds)
```

The app container runs database migrations automatically on first start, creating all required tables. No manual migration step is needed.

## First Heartbeat

Within 30 seconds of starting, the agent sends its first heartbeat to the control plane:

```
Agent                                          Control Plane
  │                                                 │
  │── POST /api/v1/agent/heartbeat (mTLS) ────────>│
  │   {                                             │
  │     "instance_id": "inst_abc123",               │
  │     "health": {                                 │
  │       "agent_version": "1.2.0",                 │
  │       "app_reachable": true,                    │
  │       "containers": [                           │
  │         { "name": "partner-manager-app-1",      │
  │           "image": "partner-manager:2.1.0",     │
  │           "state": "running" }                  │
  │       ]                                         │
  │     }                                           │
  │   }                                             │
  │                                                 │
  │                          Control plane verifies: │
  │                          ✓ Client cert valid     │
  │                          ✓ Fingerprint matches   │
  │                            inst_abc123           │
  │                          ✓ Store heartbeat       │
  │                          ✓ Set status: Online    │
  │                                                 │
  │<── 200 { "commands": [] } ─────────────────────│
```

Once the control plane receives a valid heartbeat, the instance appears on the fleet dashboard as **Online**.

## Verification

### Client-Side Checks

| Check | How | Expected Result |
|-------|-----|-----------------|
| Containers running | `docker compose ps` | Both `app` and `agent` show `running` |
| App accessible | Open `https://your-domain.com` in a browser | Login page loads |
| Database connected | App login page loads without errors | Connection successful |
| Agent logs clean | `docker compose logs agent --tail=20` | Heartbeat sent successfully |

### Operator-Side Checks

The MyService operator verifies the instance appears on the fleet dashboard:

```
┌────────────────────────────────────────────────────────┐
│  Fleet Dashboard                                        │
│                                                        │
│  Instance       Client      Version   Status   Last HB │
│  ─────────────  ──────────  ────────  ───────  ─────── │
│  inst_abc123    Acme Corp   2.1.0     Online   12s ago  │
│  inst_def456    Contoso     2.1.0     Online   4s ago   │
│  inst_ghi789    Fabrikam    2.0.1     Stale    3m ago   │
└────────────────────────────────────────────────────────┘
```

## Troubleshooting

### App Container Won't Start

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| `Connection refused` in logs | Database host unreachable | Verify `DB_HOST` and network connectivity |
| `Access denied` in logs | Wrong database credentials | Check `DB_USERNAME` and `DB_PASSWORD` in `.env` |
| `Unknown database` in logs | Database not created | Create the database on the database server |

### Agent Can't Reach Control Plane

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| `connection timed out` | Firewall blocking outbound HTTPS | Allow outbound 443 to `fleet.myservice.com` |
| `certificate verify failed` | Wrong CA cert or expired cert | Verify `ca.pem` matches the control plane's CA |
| `tls: bad certificate` | Client cert not signed by CA | Request new certificates from the operator |
| `certificate has expired` | Client cert past validity | Agent should auto-renew; if not, request new cert |

### Instance Shows "Offline" on Dashboard

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| Never appeared | Agent never connected | Check agent logs for TLS or network errors |
| Was online, now offline | Agent container crashed | Check `docker compose ps`; restart if needed |
| Was online, now stale | Network interruption | Verify outbound connectivity to control plane |

## Updating

When a new version is released, the operator triggers a deployment from the control plane:

```
Operator                      Control Plane                   Agent
   │                               │                            │
   │  1. Click "Deploy v2.2.0"     │                            │
   │──────────────────────────────>│                            │
   │                               │  2. Create Deployment      │
   │                               │  3. Queue deploy command   │
   │                               │                            │
   │                               │  (next heartbeat, ≤30s)    │
   │                               │<── heartbeat ──────────────│
   │                               │── commands: [deploy] ─────>│
   │                               │                            │
   │                               │         4. Verify signature│
   │                               │         5. Pull new image  │
   │                               │         6. Restart app     │
   │                               │                            │
   │                               │<── command report ─────────│
   │                               │  7. Update status          │
   │                               │                            │
   │  8. Dashboard: "Deployed"     │                            │
   │<──────────────────────────────│                            │
```

No manual intervention is required on the client host. The agent handles the entire update process, including cryptographic verification of the new image before applying it.

## Responsibility Summary

| Step | Responsible Party | Details |
|------|-------------------|---------|
| Provision compute + database | **Client** | Their infrastructure, their credentials |
| Register instance + issue certs | **MyService operator** | Creates identity in control plane |
| Deliver deployment package | **MyService → Client** | Secure handoff of config + certs |
| Configure `.env` with DB credentials | **Client** | MyService never sees these values |
| `docker compose up -d` | **Client** | Two containers start, app runs migrations |
| Verify connectivity | **Both** | Operator checks dashboard, client checks app |
| Ongoing updates | **MyService operator** | Triggered remotely via control plane |
| Database backups + maintenance | **Client** | Their database, their responsibility |
| Certificate renewal | **Automatic** | Agent auto-renews 30 days before expiry |
