# Workshops & Labs

> [!NOTE]
> Available to: CoE, Administrator. Requires `workshop_delivery_admin` / `workshop_delivery_user` for workshops, and `workshop_labs_admin` / `workshop_labs_user` for labs.

Workshops & Labs extend the enablement system with hands-on, interactive training delivery. Workshops integrate with Zoom for live sessions, while Labs provide self-paced exercises with checkpoint tracking.

## Workshop Delivery

### Creating a Workshop

Workshops are created as a type of [Enablement Event](enablement-events):

1. Navigate to **Strategy & CoE > Enablement > Enablement Events**
2. Click **Create Event** and select **Workshop** as the event type
3. Define the workshop details:
   - Title, product area, and target audience
   - Duration, maximum participants, and prerequisites
   - Materials URL and description
   - Learning objectives

### AI-Assisted Design

Use the AI-powered Enablement Designer to generate a workshop structure automatically:

1. When creating a new event, choose **AI-Assisted Design**
2. Provide a topic, product area, and target audience
3. The system generates a suggested structure with sessions, objectives, and exercises
4. Review and customize the generated content before saving

### Session Management

Each workshop contains one or more sessions — individual scheduled occurrences:

1. Navigate to the workshop detail page
2. Click **Add Session**
3. Configure the session:
   - **Date and time** with timezone
   - **Location** or meeting URL
   - **Instructor** assignment
   - **Geography/Region** targeting
   - **Maximum participants** and registration deadline
   - **Notes** and post-session summary

### Zoom Integration

When creating a session for a workshop, the system can automatically provision a Zoom meeting:

- Meeting link is generated and attached to the session
- Breakout rooms can be configured for small-group exercises
- Attendance is tracked through the Zoom integration

### Registration & Attendance

Manage participant lifecycle for each session:

| Status | Description |
|--------|-------------|
| **Registered** | Partner contact has signed up |
| **Confirmed** | Attendance confirmed (manual or automated) |
| **Attended** | Participant completed the session |
| **No-Show** | Registered but did not attend |
| **Cancelled** | Registration was withdrawn |

Track registrations at **Enablement Events > [Workshop] > Sessions > [Session] > Registrations**.

## Workshop Labs

Labs provide hands-on, self-paced exercises that complement live workshop sessions.

### Creating a Lab

1. Navigate to **Strategy & CoE > Workshops > Labs**
2. Click **Create Lab**
3. Define the lab structure:
   - Title, description, and associated workshop (optional)
   - Estimated duration and difficulty level

### Checkpoints

Labs are organized into sequential checkpoints — discrete tasks or exercises that participants must complete:

- Each checkpoint has instructions, expected outcomes, and completion criteria
- Participants mark checkpoints as complete as they progress
- The system tracks completion time per checkpoint

### Participant Progress

Monitor lab completion across all participants:

- View aggregate completion rates by checkpoint
- Identify bottlenecks where participants are getting stuck
- Track individual participant progress and time spent
- Export completion data for reporting

> [!TIP]
> Pair live workshops with labs for a "learn then practice" model. Use the workshop session for instructor-led demonstration, then assign the lab for hands-on reinforcement.
