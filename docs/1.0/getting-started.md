# Getting Started

This guide walks you from zero to a fully operational Partner Manager deployment. It covers provisioning your environment, then walks each user type through their first steps.

## Step 1: Provision Your Environment

The fastest and easiest way to get started is to let us host your deployment. You don't need to provide, provision, or install anything — we handle it all.

### Managed Service (Recommended)

With our managed service, we provision and maintain isolated containers for the application, the sidecar agent, and the database. Each client gets their own dedicated containers that are fully isolated — nothing is shared with or accessible to any other client.

To get started:

1. Contact your account representative to initiate provisioning
2. We create your isolated environment (typically same-day)
3. You receive your dedicated URL and initial administrator credentials
4. Log in and begin configuring the platform (continue to [Step 2](#step-2-configure-the-platform) below)

That's it. No servers to provision, no databases to install, no containers to manage. We handle infrastructure, patching, backups, monitoring, and updates on your behalf.

> [!NOTE]
> Even though we host the infrastructure, only you have access to what runs inside your containers. Your database, application data, encryption keys, and user credentials are never accessible to our operations team. See [Deployment Options — Isolation & Access Boundaries](deployment-options#isolation--access-boundaries) for the full details.

See [Deployment Options](deployment-options) for pricing and a full comparison of managed vs self-hosted.

### Self-Hosted

If you prefer to host Partner Manager in your own infrastructure, here's the process:

1. **Provision infrastructure** — You provide a compute host (VM or bare metal with Docker), a [supported database](database-support), and outbound HTTPS access
2. **Receive your deployment package** — We generate your instance identity, mTLS certificates, and configuration files
3. **Configure your environment** — Place the deployment files on your host and create your `.env` file with database credentials
4. **Launch** — Run `docker compose up -d` to start the application and agent containers
5. **Verify** — Confirm the application is accessible and the agent appears online on our fleet dashboard

See [Deployment](deployment) for the full step-by-step guide.

---

## Step 2: Configure the Platform

Now that your application and database are ready — whether managed or self-hosted — the administrator must configure several foundational elements before the platform becomes useful. Complete these steps in order.

![Phase 1: Setup & activation](images/phase1-setup-activation.svg)

### 1. Feature Activation

Start by enabling the modules your organization needs:

1. Navigate to **Administration > Feature Activation > Feature Flags**
2. Review the available modules and toggle on the ones you want to activate
3. Optionally, create **Activation Paths** to progressively roll out features to users with phased learning (Tell, Show, Do)

See [Feature Activation](feature-activation) for details.

### 2. Branding & Configuration

Establish your organization's identity and preferences:

1. Navigate to **Administration > Branding**
2. Set the application name (e.g. "Acme Partner Hub")
3. Upload your organization's logo and favicon
4. Select a default color theme or configure custom brand colors
5. Navigate to **Administration > Configuration**
6. Adjust scorecard weights to reflect your organization's priorities
7. Configure alert thresholds for health scores, overdue tasks, and expiring certifications

See [Branding](branding) and [Configuration](configuration) for details.

### 3. Create Users & Assign Roles

Add the users who will operate the platform:

1. Navigate to **Administration > User Management**
2. Create user accounts and assign the appropriate role:
   - **Administrator** — System configuration and oversight
   - **CoE** — Strategy design and enablement program management
   - **PSM** — Field partner management
   - **Manager** — Team oversight and reporting
   - **Integration** — Data connector operations
3. Set manager relationships for reporting hierarchy
4. Optionally, use **Directory Sync** to bulk-import users from a CSV export of your directory system

See [User Management](user-management), [Team Management](team-management), and [Directory Sync](directory-sync) for details.

### 4. Define Partner Paths

Paths are the backbone of prescriptive partner management. Each path defines the stages a partner progresses through, the gates they must pass, and the tasks that are auto-generated at each stage.

1. Navigate to **Strategy & CoE > Partner Paths > Path Configuration** (or delegate to your CoE)
2. Create your first path (e.g. "Standard Partner Maturity")
3. Define stages such as Onboarded, Enabled, Performing, Accelerating, Strategic
4. Set gate criteria for each stage transition
5. Create task templates for each stage

> [!WARNING]
> At least one path must exist before partners can be enrolled. Without paths, partners cannot receive auto-generated tasks or progress through stages.

See [Partner Paths](partner-paths) for the full guide.

### 5. Import Data

Populate the platform with your existing partner data:

1. Navigate to **Administration > Data Imports** or set up the **Integration Hub** for automated imports
2. Import **partners** — creates partner records with basic profile data
3. Import **PSM assignments** — maps partners to their assigned PSMs
4. Import **opportunities** — populates pipeline and revenue data
5. Import **accounts** — links workspace accounts to partner records
6. Import **resources** — adds partner resource and certification data

> [!TIP]
> Download the import templates from the Data Imports page to see the expected file format for each type. For automated, recurring imports, set up connectors in the [Integration Hub](integration-hub).

### 6. Enroll Partners in Paths

Once partners and paths exist, enroll partners to begin their maturity journey:

1. Navigate to a partner's profile
2. Enroll them in the appropriate path
3. The system auto-generates stage tasks from your templates

### 7. Set Up Enablement Content

Create the learning and enablement resources your partners will use:

1. **Blueprints** — Navigate to **Administration > Blueprints Admin** and create structured learning paths with outcomes and content items
2. **Enablement Events** — Navigate to **Strategy & CoE > Enablement Events** and create events with sessions, registration tracking, and calendar scheduling
3. **Launch Programs** — Navigate to **Strategy & CoE > Launch Programs** and set up product launch programs with milestones and partner nominations

See [Blueprints Admin](blueprints-admin), [Enablement Calendar](enablement-calendar), and [Launch Programs](launch-programs) for details.

### 8. Configure Goals & Scorecards

Establish performance targets for the current fiscal year:

1. Navigate to **Strategy & CoE > Goals > Templates** and create goal templates
2. Set targets for sourced revenue, certifications, delivery quality, and engagement
3. Assign goals to individual partners or apply templates across tiers

See [Goals & Targets](goals-and-targets) and [Scorecard](scorecard) for details.

### 9. Provision Partner Portal Access

Enable self-service access for partner contacts:

1. Navigate to **Administration > Provisioning**
2. Set up workspaces for partners that need portal access
3. Invite partner contacts with appropriate portal roles

See [Portal Overview](portal-overview) and [Provisioning](provisioning) for details.

---

## Step 3: Role-Specific Onboarding

Once the platform is configured, each role has its own onboarding path.

### CoE: Setting Up Your Programs

Once an administrator has activated features and created your account, here's how to get started as a Center of Excellence user.

#### Landing Page

After logging in, you land on the **CoE Dashboard** — an overview of your strategy plans, enablement programs, and partner path progress.

#### Your First Steps

1. **Create a strategy plan** — Navigate to **Strategy Workbench > Plans** and create your first fiscal-year plan with targets, budgets, and initiatives. See [Strategy Workbench](strategy-workbench)
2. **Build the knowledge base** — Navigate to **Knowledge Base > Ingest** and upload your product documentation, implementation guides, and best practices. These documents power the AI agents. See [Knowledge Base](knowledge-base)
3. **Configure AI agents** — Navigate to **AI Agents** and set up product expert agents with knowledge base bindings. See [AI Agents](ai-agents)
4. **Design enablement events** — Create workshops, webinars, and courses under **Enablement**. Use AI-assisted design to generate event structures from a topic and audience. See [Enablement Events](enablement-events)
5. **Create workshop labs** — For hands-on training, create labs with checkpoints and exercises under **Workshops**. See [Workshops & Labs](workshops)
6. **Set up launch programs** — For product launches, create cohort-based programs with milestones under **Launch Programs**. See [Launch Programs](launch-programs)
7. **Configure lead assignment rules** — Define routing rules for automatic lead distribution under **Lead Assignment Rules**

#### Ongoing Workflow

As a CoE user, your ongoing work focuses on refining the frameworks that PSMs execute:

1. **Plan** — Update strategy plans, set quarterly targets, model scenarios
2. **Design** — Create enablement content, workshops, and blueprints
3. **Curate** — Maintain the knowledge base as products evolve
4. **Review** — Monitor partner path progress, conduct path reviews, analyze program effectiveness

---

### PSM: Day-One Onboarding

Once an administrator has completed the initial setup and assigned you to partners, here's how to get started as a Partner Success Manager.

![Phase 2: Core management](images/phase2-core-management.svg)

#### Logging In

Navigate to the Partner Manager URL provided by your administrator and log in with your credentials.

#### The Dashboard

After logging in, your dashboard provides an at-a-glance view of your portfolio:

- **Partner count** and tier distribution
- **Active tasks** requiring your attention
- **Upcoming events** and deadlines
- **Health alerts** for at-risk partners

#### Navigation

Partner Manager uses a left-hand sidebar organized by feature area:

| Section | What You'll Find |
|---------|-----------------|
| **Partner Management** | Partner dashboard, Idea Room, business development, lead assignment, deal registration |
| **Plan** | Partner paths, task boards, action plans, goals, scorecards, competency matrices |
| **Engage** | Project health, field enablement, solution lifecycle, QBRs, events, blueprints |
| **Measure** | Portfolio analytics, delivery metrics, revenue analysis, benchmarking |
| **Act** | Health dashboard, alerts, path reviews |

#### Your First Steps

1. **Review your assigned partners** — Navigate to **Partner Management > Partner Dashboard** and familiarize yourself with each partner's current stage, scorecard, and open tasks
2. **Check your task board** — Go to **Plan > Task Board** to see tasks awaiting action. Address any overdue items first
3. **Review health alerts** — Go to **Act > Health Dashboard** to see any partners flagged as at-risk
4. **Explore the Idea Room** — Visit **Partner Management > Idea Room** to see collaborative ideation spaces
5. **Schedule QBRs** — For partners that are due, start building QBR sessions under **Engage > QBR Workbench**
6. **Assign blueprints** — Review available blueprints and assign relevant ones to partners that need enablement
7. **Register partners for events** — Check the **Engage > Enablement Events** calendar and register partners for upcoming sessions

#### Ongoing Workflow

As a PSM, your daily workflow typically follows this cycle, feeding into continuous optimization.

![Phase 4: CoE review & optimization](images/phase4-coe-optimization.svg)

1. **Plan** — Review tasks, update action plans, set goals for the quarter
2. **Engage** — Conduct enablement, manage deal registrations, hold QBR sessions
3. **Measure** — Monitor scorecards, track delivery metrics, analyze revenue trends
4. **Act** — Respond to health alerts, conduct path reviews, advance partners through stages

---

### Integration: First-Time Setup

The Integration role is focused on data operations. After logging in, you land on the Integration Hub Dashboard.

#### Your First Steps

1. **Browse connectors** — Navigate to **Integration > Integration Hub** and review the available connector types
2. **Set up your first connection** — Select a connector (e.g. CSV), configure credentials, and test connectivity
3. **Map fields** — Create field mappings between your source data and Partner Manager's data model
4. **Run your first import** — Execute an import job and monitor its progress
5. **Set up exports** — Configure export jobs to push data back to external systems

See [Integration Hub](integration-hub) for the full guide.

---

### Partner: Getting Started with the Portal

As a partner contact, you access Partner Manager through the **Partner Portal** — a self-service interface for tracking your organization's progress, registering for events, and managing deal registrations.

![Phase 3: Partner portal](images/phase3-partner-portal.svg)

#### Accessing the Portal

1. You will receive an invitation email from your Partner Success Manager or the system
2. Follow the link to set up your portal credentials
3. Log in at the Partner Portal URL (separate from the main application)

> [!NOTE]
> Portal credentials are separate from the main application. If you are unsure of your login URL, contact your Partner Success Manager.

#### What You Can Do

Depending on your portal role, you may have access to:

- **Scorecard** — View your organization's performance metrics and progress
- **Goals** — Track your targets for the current fiscal year
- **Blueprints** — Access learning paths and track your progress through assigned content
- **Events** — Browse and register for upcoming enablement sessions
- **Deal Registration** — Submit new deals for approval and track existing registrations
- **Team** — View and manage your organization's portal contacts (if you have team management access)

See [Portal Overview](portal-overview) and [Portal Roles](portal-roles) for the full list of capabilities by role.

---

## Next Steps

| If you are... | Start here |
|---------------|------------|
| An **Administrator** setting up for the first time | [Feature Activation](feature-activation), [Configuration](configuration), [Partner Paths](partner-paths) |
| A **CoE** user designing programs | [Strategy Workbench](strategy-workbench), [Knowledge Base](knowledge-base), [AI Agents](ai-agents) |
| A **PSM** managing partners | [Partner Dashboard](partner-dashboard), [Task Board](task-board), [Scorecard](scorecard) |
| A **Manager** overseeing teams | [Team Management](team-management), [Portfolio Analytics](portfolio-analytics) |
| An **Integration** user | [Integration Hub](integration-hub) |
| A **Partner** using the portal | [Portal Overview](portal-overview), [Blueprints](blueprints) |
