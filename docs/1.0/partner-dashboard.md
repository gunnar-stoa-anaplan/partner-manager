# Partner Dashboard

> [!NOTE]
> Available to: PSM, Manager, Administrator

The Partner Dashboard is the central hub for managing your partner portfolio. It provides card-based views of your assigned partners, detailed partner profiles with tabbed dashboards, and analysis tools for portfolio-level insights.

## My Partners

Navigate to **Partner Management > Partner Dashboard** to see your assigned partners displayed as cards. Each card shows the partner's name, current path stage, health indicator, and key metrics at a glance.

- **PSMs** see only their assigned partners
- **Managers** see partners across their team's portfolios
- **Administrators** see all partners in the system

### All Partners View

Administrators and Managers can access the full partner list, which includes user assignment columns and bulk operations. Use filters to narrow by tier, path stage, PSM assignment, or health status.

## Partner Detail

Click any partner to open their detailed profile. The profile is organized into tabs that cover every dimension of the partner relationship:

### Overview Tab

The landing view shows key performance indicators at a glance:

- **Active projects**, **certifications**, and **total ACV**
- **Business metrics** — quarterly and fiscal-year sourced/influenced revenue
- **Pipeline growth** year-over-year
- **Partner path** enrollment with gate progress
- **Health scorecard** displayed as a radar chart

### Pipeline Tab

A deep dive into the partner's opportunity pipeline:

- **Opportunity funnel** by stage (Qualify, Propose, Negotiate, Close)
- **Goal attainment** — sourced vs. influenced targets with progress bars
- **Win rate** — 12-month rolling average
- **Stale deals** alert for opportunities that have not advanced
- **Pipeline velocity** — average days in each stage
- **Deal alerts** — coverage ratio, win rate trends, new business indicators
- **Product whitespace** — identifies product areas with no active pipeline
- **Opportunity list** — all deals sorted by amount with stage and close date

### Enablement Tab

Tracks the partner's learning and certification progress:

- **Blueprint completion** radar chart by product area and role
- **Enrollment metrics** — depth of engagement, stalled blueprints
- **Time-to-complete** distribution for completed blueprints
- **Upcoming enablement sessions** the partner is registered for

### Projects Tab

Monitors active delivery engagements:

- **Active project list** with status indicators
- **Delivery readiness risk** — compares trained resources against project demands
- **Upcoming pipeline risk** — flags capacity shortfalls for projected new projects

### Resources Tab

Shows the partner's team capacity and credentials:

- **Staff breakdown** — delivery, sales, and pre-sales headcount
- **Certification statistics** with trend indicators
- **Blueprint enrollment** stats across the team

### App Enablement Tab

A product readiness matrix that cross-references:

- Trained resources per product area
- Active pipeline per product area
- Current project load per product area

This matrix helps identify where a partner has demand but lacks trained capacity.

### Additional Tabs

- **Calendar** — Upcoming events and deadlines for this partner
- **Influencers** — Key contact profiles and interaction frequency
- **Deal Registrations** — All deals submitted by this partner with approval status
- **Launch Programs** — Programs the partner is nominated for or enrolled in
- **Workspaces** — Provisioned workspace models and resource usage
- **Portal Users** — Partner contacts with portal access and their roles

## Analysis & Insights

Beyond individual partner profiles, Partner Manager provides portfolio-level analysis tools:

### Full Summary

Aggregated metrics across your entire portfolio — total ACV, average health scores, certification rates, and pipeline distribution.

### Guided Analysis

Interactive exploration with customizable filters. Drill down by tier, geography, product area, or any combination to surface patterns.

### Partner Coach

Trend sparklines for each partner, highlighting trajectory (improving, stable, declining) across key metrics.

### Advisor (AI-Powered)

An AI-powered health assessment that analyzes partner data and provides natural-language insights and recommended next actions. Requires the `ai_agents_user` feature flag to be active.

## Partner Wizard

The Partner Wizard provides a guided, multi-step workflow for onboarding new partners. It walks through:

1. Basic partner information (name, tier, geography)
2. Primary contact details
3. Path enrollment
4. PSM assignment
5. Initial goal setup

> [!TIP]
> Use the Partner Wizard for individual partner onboarding. For bulk imports, use the Data Imports page or the [Integration Hub](integration-hub).
