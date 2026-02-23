# Deployment Options

Partner Manager supports two deployment models. Both deliver the same application with the same features — the difference is who operates the infrastructure.

## Self-Hosted

The client provisions and manages their own infrastructure. Partner Manager runs as Docker containers in the client's datacenter, VPC, or cloud account.

**How it works:**

- Client provides compute (VM or bare metal), a MySQL database, and outbound HTTPS access
- We provide the Docker images, configuration, and TLS certificates
- The PM Agent sidecar handles automatic updates, health monitoring, and license validation
- The control plane monitors all instances remotely via the agent heartbeat

**Responsibilities:**

| Area | Client | Us |
|------|:------:|:--:|
| Compute provisioning | Yes | — |
| Database hosting | Yes | — |
| Network / firewall | Yes | — |
| OS patching | Yes | — |
| Application updates | — | Yes (via agent) |
| Monitoring & alerting | — | Yes (via control plane) |
| License management | — | Yes |
| Support | — | Yes |

**Pricing:** License fee only. See [Licensing](licensing.md) for tiers and rates.

| Tier | Monthly | Annual |
|------|--------:|-------:|
| Starter | $349/mo | $3,490/yr |
| Growth | $599/mo | $5,990/yr |
| Professional | $999/mo | $9,990/yr |
| Enterprise | Custom | Custom |

**Best for:** Clients with strict data residency requirements, existing infrastructure teams, or regulatory constraints that mandate on-premise hosting.

## Managed Service

We provision and operate the infrastructure on behalf of the client. The application runs in our cloud environment with dedicated, isolated resources per client.

**How it works:**

- We provision a dedicated environment (compute, database, networking) for each client
- Infrastructure is fully managed — patching, backups, scaling, and monitoring
- The client accesses Partner Manager through a dedicated URL
- Same agent-based management, same control plane visibility

**Responsibilities:**

| Area | Client | Us |
|------|:------:|:--:|
| Compute provisioning | — | Yes |
| Database hosting | — | Yes |
| Network / firewall | — | Yes |
| OS patching | — | Yes |
| Application updates | — | Yes |
| Monitoring & alerting | — | Yes |
| Backups | — | Yes |
| License management | — | Yes |
| Support | — | Yes |

**Pricing:** License fee + infrastructure fee. The infrastructure fee covers compute, database, networking, backups, and operations.

| Tier | License | Infra Fee | Total Monthly | Total Annual |
|------|--------:|----------:|--------------:|-------------:|
| Starter | $349/mo | +$199/mo | $548/mo | $5,480/yr |
| Growth | $599/mo | +$349/mo | $948/mo | $9,480/yr |
| Professional | $999/mo | +$499/mo | $1,498/mo | $14,980/yr |
| Enterprise | Custom | +$499/mo | Custom | Custom |

Annual pricing includes the same ~17% discount on the license portion. Infrastructure fees are fixed.

**Best for:** Clients who want zero operational overhead, fast onboarding, or do not have internal infrastructure teams.

## Comparison

| | Self-Hosted | Managed Service |
|---|:-----------:|:---------------:|
| **Data location** | Client-controlled | Our cloud (region selectable) |
| **Setup time** | 1-2 days (with client infra ready) | Same day |
| **Operational burden** | Client manages compute & DB | Fully managed |
| **Pricing** | License only | License + infrastructure fee |
| **SLA** | Depends on client infra | 99.9% uptime guarantee |
| **Customization** | Full control over environment | Standard environment |
| **Compliance** | Client controls all data | SOC 2, GDPR compliant |

## How to Choose

- **Choose Self-Hosted** if you need full control over where data lives, have existing infrastructure and ops teams, or have regulatory requirements for on-premise hosting.
- **Choose Managed Service** if you want to get started quickly, prefer not to manage infrastructure, or want a guaranteed SLA without operational overhead.

Both options include the same application features, the same agent-based management, and the same support tiers. You can also migrate between deployment models if your needs change.

## Further Reading

- [Deployment](deployment.md) — Step-by-step self-hosted deployment guide
- [Architecture](architecture.md) — System architecture and component overview
- [Licensing](licensing.md) — License types, pricing, and billing options
