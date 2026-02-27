# Licensing Roadmap — Future Considerations

## High Impact

- **Feature gating in PM3** — Middleware/gate that reads license features and blocks access to gated routes (analytics, QBR, enablement). Makes tier pricing meaningful.
- **Trial licenses** — 14-day trial with Professional features, low partner cap (10). Needs `trial` status and auto-expiry logic.
- **Stale license policy** — Define how long a stale `license-status.json` is trusted (24h? 7d?). Fall back to restricted mode after threshold.
- **Renewal workflow** — Email alerts at 60/30/14 days before expiry. Self-serve renewal endpoint or Artisan command for manual follow-up.

## Medium Impact

- **Usage reporting for billing** — Monthly usage summaries (peaks, averages, trends) for invoice/PO clients. CSV export from fleet dashboard.
- **Overage handling** — Define policy: auto-upgrade, per-partner overage billing, hard block on new partners, or flag for sales.
- **License audit log** — `license_events` table tracking creates, renewals, tier changes, limit adjustments with `performed_by` and old/new values.
- **Billing system integration** — Stripe for monthly credit card. PriceSchedule tiers map to Stripe Products/Prices. Invoice tracking for annual/PO clients.

## Lower Priority

- **Volume discounts** — `organization` model grouping instances for cross-instance bundle pricing.
- **License portability** — Transfer mechanism to re-bind a license when a client migrates infrastructure.
- **Self-serve portal** — Client-facing page for license, usage, invoices, and tier changes (separate from fleet dashboard).
- **Webhook notifications** — Push alerts to Slack/email/PagerDuty on warning thresholds.
