# Authorization Model — Target Specification (DRAFT)

Status: **decisions accepted (2026-09-11); implementation in progress.** This
defines the desired behavior; changes are made in small, validated increments
toward it (see §9). The five decisions in §4 are accepted at their recommended
defaults — revise them there if intent changes.

Implementation findings (discovered while building, drive the plan):

- **`Contact` cannot take the standard global scope.** It extends
  `Authenticatable` and is the portal login actor; auto-scoping it creates an
  auth-bootstrap hazard (loading the logged-in contact would query through a
  scope that depends on already knowing that contact). It needs a bespoke
  approach and is deferred out of the mechanical increment-2 pass.
- **Snapshot/scorecard tables are dual-purpose.** `PartnerScorecard`,
  `PartnerHealthSnapshot`, `OpportunitySnapshot`, `ResourceSnapshot` are read
  both per-partner (dashboards) *and* cross-population (benchmarks/cohorts in
  `BenchmarkService`, `CohortBenchmarkProcessor`, `BenchmarkProcessor`). A blanket
  partner scope would corrupt benchmark math for non-admin actors. These need an
  explicit per-read decision (scoped display vs `withoutGlobalScope` benchmark
  reads) and are deferred from the mechanical pass.

---

## 1. Two independent questions

Authorization answers two questions that never depend on each other:

1. **Capability — what actions may this user perform?** Determined by roles.
2. **Data scope — which records may this user operate on?** Determined by the
   user's relationships to those records.

An operation on a record is allowed only when **both** pass:

```
Allowed = (capability level is sufficient) AND (record is within the user's data scope)
```

Having `Write` on a capability grants nothing for a record the user cannot see.
Being able to see a record grants no ability to act beyond the user's capability
level.

---

## 2. Capability (roles)

- A **role** grants a level on a **capability**: `None`, `Read`, or `Write`.
- A user may hold **multiple roles**. Effective level per capability is the
  **maximum** across all held roles: `None < Read < Write`. `Write` includes
  `Read`.
- Absence of any grant = `None`.
- Capabilities do **not** flow through the reporting hierarchy. A manager does
  not inherit a report's roles. (Only data scope rolls up — see §4.)
- Capability is independent of which partner/record is involved. Roles never
  encode data visibility.

This matches the current implementation (additive union across
`User::allRoles()`, `read`/`write` grants in `role_permissions`), and we intend
to keep it.

---

## 3. Data scope — the principal model

Every scoped record has one or more **principal fields** — the people (or
teams) associated with it. A user may view a record when they are a principal of
it, or sit above a principal in the reporting hierarchy, or hold the unrestricted
privilege (§5).

Principal fields we recognize:

| Field          | Meaning                                    |
|----------------|--------------------------------------------|
| `owner`        | the record's owner (e.g. `partner.user_id`)|
| `assigned_to`  | assigned user(s) (e.g. `user_partner` pivot)|
| `created_by`   | the user who created the record            |
| `team`         | a team the record is shared with (future — see note) |
| `partner`      | the partner a record belongs to (partner-anchored records only) |

**Visibility rule** (evaluated per record):

```
visible(record) =
      owner        ∈ myUserSet
   OR assigned_to  ∈ myUserSet
   OR created_by   = me                    # direct-only — see DECISION 1
   OR team         ∈ myTeamSet             # flat, membership-based — see DECISION 4
   OR partner      ∈ myPartnerScope        # partner-anchored records only
   OR unrestricted-visibility privilege    # see §5
```

Where:

- **`myUserSet`** = the actor **plus every recursive descendant** in the
  reporting hierarchy (`manager_id` walked downward, cycle-safe). This is the
  hierarchy rollup, and it applies to the rollup fields only (see DECISION 1).
- **`myTeamSet`** = the teams the actor is a member of.
- **`myPartnerScope`** = the set of partners the actor can see, itself computed
  by applying this same principal rule at the partner level (a partner is in
  scope if its owner/assignee is in `myUserSet`). Child records of an in-scope
  partner inherit visibility through `partner`.

The rule is a **union (OR)**. Adding a principal field can only widen what a user
sees; it can never hide a record the user could already see. This makes the model
safe by construction.

---

## 4. Decisions

**Status: ACCEPTED at the recommended defaults** (2026-09-11). Each recommendation
below is the decision unless revised here. Interim note for DECISION 2: until the
principal model lands (increment 3), scoping is enforced by partner alone, which
is *stricter* than the target (a record you're personally assigned to but whose
partner is out of scope is hidden, not shown). Stricter-than-target is safe — it
can only under-show, never leak — so it is an acceptable interim state; piercing
is added uniformly in increment 3.


### DECISION 1 — Which fields roll up through the hierarchy? — ACCEPTED

`owner` and `assigned_to` roll up: a manager sees what their reports own or are
assigned. `created_by` is an audit field; if it rolled up, a manager would see
everything anyone below them ever created, including long-since-reassigned
records.

**Recommendation:** `owner` and `assigned_to` participate in hierarchy rollup.
`created_by` is **direct-only** — the creator sees their own record, but this does
not extend to the creator's managers. (A manager still sees the record if it is
owned/assigned within their `myUserSet`.)

### DECISION 2 — Does a direct principal association pierce partner scope? — ACCEPTED

