# Application Security Overview

Prepared for an infosec review of Partner Manager. Pairs with ARCHITECTURE.md
(§3 authorization, §9 risks, §11 tenancy) and OPERATING_MODEL.md.

## 1. What the system is / deployment model

Single-tenant, white-label platform sold per client. **Each client runs its own
Laravel instance + its own PostgreSQL** (client-hosted or we-host on AWS). Tenant
isolation is therefore **physical**, not row-based — the strongest separation and
a selling point. There is no shared cross-client database and no cross-client
identity.

One Postgres instance per deployment hosts three databases: `partner_manager`
(app + `wh` analytics schema) and `knowledge_base` (pgvector). Session, cache,
and queue all use the database driver.

## 2. Identity & authentication

- Two audiences, two guards: internal **users** (`web`) and external partner
  **contacts** (`portal`).
- Email + password (`laravel/ui`). **Gap: no MFA/2FA and no SSO.** Remediation:
  `laravel/fortify` (TOTP 2FA) and `laravel/socialite`/SAML for SSO.
- **Gap: seeded demo accounts use the password `password`.** Dev-only; must never
  reach a client instance. Remediation: force-rotate/disable seeded accounts on
  provisioning.

## 3. Authorization (defense in depth)

Three layers, all enforced server-side:

1. **Module access** — custom RBAC. People hold one *or more* roles
   (`role_user` / `role_contact` pivots); permission checks **union across all
   roles**. `EnsureModuleEnabled` (internal) and `portal.can` (partner) gate
   features; `RedirectRestrictedRoles` confines limited roles.
2. **Record authorization** — Laravel Policies (e.g. `PartnerPolicy`): module
   permission + ownership; `Gate::before` short-circuits bypass/system roles.
3. **Row-level visibility** — `VisibilityContext` resolves each actor's visible
   partner scope across both guards; the `ScopedToActor` global scope filters
   queries **fail-closed** (out-of-scope records are invisible → 404, not 403).
   The audited-metric drill endpoint enforces the same scope, so an audit trail
   can never return rows the user may not see.

Teams/functions (Sales, Marketing, Service, Implementation) span both internal
and partner people and are the collaboration/visibility axis.

## 4. Data classification & PII

- **PII:** partner people — names, emails, countries, roles.
- **Commercially sensitive:** opportunity ACV, accounts, pipeline, deal
  registrations.
- **Analytics:** audited weekly snapshots in schema `wh`, every row carrying
  provenance (`snapshot_date`, `load_run_id`, `source_file`, `source_id`).
- **Knowledge base:** documents + vector embeddings (may contain sensitive text).

Minimize exposure per audience via the row-level layer (§3). Partners only ever
see their own company's slice.

## 5. Secrets & integrations

- Integrations: Salesforce (OAuth), Zoom (OAuth + webhooks, signed), GitHub
  (token), Anaplan (token). Webhook secrets in config/services.
- Secrets live in `.env` (gitignored). **Remediation:** move to a secrets manager
  (AWS Secrets Manager / SSM Parameter Store), rotate on a schedule, and scope
  per instance — never shared across clients.
- `MAIL_MAILER` and `BROADCAST` default to `log`. **Configure a real mailer**
  before any user-facing email in a client instance (config is env-driven and
  ready; only provider credentials are needed).

## 6. Auditability

- Model-level `audit_logs` + observers for sensitive content (workshops).
- The metric layer makes every number traceable: the drill returns the exact
  source rows, the SQL, and provenance. This is a strong story for a data-trust
  review.
- **Impersonation** exists (internal → partner). Treat as high-privilege; ensure
  every impersonation is audited.

## 7. Licensing / entitlement

Per-instance licensing is handled by a companion agent that writes
`license-status.json` (tier, features, expiry, grace period), read by
`LicenseStatusService`. Enhancement path: verify the license signature in-app and
drive `laravel/pennant` edition flags from it.

## 8. Known gaps & remediations (summary)

| Gap | Remediation | Priority |
|-----|-------------|----------|
| No MFA / SSO | Fortify (2FA), Socialite/SAML | High |
| Seeded `password` accounts | Rotate/disable on provisioning | High |
| Secrets in `.env` | Secrets manager + rotation | High |
| Mail/broadcast on `log` | Real mailer per instance | Medium |
| Impersonation | Ensure fully audited | Medium |
| Row-level rollout | Extend Policies/`ScopedToActor` beyond the reference models | Medium |
| Legacy Flask dashboards | Confirm retired wherever deployed (bypass app auth) | Medium |

## 9. What to bring to the review

- This document + ARCHITECTURE.md (§3, §9, §11) + OPERATING_MODEL.md.
- The role/permission matrix (internal + portal roles) and the team model.
- A data-flow diagram: Salesforce/Anaplan → app → warehouse → dashboards.
- The deployment/isolation model (one instance + one DB per client).
- The output of the built-in `security-review` on the release branch.
