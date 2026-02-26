# Feature Activation

> [!NOTE]
> Available to: Administrator. CoE access requires the `feature_activation_coe` flag to be active.

Feature Activation provides progressive feature rollout through a combination of feature flags and phased activation paths. It allows administrators to control which modules are available and to ensure users are properly trained before gaining full access.

## Feature Flags

Navigate to **Administration > Feature Activation > Feature Flags** to manage module availability.

### How Flags Work

Each module in Partner Manager is gated by one or two feature flags:

- **Admin flag** — Controls access to creation and configuration screens (e.g. `enablement_events_admin`)
- **User flag** — Controls access to end-user interactive features (e.g. `enablement_events_user`)

Flags have two layers:

1. **Environment** — The module must be enabled in the deployment configuration
2. **Pennant** — The flag must be activated in the Feature Activation UI

Both layers must be active for the feature to be available.

### Toggling Flags

1. Navigate to **Feature Flags**
2. Each module shows its current status:
   - **Env Enabled** — Whether the environment configuration allows this module
   - **Pennant Active** — Whether the flag has been toggled on
3. Toggle the Pennant switch to activate or deactivate

> [!IMPORTANT]
> Administrators and CoE users bypass feature flag checks — they can access all modules regardless of flag state. Flags primarily gate access for PSM, Manager, and Integration users.

### Available Flags

See [Roles & Permissions](roles-and-permissions) for the complete list of feature flags organized by module.

## Activation Paths

Activation Paths go beyond simple on/off flags by adding phased learning progressions that gradually unlock features for users.

### How Paths Work

Each activation path has three phases:

| Phase | Purpose | Example Steps |
|-------|---------|---------------|
| **Tell** | Educational content | Read articles, watch overview videos |
| **Show** | Guided demonstration | Follow interactive tours, observe walkthroughs |
| **Do** | Hands-on proof | Complete practical tasks, achieve target metrics |

Users progress through each phase sequentially. The feature becomes accessible once the user completes the **Tell** phase, and fully enabled after completing all phases.

### Creating a Path

1. Navigate to **Administration > Feature Activation > Activation Paths**
2. Click **Create Path**
3. Configure:
   - **Name** and **Description**
   - **Linked Feature Flag** — which module this path controls
4. Add steps to each phase:
   - **Step type** — read, watch, tour, or task
   - **Content** — URL, description, or instructions
   - **Required vs. Optional** — whether the step must be completed
   - **Completion criteria** — for task steps, what constitutes completion (e.g. "complete 5 tasks")

### Assigning Paths

1. Navigate to the path detail page
2. Click **Assignments**
3. Assign users:
   - **By role** — assign to all users with a specific role
   - **Individual** — assign to specific users
4. Assigned users will see the path in their training queue

## Progression Dashboard

Navigate to **Administration > Feature Activation > Progression Dashboard** for aggregate completion metrics:

- Completion rates by path and phase
- User distribution across Tell/Show/Do phases
- Bottleneck identification — which steps have the lowest completion rates

### Individual User Progress

Drill into any user to see their path-by-path progress:

- Steps completed vs. remaining
- Time spent in each phase
- Manual completion override (for administrator use when training was delivered outside the system)

## Activation Log

The Activation Log provides an audit trail of all feature activation events:

- Flag toggles (who changed what, when)
- User path assignments
- Step completions
- Manual overrides

> [!TIP]
> Use activation paths when rolling out complex features like AI Agents or the Strategy Workbench. The phased approach ensures users understand the feature before they start using it, reducing support burden and improving adoption.