A record for Partner Z (outside the actor's partner scope) is assigned directly
to the actor. Can the actor see that one record?

**Recommendation:** **Yes.** A direct principal association grants visibility to
*that record* even when its partner is out of scope. Partner scope is a
*contributor* to visibility, not a ceiling. Important boundary: piercing exposes
**only the associated record**, not the partner itself or the partner's other
records. Aggregations must reflect the pierced record without revealing anything
else about Partner Z.

### DECISION 3 — Does the creator retain visibility after reassignment? — ACCEPTED

If the actor created a record that is later reassigned away from them, do they
still see it via `created_by`?

**Recommendation:** **Yes** — consistent with DECISION 1 treating `created_by` as
a direct principal. Because `created_by` does not roll up, the blast radius is a
single person. If specific record types need creator visibility to end at
handoff, add a per-type opt-out later rather than changing the global rule.

### DECISION 4 — Team sharing semantics — ACCEPTED

**Recommendation:** Team sharing is **flat and membership-based**. A user sees a
team-shared record if they are a member of that team. Team visibility does **not**
roll up through the reporting hierarchy — a manager does not inherit team-shared
records via the org chart (they get them through their own membership, or through
`owner`/`assigned_to` rollup). This avoids surprising cross-team leakage.

> Note: no record currently carries a team field. Teams today group *people*
> (`team_members` maps users/contacts to teams). Adding a team principal to
> records is a future step; this decision defines its semantics for when we do.

### DECISION 5 — What is the unrestricted-visibility privilege, and who holds it? — ACCEPTED

Company-wide visibility (see every record regardless of relationship) must be an
**explicit privilege**, not an implicit side effect of a role slug. See §5.

**Recommendation:** Model it as an explicit capability grant (e.g. a
`data_scope.all_partners` capability) that a role can carry, resolved the same way
as any other permission. `bypass_permissions` roles (super_admin) continue to see
everything — but because they bypass all checks by design, not because
`VisibilityContext` hard-codes their slugs. No non-bypass role gets unrestricted
scope unless it explicitly holds the privilege.

---

## 5. Unrestricted visibility and "My / All Partners"

- **`All Partners` ≠ every partner in the database.** For a normal user,
  `All Partners` = their full authorized hierarchical scope
  (`My Partners` + everything visible through all descendants). A manager's
  "All" might be 30 partners; an executive's, 500.
- **`My Partners`** = records where the actor is a *direct* principal (no
  hierarchy rollup).
- **True company-wide unrestricted visibility** is the separate explicit
  privilege of DECISION 5.
- The **My / All selector is a view filter, never a grant.** It only chooses
  between subsets the user is already authorized to see. It must never be able to
  widen access.

---

## 6. Universality of data scope

Data scope must apply **everywhere** a record or a fact derived from it can be
observed:

detail screens · lists · search · autocomplete · dashboards · KPIs · counts ·
metrics · charts · reports · exports · APIs · related records · aggregations.

A user must never be able to infer anything about a record outside their scope,
including through an aggregate (a total, a count, an average).

Two enforcement mechanisms exist today and both must honor the same rule:

1. **Eloquent global scope** (`ScopedToActor`) — automatic, fail-closed, the
   default guarantee. Preferred wherever a query goes through Eloquent.
2. **Manual filtering** — warehouse/metric queries (`MetricService`) build their
   own WHERE clauses from the authorized set. This is the *audited exception*:
   every manual path must be inventoried and tested, because it is where leaks
   hide.

Design intent: maximize what mechanism (1) covers; treat every use of mechanism
(2) as a reviewed, tested exception.

---

## 7. Separate dimensions (not part of authorization)

These gate *availability* and are independent of the capability/scope model
above. A capability turned off here is unavailable regardless of role or scope.

- **Module enablement** (`modules.enabled`).
- **Pennant feature flags** (instance-wide on/off).
- **Training lifecycle** (`hidden`/`locked`/`nudge`/`unlocked`).

We do not fold these into RBAC unless we explicitly decide to.

---

## 8. Known divergences from current implementation

Recorded for planning; not addressed by this document.

1. **Unrestricted scope is currently implicit in role identity.**
   `VisibilityContext` returns "see everything" for `bypass_permissions`,
   `isAdmin()`, and a hard-coded `['coe']` slug list. Target: explicit privilege
   (DECISION 5).
2. **Scope enforcement covers ~5 of ~40 partner-bearing models.** Only
   `Partner`, `Opportunity`, `Project`, `Lead`, `DealRegistration` use
   `ScopedToActor`. Coverage of the rest is unverified (inventory needed).
3. **The principal model is partner-only today.** `VisibilityContext` computes a
   partner-id set and nothing else. `owner`/`assigned`/`created_by`/`team` as
   general principals across non-partner records (e.g. workshops, owned by
   `created_by`) are not implemented.
4. **Team membership does not affect visibility.** `teamIds()` exists but is
   unused for scoping.

---

## 9. Proposed increments (smallest correct change first)

0. **Inventory / coverage matrix** (no behavior change): every scoped model, its
   principal fields, whether it is auto-scoped, and every manual read/aggregate
   path. Turns assumptions into a checkable list and orders the work below.
1. **Explicit unrestricted-visibility privilege** (DECISION 5): replace the
   hard-coded slug checks in `VisibilityContext` with an explicit grant.
   Resource-independent; small; fully testable.
2. **Close partner-scope coverage gaps**, one model at a time, each with a test
   proving an out-of-scope actor gets zero rows.
3. **Generalize to the principal model** (only when a non-partner resource needs
   enforcement): resolve `myUserSet` + `myTeamSet` once per request; each model
   declares its principal columns; scope is the OR of them. Partner scope becomes
   one principal source among several.
```
