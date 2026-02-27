# Blueprints Admin

> [!NOTE]
> Available to: CoE, Administrator

The Blueprints Admin interface allows CoE users and administrators to create, manage, and publish learning paths for partners.

## Creating a Blueprint

1. Navigate to **Administration > Blueprints**
2. Click **New Blueprint**
3. Enter the blueprint name and description
4. Define the outcomes (what the partner will achieve)
5. Add content items in the desired order
6. Set the status to **Draft** or **Published**

<!-- ![Blueprint builder](images/blueprint-builder.png) -->

## Blueprint Structure

### Outcomes

Outcomes describe what the partner will be able to do after completing the blueprint. Each outcome should be specific and measurable.

### Content Items

Content items are the individual learning resources:

| Type | Description |
|------|------------|
| **Document** | PDF, article, or documentation link |
| **Video** | Training or demonstration video |
| **Lab** | Hands-on exercise (requires `workshop_labs_admin` feature flag) |
| **Assessment** | Quiz or evaluation |
| **External** | Link to external training platform |

Each content item has a title, description, type, URL/resource link, and estimated duration.

## Publishing

Blueprints must be published before they can be assigned to partners. Draft blueprints are only visible to CoE users and administrators.

> [!NOTE]
> Once published, changes to a blueprint's content items will affect all active enrollments. If you need to make significant changes, consider creating a new version of the blueprint.

## Assigning Blueprints

Blueprints can be assigned to partners individually or in bulk:

- **Individual** — Assign from the partner's profile page
- **Bulk** — Use the blueprint's assignment page to select multiple partners
- **Path-based** — Configure auto-assignment when a partner enters a specific path stage

## Tracking Enrollment

The blueprint admin view shows enrollment statistics:

- Total enrollments
- Completion rate
- Average time to complete
- Partners currently in progress
