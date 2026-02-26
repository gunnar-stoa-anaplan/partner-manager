# Configuration

> [!NOTE]
> Available to: Administrator

The Configuration section allows administrators to customize how Partner Manager operates — from scorecard weights to alert thresholds to task templates.

> [!TIP]
> To customize the application's appearance (logo, app name, colors, favicon), see [Branding](branding).

## Accessing Configuration

Navigate to **Administration > Configuration** from the admin sidebar.

> [!IMPORTANT]
> Configuration changes affect all partners globally. Review changes carefully before saving.

## Scorecard Weights

Configure how much each dimension contributes to the composite scorecard score:

<!-- ![Scorecard weight configuration](images/scorecard-weights.png) -->

| Dimension | Default Weight |
|-----------|---------------|
| Revenue | 30% |
| Delivery | 25% |
| Enablement | 25% |
| Engagement | 20% |

Weights must total 100%. Adjust them to reflect your organization's priorities.

## Alert Thresholds

Configure the thresholds that trigger automated alerts:

- Health score below a minimum value
- Task overdue by more than N days
- Certification expiring within N days
- Pipeline value declining by more than N percent

## Path Configuration

Manage the paths, stages, and gates that define partner progression:

- **Paths** — Create and edit partner paths
- **Stages** — Define the stages within each path
- **Gates** — Set the criteria for advancing between stages

See [Partner Paths](partner-paths) for how paths are used in practice.

## Task Templates

Task templates define the tasks that are auto-created when a partner enters a new stage. Each template specifies:

- Task title and description
- Default owner assignment
- Due date offset (e.g., 30 days after stage entry)
- Associated path and stage

## Notification Templates

Configure email notification templates for system events:

- Partner stage advancement
- Task assignment
- Alert generation
- Event registration confirmation

Templates support dynamic variables (partner name, PSM name, dates) that are populated at send time.
