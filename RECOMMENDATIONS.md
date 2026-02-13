# Partner Manager 3 — Recommended Changes

Generated: 2026-02-07 | Updated: 2026-02-13

---

## Previous Recommendations (2026-02-07)

### Priority 1 — Core Gaps (Mostly Resolved)

1. ~~**Missing Lead Management views**~~ — Resolved
2. ~~**Empty Partner CRUD**~~ — Resolved
3. ~~**Missing Field Enablement views**~~ — Resolved
4. ~~**No Profile edit view**~~ — Resolved
5. ~~**Partner Goals page**~~ — Resolved and expanded (see Feb 13 Goals system)

### Priority 2 — Data & Analytics

6. **Pipeline Funnel time comparison** — Add a time slider or comparison showing funnel changes over time (52 weeks of snapshots available).
7. ~~**No "Closed Lost" tracking**~~ — Resolved in seeder improvements
8. **Opportunity deduplication on re-import** — No unique constraint on `salesforce_id`. Re-importing creates duplicates.
9. **Fiscal quarter/year not auto-populated** — `FiscalYearHelper` exists but isn't used in the import flow.
10. ~~**Partner Goals: show actuals vs targets**~~ — Resolved in Goals & Targets enhancement

### Priority 3 — Dashboard & UX

11. ~~**No PSM portfolio dashboard**~~ — Resolved
12. ~~**No admin overview dashboard**~~ — Resolved (comprehensive 9-section admin dashboard)
13. **Partner show page layout gap** — Pipeline Progression funnel leaves half the row empty.
14. **Opportunity detail view missing** — Can see pipeline aggregates but can't drill into individual opportunities.
15. **Breadcrumb inconsistency** — Not shown on all pages.

### Priority 4 — Code Quality & Security

16. **No Form Request validation classes** — All validation inline in controllers.
17. **No authorization policies** — PSM access checks are inline. Use Laravel Policies.
18. **No soft deletes on critical models** — Partners, Opportunities, Contacts hard-deleted on cascade.
19. **No audit trail** — No tracking of who changed what.
20. ~~**Eager loading overhead**~~ — Addressed

### Priority 5 — Cleanup

21. **60+ unused SmartAdmin demo views** — Template demos never integrated.
22. ~~**Unused models**~~ — Now in use (PartnerEvent, Account, Objective)
23. **`storage/seeder_debug.json`** — Debug artifact.
24. **No tests** — Only placeholder test files exist.
25. **Multiple markdown files in root** — Planning docs should be moved to `docs/`.

---

## New Recommendations (2026-02-13)

### High-Impact, Directly Actionable

#### ~~1. Partner Health Score / Churn Risk Indicator~~ — DONE
Composite "partner health" metric combining scorecard, pipeline, enablement, delivery CSAT, and goal attainment data. Traffic-light dashboard showing at-risk partners based on combined signals (declining pipeline, missed goals, low engagement, stale contacts) for PSM prioritization.

#### ~~2. Deal Registration / Co-Sell Workflow~~ — DONE
Workflow for partners to register deals and PSMs to manage the co-sell lifecycle (registration → approval → support → close). Core partner program feature.

#### 3. Partner Tiering / Segmentation Engine
Automated tier assignment (Platinum/Gold/Silver) based on scoring criteria. Tiers unlock benefits, target allocations, and visibility.

#### 4. Activity Timeline / Audit Log
Unified per-partner timeline showing all touchpoints chronologically (meetings, emails, task completions, stage advances, deal closures, certifications earned).

#### 5. Export & Reporting
PDF/Excel export for QBRs, scorecards, and analytics dashboards.

#### ~~6. Partner Self-Service Portal~~ — DONE
Limited external-facing portal where partners can view their own performance, register for events, submit deal registrations, track blueprint progress, and update certifications.

### Medium-Impact, Strategic

#### 7. Renewal/Retention Tracking
Track partner contract renewals, satisfaction trends, and at-risk renewal signals.

#### 8. Incentive/MDF Management
Market Development Funds (MDF) and incentive programs with approval workflows, spend tracking, and ROI measurement.

#### 9. Communication Hub
Centralized messaging (or Slack/Teams integration) with template-driven campaigns.

#### ~~10. Benchmark Comparisons~~ — DONE
Show partners how they compare to peers anonymously (e.g., "Top 25% for certifications, bottom quartile for pipeline").

#### ~~11. Goal Auto-Computation~~ — DONE
Auto-compute goal actuals from live data using the metric_key field in the goals system.

#### 12. Mobile-Friendly Dashboard
Simplified mobile view optimized for PSMs doing field visits.
