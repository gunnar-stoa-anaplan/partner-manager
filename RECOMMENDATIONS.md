# Partner Manager 3 — Recommended Changes

Generated: 2026-02-07

---

## Priority 1 — Core Gaps

1. **Missing Lead Management views** — `LeadController` references views (`business-development.leads.*`) that don't exist. Sourced Leads, Influenced Leads, and lead assignment pages are non-functional.

2. **Empty Partner CRUD** — `PartnerController::create()` and `store()` are empty stubs. Admin can't create partners via UI, only through imports.

3. **Missing Field Enablement views** — Routes for `/field/key-influencers`, `/field/breadth`, `/field/depth` exist but no Blade templates. The sidebar links lead to errors.

4. **No Profile edit view** — `ProfileController` has `edit`/`update` routes but no corresponding view template.

5. **Partner Goals page** — Migration and controller exist but verify it's fully wired up (view, navigation link, save flow).

---

## Priority 2 — Data & Analytics

6. **Pipeline Funnel shows only current snapshot** — The funnel chart on `show.blade.php` shows current-state pipeline only. Add a time slider or comparison to show how the funnel has changed over time (52 weeks of snapshots available).

7. **No "Closed Lost" tracking** — The seeder only progresses opps forward toward Closed Won. Real pipelines have deals that go to Closed Lost. Add attrition at each stage transition (10-15% fall to Closed Lost).

8. **Opportunity deduplication on re-import** — No unique constraint on `salesforce_id` or `opportunity_name`. Re-importing the same Excel creates duplicates.

9. **Fiscal quarter/year not auto-populated** — `FiscalYearHelper` exists but isn't used in the import flow. Opportunities rely on manual or seeder-set values.

10. **Partner Goals: show actuals vs targets** — The goals table stores targets but there's no view showing actual sourced/influenced NNACV vs those targets (progress bars, % to goal).

---

## Priority 3 — Dashboard & UX

11. **No PSM portfolio dashboard** — `/my-partners` shows a card list but no aggregated KPIs across the PSM's entire book of business (total pipeline, total sourced NNACV, total contacts, etc.).

12. **No admin overview dashboard** — Admin has no central dashboard showing system-wide metrics (total partners, total pipeline, imports this week, PSM leaderboard).

13. **Partner show page layout gap** — The Pipeline Progression funnel is in a `col-lg-6` leaving half the row empty. Consider adding a second chart (e.g., revenue by product area, or win rate trend) next to it.

14. **Opportunity detail view missing** — You can see pipeline aggregates but can't drill into individual opportunities from the partner page.

15. **Breadcrumb inconsistency** — Breadcrumbs aren't shown on all pages. Standardize across all authenticated views.

---

## Priority 4 — Code Quality & Security

16. **No Form Request validation classes** — All validation is inline in controllers. Extract to `FormRequest` classes for reusability and cleaner controllers.

17. **No authorization policies** — PSM access checks are inline (`$partner->user_id !== auth()->id()`). Use Laravel Policies for `Partner`, `Opportunity`, `Lead`, etc.

18. **No soft deletes on critical models** — Partners, Opportunities, Contacts are hard-deleted on cascade. One accidental delete loses all historical data. Add `SoftDeletes`.

19. **No audit trail** — No tracking of who changed what. Consider `updated_by` on key models or a generic `audits` table.

20. **Eager loading overhead** — `Partner` model has `protected $with = ['projects', 'contacts']`, loading these on every query even when not needed. Remove default eager loading and use explicit `->with()` where needed.

---

## Priority 5 — Cleanup

21. **60+ unused SmartAdmin demo views** — The `apex/`, `dashboard/`, `databites/`, `email/`, `tables/`, `forms/`, `ui/`, `forum/`, `messenger/`, `icons/`, `utilities/` directories are template demos never integrated. Remove them to reduce clutter.

22. **Unused models** — `PartnerEvent`, `Account`, `Objective` are created but barely used. Either build them out or remove them.

23. **`storage/seeder_debug.json`** — Debug artifact sitting in storage, should be gitignored.

24. **No tests** — Only placeholder `ExampleTest` files exist. At minimum add feature tests for import flow, partner access control, and goal CRUD.

25. **Multiple markdown files in root** — `COMPONENT_EXAMPLES.md`, `FULL_SUMMARY_IMPLEMENTATION.md`, `SMARTADMIN_ANALYSIS.md`, etc. appear to be planning docs that should be removed or moved to a `docs/` folder.
