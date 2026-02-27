# Licensing

Partner Manager is licensed per-instance. Each client deployment requires an active license that defines usage limits, enabled features, and billing terms.

## License Types

| Type | Metric | Best For |
|------|--------|----------|
| **Partner Count** | Number of active partners per month | Most clients — scales with portfolio size |
| **Named Users** | Number of named user accounts | Clients who manage many partners with few users |
| **Feature Tier** | Feature bundle (Starter / Growth / Professional / Enterprise) | Clients who want simplicity |
| **Unlimited** | No usage limits | Large enterprise agreements |

## Pricing Tiers

Published pricing is transparent and available to all clients. Current rates (self-hosted):

| | Starter | Growth | Professional | Enterprise |
|---|---------|--------|-------------|-----------|
| **Partners** | Up to 25 | Up to 50 | Up to 150 | Unlimited |
| **Users** | Up to 10 | Up to 25 | Up to 75 | Unlimited |
| **Monthly** | $349/mo | $599/mo | $999/mo | Contact us |
| **Annual** | $3,490/yr | $5,990/yr | $9,990/yr | Contact us |
| **Savings** | ~17% (2 months free) | ~17% (2 months free) | ~17% (2 months free) | Negotiated |

Enterprise pricing is negotiated based on scope and includes a dedicated support tier.

For managed service pricing, add the infrastructure fee listed in [Deployment Options](deployment-options.md).

### Multi-Year Discounts

| Term | Additional Discount | Price-Lock |
|------|--------------------:|:----------:|
| 2-year | 5% off annual rate | Yes |
| 3-year | 10% off annual rate | Yes |

Multi-year agreements include a **price-lock guarantee** — the rate at sign-up is honored for the full term, even if published pricing increases.

## Features by Tier

| Feature | Starter | Growth | Professional | Enterprise |
|---------|:-------:|:------:|:------------:|:----------:|
| Partner management | Yes | Yes | Yes | Yes |
| Pipeline tracking | Yes | Yes | Yes | Yes |
| Task board | Yes | Yes | Yes | Yes |
| Deal registration | Yes | Yes | Yes | Yes |
| Analytics & dashboards | — | Yes | Yes | Yes |
| QBR workbench | — | — | Yes | Yes |
| Enablement events | — | — | Yes | Yes |
| Workshop labs | — | — | Yes | Yes |
| Competency matrix | — | — | Yes | Yes |
| AI agents | — | — | — | Yes |
| Benchmarking | — | — | — | Yes |
| Custom integrations | — | — | — | Yes |
| Dedicated support | — | — | — | Yes |

## Billing Options

Clients choose the billing arrangement that suits them best:

### Monthly (Credit Card)

- Self-serve sign-up
- Billed monthly to a credit card on file
- Cancel or change tier at any time
- No long-term commitment

### Annual (Invoice)

- Billed annually via invoice
- ~17% discount vs. monthly pricing (2 months free)
- Net-30 payment terms
- Auto-renews unless cancelled 30 days before expiry

### Multi-Year (Purchase Order)

- 2 or 3 year terms available
- Billed via purchase order
- **Price-lock guarantee** — the rate at sign-up is honored for the full term, even if published pricing increases
- Additional 5% (2-year) or 10% (3-year) discount off the annual rate
- Custom payment schedules (annual, semi-annual, or upfront)
- Volume discounts available for multiple instances

## Usage Tracking

Usage metrics are collected automatically through the agent heartbeat pipeline:

1. The **PM Agent** sidecar calls the application's internal metrics endpoint every heartbeat cycle
2. The agent includes `partner_count` and `user_count` in its heartbeat payload to the control plane
3. The **control plane** records monthly usage snapshots, tracking both current and peak values
4. License limits are evaluated on every heartbeat — warnings are returned to the instance

### Usage Warnings

The system issues warnings at progressive thresholds:

| Condition | Warning | Severity |
|-----------|---------|----------|
| Partner count > 80% of limit | `approaching_partner_limit` | Info |
| User count > 80% of limit | `approaching_user_limit` | Info |
| Partner count exceeds limit | `over_partner_limit` | Danger |
| User count exceeds limit | `over_user_limit` | Danger |
| License expires in < 30 days | `expiring_soon` | Warning |
| License expired, in grace period | `grace_period` | Warning |
| License expired, past grace period | `expired` | Danger |

Warnings appear as banners in the Partner Manager UI and are visible in the control plane fleet dashboard.

## Enforcement Model

License enforcement uses a **soft-limit** approach:

- **Active** — All features available, usage within limits
- **Warning** — Approaching limits or nearing expiry; banners shown but no restrictions
- **Grace Period** — License has expired but a configurable grace window (default 14 days) allows continued operation
- **Expired** — Past grace period; banners become persistent

The application continues to function during warning and grace periods. This ensures clients are never locked out abruptly.

## License Lifecycle

```
┌─────────┐    ┌──────────┐    ┌──────────────┐    ┌─────────┐
│  Active  │───>│  Warning  │───>│ Grace Period  │───>│ Expired │
└─────────┘    └──────────┘    └──────────────┘    └─────────┘
     │              │                                     │
     └──────────────┴── Renew ──────────────────────>─────┘
                                                    Back to Active
```

## Price Schedules

Published pricing is versioned through **price schedules**. When pricing changes:

- A new price schedule is created with an effective date
- Existing clients on term agreements keep their locked-in rate
- Only new agreements or renewals use the current schedule
- Historical pricing is preserved for audit and billing accuracy
