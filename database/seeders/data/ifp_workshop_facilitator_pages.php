<?php

return [
    0 => [
        'slug' => 'facilitator-run-of-show',
        'title' => 'Facilitator Run-of-Show — Live Cohorts',
        'body' => '<div class="callout-warning">
        <span class="callout-label">⚠ Scope of This Run-of-Show</span>
        <p>This document applies when a facilitator runs a <strong>guided live cohort</strong> — in person or over Zoom, walking participants through the workshop in sessions. Most participants today take the workshop fully self-paced; for that path, the <strong>Facilitator Launch Toolkit</strong> covers the entire workflow (invitations, kickoff email, Slack welcome, mid-week nudge, wrap-up email). Use this Run-of-Show only when you\'re delivering a guided session.</p>
        <p>The two sessions below can run as a single intensive day, two half-days, or split across weeks — whatever fits the cohort. Durations are guidance; cut for time by skipping the optional debrief discussions, not the labs.</p>
      </div>

      <div class="callout-info">
        <span class="callout-label">📦 Companion pages</span>
        <p>For all the <em>non-session</em> assets (invitations, registration confirmation, day-before welcome, tenant credentials email, Slack channel welcome, mid-week check-in, thank-you email, common-Q&amp;A cheat sheet, printable PDF overview), see the <strong>Facilitator Launch Toolkit</strong>.</p>
        <p>For tracking each cohort\'s participation rates by partner and pre→post survey deltas, see the <strong>Progress Report</strong>.</p>
      </div>

      <div class="legend">
        <div class="legend-item"><div class="legend-swatch" style="background:#f0f7ff;border:2px solid #0066cc;"></div> Script (say out loud)</div>
        <div class="legend-item"><div class="legend-swatch" style="background:#fffbeb;border:2px solid #f59e0b;"></div> Click / Navigate</div>
        <div class="legend-item"><div class="legend-swatch" style="background:#f0fdf4;border:2px solid #22c55e;"></div> Facilitator note (silent)</div>
      </div>

      <h2>Pre-Workshop Checklist</h2>
      <ul class="checklist">
        <li>Training tenant credentials confirmed for all participants — test login before the first session</li>
        <li>All four required roles assigned to each participant: <strong>Application Owner, Workspace Admin, Page Builder, Integration Admin</strong></li>
        <li>Dataspace assigned to each participant in the training tenant</li>
        <li>TRAIN models pre-staged and accessible to all participants (e.g., <code>IFPv2.0.0-train-base-financial-planning</code>)</li>
        <li>Application Framework available to all participants — not just the facilitator</li>
        <li>Dataset files distributed for customer context</li>
        <li>Known generation error confirmed — at least one <code>LINE_ITEM_FORMULA</code> error expected in training model so participants see error log triage</li>
        <li>Extension reference implementations prepared and ready to review</li>
        <li>Micro-lesson video links confirmed working (SharePoint links for Dimensionality, Configuration Overview, Editing Hierarchy Levels, Post-Generation, Error Log Review)</li>
        <li>Workshop Discussion panel confirmed accessible to participants for follow-up questions during and after the session</li>
        <li>Zoom screen share tested — participants can read your screen clearly (resolution, font size)</li>
      </ul>

      <hr class="section-divider">

      <div class="day-banner">📚 Session 1 — Configuration &amp; Generation</div>

      <h3>Opening <span class="timing-badge">15 min</span></h3>
      <div class="script-block">
        <p>Welcome everyone. In this session and the next we are going to go deep on IFP 2.1 delivery — not the overview, but the hands-on practitioner workflow you need to actually deliver this for a customer.</p>
        <p>Session 1 is all about configuration and generation. We will walk through the Application Framework Configurator together, then participants will configure it for their own customer scenario. After that, we will generate a model, deal with errors, and review the error logs systematically.</p>
        <p>Session 2 is extensions. We will build two real extensions from scratch — the kind of things customers ask for in almost every IFP engagement.</p>
        <p>Quick logistics: please confirm you can log into the training tenant. Drop a message in the Slack channel if you can\'t access the workspace, and we\'ll resolve before moving on.</p>
      </div>
      <div class="facilitator-note">Check: everyone has workspace access before moving on. Do not proceed if more than 2 people are locked out — resolve access first.</div>

      <hr class="section-divider">

      <h3>Tenant Access Verification <span class="timing-badge">10 min</span></h3>
      <div class="script-block">
        <p>Before we touch the Application Framework, let\'s confirm everyone\'s tenant is set up correctly. This is the single biggest reason participants get stuck mid-engagement — they discover a missing role or unassigned Dataspace right when they hit Generate.</p>
        <p>Open the Tenant Access page in the lab guide and follow the three checks: confirm the correct tenant, verify your roles, and verify your Dataspace assignment.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Lab guide → Tenant Access. Walk through the three checks live: login → correct tenant → Administration section showing the four roles (Application Owner, Workspace Admin, Page Builder, Integration Admin) and the Dataspace.</div>
      <div class="facilitator-note">Verify every participant confirms all four roles AND a Dataspace before moving on. If anyone is missing a role or Dataspace, escalate to the Anaplan team in Slack — do not proceed to configuration without this resolved.</div>

      <hr class="section-divider">

      <h3>IFP Overview <span class="timing-badge">20 min</span></h3>
      <div class="script-block">
        <p>Let me set the context. IFP 2.1 is a significant re-architecture. Three major changes: Application Framework, ADO, and Headcount Planning 2.0. Of these, the Application Framework is the one that changes how you deliver — everything else is incremental.</p>
        <p>The Application Framework is a configurator. You answer 44 questions, hit generate, and it produces a fully structured model. The generated model has errors — that is expected and normal. We will see this today and we will learn to deal with it.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Open Application Framework → Overview page. Show the object counts: 44 configurations, 4 models, 17 assets, generation status.</div>
      <div class="facilitator-note">Point out "Generated with Errors" on the overview — normalize it early. This is the number-one source of participant anxiety and it is completely unfounded.</div>

      <hr class="section-divider">

      <h3>Configurator Walkthrough <span class="timing-badge">45 min</span></h3>
      <div class="script-block">
        <p>Let me take you through The Application Framework Configurator section by section. Follow along in your own workspace — you are not configuring yet, just watching.</p>
        <p>The Application Framework Configurator has three main sections: Hierarchies, Data Structure, and Configurations. Hierarchies is the most critical — these decisions are irreversible after generation.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Application Framework → Configurator → Hierarchies. Show entity levels, geography levels, functional area checkboxes.</div>
      <div class="script-block">
        <p>See these hierarchy choices? Entity levels, geography levels, functional areas. These drive the dimension structure of every module that gets generated. If you change your mind after generation, you regenerate — which means rebuilding any extensions you added.</p>
        <p>Session Zero with your customer is about locking these decisions. Get hierarchy wrong at Session Zero and you are regenerating during Test &amp; Deploy.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Configurations section. Walk through top-level questions 1-10. Point out: fiscal year, currency, planning methods available.</div>
      <div class="facilitator-note">Key decision to highlight: planning methods available. Participants often enable everything "just in case." Only enable what the customer actually needs — each enabled method adds line items to the INP modules.</div>

      <hr class="section-divider">

      <h3>Lab: Configurator Exercise <span class="timing-badge">60 min</span></h3>
      <div class="script-block">
        <p>Now it is your turn. Configure the Application Framework for a customer scenario. Consider: how many entity levels, geography structure, functional areas in scope, and any special configurations. Configure and trigger generation.</p>
        <p>This is a decision exercise. There is no step-by-step answer guide. You are making real configuration choices — the same ones you would make with an actual customer. Document your decisions and your reasoning.</p>
        <p>You have 60 minutes. When everyone has triggered generation, we will take a break and then I will walk through the reference configuration during debrief.</p>
      </div>
      <div class="facilitator-note">Circulate during configuration. Watch for: participants over-engineering hierarchy levels, anyone trying to configure Headcount × Rate in The Application Framework Configurator (it is an extension — correct that immediately), anyone hitting Save without confirming all required fields.</div>
      <div class="facilitator-note">Managing generation wait (~10-15 min): use this time to take questions about configurator decisions, review what participants chose, discuss the debrief questions. Do not let it become dead time.</div>

      <hr class="section-divider">

      <h3>Model Generation <span class="timing-badge">30 min</span></h3>
      <div class="script-block">
        <p>Let me walk through what just happened when you hit Generate. The Application Framework read all 43 of your answers and provisioned four models: Financial Planning, Headcount, Admin, and Data Orchestrator. It applied your configuration to the module template and wired the formulas.</p>
        <p>Most of you will see "Generated with Errors." That is fine — say it with me: that is fine. It does not mean the model is broken. It means some formulas hit references that differ from the base template because of your configuration choices.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Workspace → show 4 generated models. Open Financial Planning model → Modules → show ADMIN, SYS, DAT, INP, CALC, OUT prefixes.</div>
      <div class="script-block">
        <p>Every module prefix has a purpose. ADMIN is configuration. SYS is system control — Boolean flags, list mappings. DAT is data staging. INP is where users enter numbers. CALC is aggregation and derived calculations. OUT is what flows to your dashboards and reports. Know this architecture and you can navigate any generated IFP model.</p>
      </div>

      <hr class="section-divider">

      <h3>Post-Generation Steps <span class="timing-badge">20 min</span></h3>
      <div class="script-block">
        <p>Before you do anything else after generation, run through this checklist. Every time. On every customer project. I am going to demonstrate each step now and then you will do it on your own model.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Application Framework overview — point out model count, UX app count, asset count. Click Error Logs — show error count and log format.</div>
      <div class="click-instruction"><strong>Click:</strong> Financial Planning model → a SYS module → find Planning Methods list → verify enabled methods are present.</div>
      <div class="click-instruction"><strong>Click:</strong> a CALC module → open a line item in blueprint → show the formula is populated (not blank).</div>
      <div class="facilitator-note">Tip for participants: export error log to Excel before the error review session. Filter by Error Type to group similar errors. This is much faster than working through them one at a time in-app.</div>

      <hr class="section-divider">

      <h3>Error Log Review <span class="timing-badge">30 min</span></h3>
      <div class="script-block">
        <p>Welcome back. Now we get into the error logs. This is new content — IFP 2.1 introduced systematic error reporting that prior versions did not have. Learning to read these efficiently is one of the key skills you will take out of today.</p>
        <p>The four error types you will see: LINE_ITEM_FORMULA, LIST_PROPERTY_FORMULA, IMPORT_ACTION, and MODEL_DATA_SOURCE. The most common is LINE_ITEM_FORMULA. Let me show you how to track one down.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Application Framework → Error Logs. Filter to LINE_ITEM_FORMULA. Pick the first error. Note the code (underscore prefix).</div>
      <div class="script-block">
        <p>See this code — the underscore at the beginning? That is the error line item code. Go into the model, search for that code in Line Items, and it takes you directly to the problem.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Financial Planning model → Line Items search → paste the error code → navigate to the module → show the formula in blueprint → compare to base model formula.</div>
      <div class="script-block">
        <p>Most of the time, the error is because we changed a hierarchy name, or we removed a geography level, or we changed a dimension that the formula references. The fix is usually to copy the base model formula and adjust it for your configuration choices.</p>
        <p>The goal is not zero errors before you proceed. The goal is to understand which errors matter — fix the ones that block testing, log the ones that do not, and escalate anything that looks like a platform issue rather than a configuration issue.</p>
      </div>

      <hr class="section-divider">

      <h3>Error Log Lab <span class="timing-badge">30 min</span></h3>
      <div class="script-block">
        <p>Now you do it. Open the error log, export to Excel if you have more than 10 errors, and categorize every error by type. Find at least one LINE_ITEM_FORMULA error, track it to the line item, compare to the base model, and either fix it or document why you cannot.</p>
      </div>
      <div class="facilitator-note">Circulate during the error lab. Common issues: participants searching by display name instead of code, participants trying to fix IMPORT_ACTION errors (tell them to log those and move on for now), participants who cannot find the base model (provide the workspace link).</div>

      <hr class="section-divider">

      <h3>Session 1 Wrap <span class="timing-badge">15 min</span></h3>
      <div class="script-block">
        <p>Good work today. Let me summarize what you have covered: Application Framework configuration, model generation, post-generation verification, and error log triage. These are the core skills for Phase 1 and Phase 2 of an IFP delivery.</p>
        <p>Tomorrow we do extensions. You will build two real extensions that you can take directly into a customer engagement. See you at 9 AM.</p>
      </div>

      <hr class="section-divider">

      <div class="day-banner">📚 Session 2 — Extensions</div>

      <h3>Session 2 Opening &amp; Recap <span class="timing-badge">15 min</span></h3>
      <div class="script-block">
        <p>Welcome back. Quick recap of Session 1: we configured the Application Framework Configurator, generated the model, verified it, and worked through the error logs. This session is extensions — adding customer-specific capabilities on top of the generated base model.</p>
        <p>We are going to cover the extension framework, then build two extensions hands-on: direct input with adjustment layers, and a headcount-driven rate planning method. Both of these come up in almost every IFP engagement.</p>
      </div>
      <div class="facilitator-note">Confirm: everyone can access the training base model (IFPv2.0.0-train-base-financial-planning). If anyone cannot access it, resolve before proceeding to Extensions.</div>

      <hr class="section-divider">

      <h3>Extensions Overview <span class="timing-badge">30 min</span></h3>
      <div class="script-block">
        <p>Three types of extensions: Configure, Extend, Modify. Configure is always first — use The Application Framework Configurator if it has a setting. Extend adds new things without touching base model formulas — low upgrade risk. Modify changes existing base model formulas — highest upgrade risk.</p>
        <p>Before I touch any extension in an IFP model, I create two data tags: Extension and Formula Override. These are the only in-model records of what I changed. Without them, the next consultant on the project — or you, six months later — has no way to know what is base model and what is custom.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Show the OpEx planning UX — demonstrate what Direct Input looks like now (single value, no adjustments). This is what you are extending in Extensions.</div>

      <hr class="section-divider">

      <h3>Extension Exercises 1 &amp; 2 <span class="timing-badge">90 min</span></h3>
      <div class="script-block">
        <p>The two extension exercises in the lab guide walk through the builds end-to-end. Have participants work through Exercise 1 (Manual Adjustments) first, then Exercise 2 (Channel Revenue Planning). Both are run against the pre-populated TRAIN models — not the configured models from Session 1 — because extensions need data to demonstrate behavior end-to-end.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Lab guide → Extension Exercise 1, then Extension Exercise 2. Walk through one or two steps to demonstrate the format, then turn participants loose.</div>
      <div class="facilitator-note">Float during this block. Participants will hit blockers (formula references, dimension mismatches, data tag confusion). The two most common issues: (1) forgetting to tag the change as an Extension or Formula Override; (2) building against their configured model instead of the TRAIN base. Catch both early.</div>

      <hr class="section-divider">

      <h3>Workshop Wrap-up <span class="timing-badge">15 min</span></h3>
      <div class="script-block">
        <p>That\'s a wrap. Three things before we close out.</p>
        <p>First, please run through the Workshop Wrap-up checklist in the lab guide — five tenant cleanup tasks so the next cohort gets a clean slate. Most important: delete the configured (non-TRAIN) apps and models, clear your Dataspace, restore hierarchies and configuration questions to default, and restore the Financial Planning model to its pre-extension ID. The wrap-up page has the exact ID for Application Training tenants 00–60.</p>
        <p>Second, the post-workshop survey. Five minutes. We read every response and use them to cut what didn\'t help and reinforce what did. Honest feedback shapes the next cohort.</p>
        <p>Third, the Slack channel stays open. Drop in any time you hit something on a real engagement and want a second opinion. Our IFP product experts are still there.</p>
      </div>
      <div class="click-instruction"><strong>Click:</strong> Lab guide → Workshop Wrap-up. Walk through the 5-item cleanup checklist on screen so participants know exactly what to do.</div>
      <div class="facilitator-note">Send the post-workshop survey link in Slack and via email immediately after the session — response rates drop fast once people move on. The thank-you email template is in the Facilitator Launch Toolkit.</div>

      <hr class="section-divider">',
        'checks' => [
        ],
        'sort_order' => 100,
    ],
    1 => [
        'slug' => 'facilitator-launch-toolkit',
        'title' => 'Facilitator Launch Toolkit',
        'body' => '<div class="callout-warning">
        <span class="callout-label">🔒 Private — Do Not Share This URL Publicly</span>
        <p>This page contains facilitator-internal content (emails, scripts, prep materials). It\'s reachable only via a non-guessable URL and is excluded from search engines. Share the URL with other facilitators directly, not in public partner channels.</p>
        <p style="margin-top:0.5rem;">Companion pages: Facilitator Run-of-Show (session execution) · Progress Report (participation + survey deltas).</p>
      </div>

      <div class="toolkit-toc">
        <strong>Jump to:</strong>
        <ol>
          <li><a href="#promote">Promote &amp; Invite</a> — invitations, social, PDF overview</li>
          <li><a href="#registered">After Registration</a> — confirmation, day-before, tenant credentials</li>
          <li><a href="#kickoff">Workshop Morning</a> — kickoff email, Slack welcome, live intro script</li>
          <li><a href="#during">During the Week</a> — mid-week check-in, Slack cheat sheet</li>
          <li><a href="#wrapup">Wrap-up</a> — thank-you email, survey reminder</li>
          <li><a href="#reference">Reference</a> — fact sheet, URLs, branding</li>
        </ol>
      </div>

      <div class="placeholder-note">
        <strong>Placeholders to fill in throughout:</strong> <code>[FIRST NAME]</code>, <code>[YOUR NAME]</code>, <code>[ZOOM LINK]</code>, <code>[WORKSHOP URL]</code>, <code>[END DATE]</code>, <code>[USERNAME]</code>, <code>[PASSWORD]</code>, <code>[NN]</code> (tenant number), <code>[SURVEY URL]</code>, <code>[RECORDING URL]</code>. Search for the square brackets in any email before sending.
      </div>

      <!-- ============================================================ -->
      <h2 id="promote" class="section-heading">1. Promote &amp; Invite</h2>
      <p>Before participants register. Use these to drive sign-ups from partner Slack channels, LinkedIn, and direct email.</p>

      <!-- Short invitation -->
      <div class="asset">
        <h3>Short Invitation Email (~200 words)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Direct invite to known partners. Best when you want a quick yes.</p>
        <details>
          <summary>Rendered preview</summary>
          <div class="asset-preview">
            <p class="subject-line"><strong>Subject:</strong> Hands-On Virtual Enablement: IFP Delivery Workshop — Reserve Your Seat (and Your Tenant!)</p>
            <p>Hi team,</p>
            <p>We\'re running the <strong>IFP Delivery Workshop</strong> — a hands-on, fully self-paced virtual program covering the complete IFP 2.1 delivery workflow: Application Framework configuration, model generation, error log resolution, and building customer extensions from scratch.</p>
            <p>You\'ll get your <strong>own dedicated training tenant</strong> for the week of your session — yours Monday through Friday, then refreshed and reassigned to the following week\'s participant. Most participants finish the workshop, labs, and supporting videos in <strong>an hour or two a day</strong>, so plan to start on day 1.</p>
            <p><strong>Who it\'s for:</strong> Anaplan delivery consultants at partner firms implementing IFP — or expecting to in the next 12 months.</p>
            <p><strong>Prerequisites:</strong></p>
            <ul>
              <li>MB, SA, or Master Anaplanner certification</li>
              <li>Prior Anaplan model-building experience</li>
              <li>Familiarity with financial planning (P&amp;L, balance sheet, headcount)</li>
              <li>Registration via one of the links below</li>
            </ul>
            <h4>Register</h4>
            <p>Two sessions, 50 seats each. If June 9 fills up, grab a seat on June 15.</p>
            <ul>
              <li><strong>June 9 — IFP Virtual Workshop</strong> → <a href="https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&amp;hash=793de368169e514c7d9ae274d32bba05cd6f2ec0">Register</a></li>
              <li><strong>June 15 — IFP Virtual Workshop</strong> → <a href="https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&amp;hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd">Register</a></li>
            </ul>
            <p><a href="https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95">Full course details on Anaplan Academy</a></p>
            <p>— [YOUR NAME]</p>
          </div>
        </details>
        <details>
          <summary>Copy plain text</summary>
          <p class="copy-hint">Click into the box, Cmd+A, Cmd+C.</p>
          <textarea readonly>Subject: Hands-On Virtual Enablement: IFP Delivery Workshop — Reserve Your Seat (and Your Tenant!)

Hi team,

We\'re running the IFP Delivery Workshop — a hands-on, fully self-paced virtual program covering the complete IFP 2.1 delivery workflow: Application Framework configuration, model generation, error log resolution, and building customer extensions from scratch.

You\'ll get your own dedicated training tenant for the week of your session — yours Monday through Friday, then refreshed and reassigned to the following week\'s participant. Most participants finish the workshop, labs, and supporting videos in an hour or two a day, so plan to start on day 1.

Who it\'s for: Anaplan delivery consultants at partner firms implementing IFP — or expecting to in the next 12 months.

Prerequisites:
- MB, SA, or Master Anaplanner certification
- Prior Anaplan model-building experience
- Familiarity with financial planning (P&L, balance sheet, headcount)
- Registration via one of the links below

Register
--------

Two sessions, 50 seats each. If June 9 fills up, grab a seat on June 15.

- June 9 — IFP Virtual Workshop:
  https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&hash=793de368169e514c7d9ae274d32bba05cd6f2ec0

- June 15 — IFP Virtual Workshop:
  https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd

Full course details on Anaplan Academy:
https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- Long invitation -->
      <div class="asset">
        <h3>Long Invitation Email (~500 words)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Broader announcement, partners who need more context before committing. Includes outcomes, why-it-matters, and workshop modules.</p>
        <details>
          <summary>Rendered preview</summary>
          <div class="asset-preview">
            <p class="subject-line"><strong>Subject:</strong> Hands-On Virtual Enablement: IFP Delivery Workshop — Reserve Your Seat (and Your Tenant!)</p>
            <p>Hi team,</p>
            <p>We\'re running the <strong>IFP Delivery Workshop</strong> — a hands-on, fully self-paced virtual program that takes Anaplan partner practitioners through the complete IFP 2.1 delivery workflow: Application Framework configuration, model generation, error log resolution, and building real customer-specific extensions from scratch.</p>
            <p>If you\'ve ever generated an IFP model, hit an error log, and weren\'t sure whether to escalate or fix it yourself — this workshop is built to close that gap.</p>
            <h4>What you\'ll learn</h4>
            <p>You\'ll work through the full IFP delivery lifecycle. Application Framework configuration (all 44 questions, end-to-end). Model generation and post-generation verification. Error log review — read, categorize, and resolve generation errors without escalating. Extensions from scratch (two production-grade builds). Anaplan Way for Applications — the six-phase delivery methodology.</p>
            <h4>Format &amp; sessions</h4>
            <p>The workshop is <strong>entirely virtual and self-paced</strong>. Most participants finish the workshop, labs, and supporting videos in <strong>an hour or two a day</strong>. Each participant is assigned their <strong>own dedicated training tenant</strong>, available <strong>only during the week of your registered session</strong>.</p>
            <p>Two sessions, 50 seats each:</p>
            <ul>
              <li><strong>June 9 — IFP Virtual Workshop</strong> → <a href="https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&amp;hash=793de368169e514c7d9ae274d32bba05cd6f2ec0">Register for June 9</a></li>
              <li><strong>June 15 — IFP Virtual Workshop</strong> → <a href="https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&amp;hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd">Register for June 15</a></li>
            </ul>
            <p><strong>Plan for the week you register</strong> — your dedicated training tenant is yours Monday through Friday, then refreshed and reassigned to next week\'s participant. There\'s no extending access, so pick a session that fits a week you can give the workshop real attention, and plan to start on day 1.</p>
            <p><em>[Preview truncated — see plain text for full version]</em></p>
          </div>
        </details>
        <details>
          <summary>Copy plain text</summary>
          <p class="copy-hint">Click into the box, Cmd+A, Cmd+C.</p>
          <textarea readonly>Subject: Hands-On Virtual Enablement: IFP Delivery Workshop — Reserve Your Seat (and Your Tenant!)

Hi team,

We\'re running the IFP Delivery Workshop — a hands-on, fully self-paced virtual program that takes Anaplan partner practitioners through the complete IFP 2.1 delivery workflow: Application Framework configuration, model generation, error log resolution, and building real customer-specific extensions from scratch.

If you\'ve ever generated an IFP model, hit an error log, and weren\'t sure whether to escalate or fix it yourself — this workshop is built to close that gap.

What you\'ll learn
-----------------

You\'ll work through the full IFP delivery lifecycle:

- Application Framework configuration — all 44 configuration questions, end-to-end
- Model generation & post-generation verification — what generation produces, what to check, and why
- Error log review — read, categorize, and resolve generation errors without escalating
- Extensions from scratch — two production-grade builds: a multi-adjustment direct input method and a headcount-driven rate method
- Anaplan Way for Applications — the six-phase delivery methodology that frames every IFP engagement

Why this matters
----------------

IFP is delivered through the Application Framework — not built from scratch. That makes configuration, generation, and error log triage the core of the delivery skillset, not custom modeling. The configuration decisions, the post-generation checklist, and the data tag discipline for extensions aren\'t optional — they ARE the delivery method. Every lab happens in your own assigned training tenant with pre-staged IFP models, using real planning scenarios (OpEx/Headcount, Insurance 3-Statement, Nuclear Plant OpEx).

By the end, you should be able to:
- Configure the Application Framework end-to-end and generate a functional IFP model
- Read and resolve generation error logs without escalating basic formula errors
- Build a direct-input extension with adjustment layers
- Build a headcount-driven rate planning method
- Apply Configure-vs-Extend-vs-Modify discipline and data tag every change you make

Who should attend
-----------------

Anaplan delivery consultants at partner firms who are implementing IFP for customers — or expect to in the next 12 months. This is a practitioner workshop, not a product overview.

Prerequisites:
- You must register using one of the links below
- Model Builder (MB), Solution Architect (SA), or Master Anaplanner certification
- Prior experience building or configuring Anaplan models
- Familiarity with financial planning concepts (P&L, balance sheet, headcount)

Format & sessions
-----------------

The workshop is entirely virtual and self-paced — work through the content on your own schedule, in any order. Most participants find an hour or two a day is enough to fully complete the workshop, labs, and supporting videos within the week.

Each participant is assigned their own dedicated training tenant for the week of their registered session. Tenants get refreshed over the weekend and assigned to next week\'s participant, so plan to start on day 1 of your week — there\'s no extending access. Pick the session that fits a week you can give the workshop real attention.

We\'re running two sessions, with capacity for 50 participants each. If the first session fills up, please register for the second.

- June 9 — IFP Virtual Workshop:
  https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&hash=793de368169e514c7d9ae274d32bba05cd6f2ec0

- June 15 — IFP Virtual Workshop:
  https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd

Course details and general information:
https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95

Looking forward to working through this with you.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- LinkedIn short -->
      <div class="asset">
        <h3>LinkedIn / Social — Short Post</h3>
        <p class="asset-meta"><strong>Use when:</strong> Quick LinkedIn share. Fits under most character limits including X/Twitter.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Anaplan partners — we\'re running a hands-on, fully self-paced IFP Delivery Workshop. 1–2 hours/day for a week, your own training tenant (Mon–Fri, then it rolls to next week\'s participant), IFP product experts on Slack throughout. Two June sessions opening — 50 seats each.

Register: https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95

#Anaplan #IFP #FinancialPlanning #Workshop
</textarea>
        </details>
      </div>

      <!-- LinkedIn long -->
      <div class="asset">
        <h3>LinkedIn — Long Post</h3>
        <p class="asset-meta"><strong>Use when:</strong> Anchor announcement on LinkedIn or partner blog.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>🚀 IFP Delivery Workshop — registration open

If you\'re an Anaplan delivery consultant implementing IFP for customers (or you expect to in the next 12 months), our hands-on workshop is built to close the gap between knowing about the Application Framework and actually delivering it.

What you get:
✅ Your own dedicated training tenant (yours Monday–Friday, then refreshed for the next participant)
✅ All 44 Application Framework configuration questions, end-to-end
✅ Error log triage and resolution
✅ Two production-grade extension builds, from scratch
✅ IFP product experts on Slack throughout the workshop week

Fully virtual, fully self-paced. Most participants finish in an hour or two a day.

Two sessions opening in June, 50 seats each. Reserve your seat:
👉 https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95

#Anaplan #IFP #IntegratedFinancialPlanning #PartnerEnablement
</textarea>
        </details>
      </div>

      <!-- Internal Slack -->
      <div class="asset">
        <h3>Internal Partner Slack Announcement</h3>
        <p class="asset-meta"><strong>Use when:</strong> Posting in Anaplan partner Slack channels to drive registrations.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>👋 Heads up — registration is open for the IFP Delivery Partner Workshop.

Hands-on, fully self-paced. Get your own dedicated training tenant for the week (Mon–Fri, then it rolls to the next cohort, so plan to start on day 1), work through all 44 Application Framework configuration questions, build two real extensions, and have our IFP product experts on Slack the whole time.

Two June sessions, 50 seats each.

June 9: https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&hash=793de368169e514c7d9ae274d32bba05cd6f2ec0
June 15: https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd

DM me with questions.
</textarea>
        </details>
      </div>

      <!-- PDF -->
      <div class="asset">
        <h3>Front/Back PDF Overview</h3>
        <p class="asset-meta"><strong>Use when:</strong> Forwarding a polished one-pager to a partner who hasn\'t seen the workshop yet. Attach to email or share via Drive.</p>
        <p>Page 1: what it is + outcomes. Page 2: format, sessions, prerequisites, register.</p>
        <p style="margin-top: 0.75rem;">
          <a class="download-link" href="../assets/ifp-workshop-overview.pdf" download>⬇ Download IFP Workshop Overview (PDF)</a>
        </p>
        <p class="copy-hint">PDF is regenerated from <code>docs/printable-workshop-overview.html</code> if the source content changes.</p>
      </div>

      <!-- ============================================================ -->
      <h2 id="registered" class="section-heading">2. After Registration</h2>
      <p>Participant has signed up. Send these in order leading up to the workshop start.</p>

      <!-- Registration confirmation -->
      <div class="asset">
        <h3>Registration Confirmation Email</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send right after a participant registers. Confirms they\'re in and sets expectations for the week ahead.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: You\'re In — IFP Delivery Workshop opens Tuesday, June 9

Hi [FIRST NAME],

You\'re confirmed for the IFP Delivery Workshop. Here\'s what to expect.

What happens Tuesday, June 9
----------------------------

- Your training tenant goes live in the morning and stays yours through the workshop week
- Workshop content opens the same morning — work through it at your own pace
- Live kickoff session at 12:00 PM Eastern — optional but recommended; we\'ll walk through the workshop structure, surface common pitfalls, and answer any questions before you dive in
- Can\'t make the live session? No problem. A recording will be posted shortly after, and the workshop is fully self-paced.

Most participants finish the workshop, labs, and supporting videos in an hour or two a day.

What you\'ll get the morning of June 9
-------------------------------------

- A link to the workshop lab guide
- The live kickoff Zoom link (or recording link, whichever you prefer)
- An invite to our Slack channel where IFP product experts will be on hand all week to answer questions

Please be ready to participate
------------------------------

- There is great demand for this workshop globally, as all available seats have been filled in every scheduled session so far.
- Your training tenant is yours Monday through Friday — then it gets refreshed over the weekend and assigned to the next participant. Access ends Friday, June 19, and isn\'t extended, so please plan to engage from day 1.

Anything to do beforehand?
--------------------------

Nothing required. If you want to skim ahead, the workshop overview is open now:
[WORKSHOP URL]/docs/01-overview.html

See you Tuesday.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- Day-before welcome -->
      <div class="asset">
        <h3>Day-Before Welcome Email (with Pre-Workshop Survey)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send the day before the workshop opens. Asks for the pre-workshop survey to tune content.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: Welcome — Your IFP Delivery Workshop Starts Tomorrow

Hi [FIRST NAME],

Tomorrow your IFP Delivery Workshop begins. Your dedicated training tenant goes live in the morning and stays yours for the full week — long enough to work through every section, every lab, and every video at the pace that works for you.

Before you log in, take 5 minutes to do one thing.

Tell us what you need
---------------------

👉 Pre-workshop survey — 5 minutes: [SURVEY URL]

This is the most important thing you can do today. Our goal for this workshop is simple: every minute you spend in it should be worth spending. To make that real, we need to know what YOU are bringing into the week — what you already know, what you\'re stuck on, what you\'re hoping to walk away able to do.

We use your answers to:
- Cut what doesn\'t help you — sections you already know, concepts that aren\'t relevant
- Reinforce what does — extra time on the topics this cohort is least confident in
- Tune the labs — so the scenarios match the kinds of customers you\'re working with

You\'ll get a second survey at the end of the week. That one tells us whether we delivered. The two together are how this workshop gets better every time we run it.

What\'s waiting for you tomorrow
-------------------------------

- Your training tenant credentials — arriving in a separate email tomorrow morning
- The workshop lab guide — your home base for the week: [WORKSHOP URL]
- A short welcome video — the first thing to watch when you log in
- About an hour or two a day — most participants finish everything in well under 10 hours over the week

You don\'t need to do anything else to prepare. Just take the survey, get a good night\'s sleep, and we\'ll see you in the tenant tomorrow.

— [YOUR NAME]

P.S. If anything in your tenant doesn\'t look right when you log in tomorrow, reply to this email immediately — we\'d rather fix it in the first hour than have you lose a day.
</textarea>
        </details>
      </div>

      <!-- Tenant credentials -->
      <div class="asset">
        <h3>Tenant Credentials Email</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send the morning the workshop opens with each participant\'s specific tenant login.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: Your IFP Workshop Tenant Access is Ready

Hi [FIRST NAME],

Your training tenant for the IFP Delivery Workshop is provisioned. Here\'s everything you need to get started.

Your tenant access
------------------

- URL: https://us1a.app.anaplan.com/
- Username: [USERNAME]
- Temporary password: [PASSWORD] (you\'ll be prompted to change this on first login)
- Tenant name: Application Training [NN]

Required roles (already assigned to you)
----------------------------------------

- Page Builder
- Application Owner
- Integration Admin
- Workspace Admin

Dataspace (already assigned to you)
-----------------------------------

- Dataspace [NN]

Where to start
--------------

1. Log in and confirm access. Full instructions: [WORKSHOP URL]/docs/02-tenant-access.html
2. Open the workshop lab guide and start with Workshop Overview: [WORKSHOP URL]
3. Drop into Slack (link below) if you hit any access issues

Your tenant is yours through [END DATE] — work at your own pace.

Post questions in the Discussion panel on your workshop page - IFP product experts monitor it all week.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- ============================================================ -->
      <h2 id="kickoff" class="section-heading">3. Workshop Morning</h2>
      <p>The day the workshop opens. Send the kickoff email, post the Slack channel welcome, and run the live intro.</p>

      <!-- Morning kickoff -->
      <div class="asset">
        <h3>Morning Kickoff Email (with workshop link &amp; Zoom)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send the morning the workshop opens. Includes the workshop URL and the live Zoom kickoff link.</p>
        <details>
          <summary>Rendered preview</summary>
          <div class="asset-preview">
            <p class="subject-line"><strong>Subject:</strong> Good morning — your IFP Workshop opens today</p>
            <p>Hi [FIRST NAME],</p>
            <p>Your IFP Delivery Workshop opens this morning. Here\'s everything you need.</p>
            <h4>🎯 Workshop lab guide</h4>
            <p><a href="[WORKSHOP URL]">[WORKSHOP URL]</a> — start with Workshop Overview, then Tenant Access.</p>
            <h4>📅 Live kickoff session at 12:00 PM Eastern (optional but recommended)</h4>
            <p>Join via Zoom: <a href="[ZOOM LINK]">[ZOOM LINK]</a></p>
            <p>We\'ll walk through the workshop structure, surface common pitfalls, and answer questions. Can\'t make it? A recording will be posted by end of day, and the workshop is fully self-paced regardless.</p>
            <h4>💬 Questions during the week → Discussion panel</h4>
            <p>Point participants to the Discussion panel on their workshop page. Our IFP product experts are on hand all week and reply there directly.</p>
            <h4>What to expect this week</h4>
            <ul>
              <li>1–2 hours per day is enough to complete the workshop, labs, and supporting videos</li>
              <li>Your dedicated training tenant is yours through <strong>[END DATE]</strong></li>
              <li>Work in whatever order makes sense — the lab guide is fully self-paced</li>
            </ul>
            <p>Got tenant credentials yesterday but haven\'t logged in yet? Now\'s the time. If you hit any issues, ping me or drop them in Slack.</p>
            <p>Looking forward to working through this with you.</p>
            <p>— [YOUR NAME]</p>
          </div>
        </details>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: Good morning — your IFP Workshop opens today

Hi [FIRST NAME],

Your IFP Delivery Workshop opens this morning. Here\'s everything you need.

🎯 Workshop lab guide
---------------------

[WORKSHOP URL]

Start with Workshop Overview, then Tenant Access.

📅 Live kickoff session at 12:00 PM Eastern (optional but recommended)
---------------------------------------------------------------------

Join via Zoom: [ZOOM LINK]

We\'ll walk through the workshop structure, surface common pitfalls, and answer questions. Can\'t make it? A recording will be posted by end of day, and the workshop is fully self-paced regardless.

💬 Questions during the week → Discussion panel
---------------------------------------------

Point them to the Discussion panel on their workshop page. Our IFP product experts are on hand all week and reply there directly.

What to expect this week
------------------------

- 1–2 hours per day is enough to complete the workshop, labs, and supporting videos
- Your dedicated training tenant is yours through [END DATE]
- Work in whatever order makes sense — the lab guide is fully self-paced

Got tenant credentials yesterday but haven\'t logged in yet? Now\'s the time. If you hit any issues, ping me or drop them in Slack.

Looking forward to working through this with you.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- Slack channel welcome -->
      <div class="asset">
        <h3>Slack Channel Welcome Message (Pinned)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Post and pin in the workshop Slack channel on Day 1 morning so newcomers see it.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>👋 Welcome to the IFP Delivery Workshop channel!

This is your direct line to IFP product experts during the workshop week. A few things:

Ask anything. Configuration questions, generation errors, "is this normal," "how would you scope this for a real customer" — all fair game. Threading is encouraged so the channel stays scannable.

Tag specific topics if helpful:
🔧 Configuration questions
🪵 Error log help
⚡ Extension building
🧭 Anaplan Way / methodology

Workshop lab guide: [WORKSHOP URL]
Live kickoff today at 12 ET: [ZOOM LINK]

Daily hours: Experts are most active 9 AM – 5 PM Eastern, but you\'ll usually get a reply within an hour during the workshop week. Channel stays open after the workshop closes too.

Glad you\'re here. Let\'s go.
</textarea>
        </details>
      </div>

      <!-- Slack channel intro post (warm, conversational) -->
      <div class="asset">
        <h3>Slack Channel Intro Post (warm welcome)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Post in the channel as a regular (not pinned) message when participants start joining. Sets a low-barrier, ask-anything tone. Complements the more functional pinned welcome above.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>👋 Welcome to the workshop — glad you\'re here!

This channel is your direct line to the IFP product team during the workshop week. We\'re here, we\'re watching, and we\'ll respond fast.

A few things to know:

🙋 Post your questions. Configuration questions, "is this normal?", "did I break something?", "how would you scope this for a real customer?" — all fair game. Don\'t be shy.

🧵 Threading helps. Reply in thread on someone else\'s question if you\'ve got the same one — keeps the channel scannable.

⚡ No question is too small. If you\'re stuck, ask. We\'d rather help you in 5 minutes than have you bang your head against a problem for two hours.

👀 Lurking is fine too. Some of the best learning happens by watching others work through their issues. Skim the channel daily even if you don\'t have your own questions.

Happy to have you here. Let\'s make this a good week.
</textarea>
        </details>
      </div>

      <!-- Kickoff script -->
      <div class="asset">
        <h3>Live Kickoff Intro Script (~10 min)</h3>
        <p class="asset-meta"><strong>Use when:</strong> Running the noon Eastern Zoom kickoff. Talking points, not a read-aloud script — adapt to your style.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>LIVE KICKOFF INTRO — TALKING POINTS (~10 min)

Adapt freely to your delivery style. Use these as anchors.

OPEN (1 min)
------------
Welcome everyone. Quick agenda: about 10 minutes from me on what to expect this week, then Q&A. Recording will be posted later today for anyone who couldn\'t join live.

WHAT THIS WORKSHOP IS (2 min)
-----------------------------
Five sentences:
1. IFP is delivered through the Application Framework — not built from scratch.
2. Configuration, generation, and error log triage are the core skills.
3. Two extension builds, from scratch, mirroring real customer asks.
4. Your tenant is provisioned and yours through end of week.
5. Anaplan IFP product experts are on Slack throughout the workshop week to answer questions.

THE LAB GUIDE (2 min)
---------------------
[Screen share the workshop landing page]
Sidebar navigation: Getting Started → Configuration → Extensions → Reference.
Work at your own pace. Most participants finish in an hour or two a day.
The "Read · Watch · Do" pattern is consistent across sections.

THREE THINGS TO NOT MESS UP (2 min)
-----------------------------------
1. Do not generate against a production tenant.
2. Confirm your roles AND dataspace assignment before generating — biggest reason people get stuck.
3. Use the TRAIN models for extension exercises, not the ones you generated yourself.

WHERE TO GET HELP (1 min)
-------------------------
Slack channel is the fastest path. Link\'s in the lab guide. No Slack? Email-into-channel address is there too.

AT THE END OF THE WEEK (1 min)
------------------------------
Workshop Wrap-up page has the tenant cleanup checklist — please run through it before you log out for the last time. Post-workshop survey link is at the top of the wrap-up page.

Q&A (1+ min)
------------
Open the floor. Common questions to be ready for:
- "How long does generation take?" → 20–40 min
- "Can I extend my tenant access?" → Case by case, contact me
- "Do I need to attend the live kickoff to count as participating?" → No, fully self-paced
</textarea>
        </details>
      </div>

      <!-- ============================================================ -->
      <h2 id="during" class="section-heading">4. During the Week</h2>
      <p>Keep momentum, surface blockers, and have ready answers for the common questions.</p>

      <!-- Mid-week check-in -->
      <div class="asset">
        <h3>Mid-Week Check-In Email</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send Wednesday or Thursday of the workshop week. Surfaces blockers and reminds about Slack.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: Halfway through — quick check-in

Hi [FIRST NAME],

Quick midweek nudge from the IFP Workshop. Three things:

1. How\'s it going?
   Drop a thumbs-up or thumbs-down in the Discussion panel on your workshop page
   so I can see if anything\'s stuck.

2. Reminder: experts monitor the Discussion panel all week.
   If you hit something weird in generation, or you\'re not sure if a formula error is a real problem — that\'s exactly what it\'s for. Don\'t bang your head on it for hours.

3. Pacing.
   Most participants are through the Configuration section by now and into Extensions. If you\'re behind, no stress — you\'ve got the rest of the week. If you\'re ahead, the Reference section has the Q&A from prior sessions which is worth skimming.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- Slack cheat sheet -->
      <div class="asset">
        <h3>Common Questions — Slack Cheat Sheet</h3>
        <p class="asset-meta"><strong>Use when:</strong> Quick reference for facilitators answering in Slack. Paste any of these as a starting point for your reply.</p>
        <details>
          <summary>View cheat sheet</summary>
          <div class="asset-preview">
            <p><strong>"Generation is taking forever"</strong> → 20–40 min is normal. Watch the <code>/applications</code> page, not the App Framework page (status indicator lags inside the Application Framework).</p>
            <p><strong>"I see errors after generation"</strong> → That\'s expected. Page 09 (Error Log Review) walks the diagnostic. Most are <code>LINE_ITEM_FORMULA</code> — fixable by copying from the base TRAIN model.</p>
            <p><strong>"Which models do I use for extensions?"</strong> → The pre-populated TRAIN models (with <code>TRAIN</code> prefix), not the ones you generated. The TRAIN models have data; extensions need data to demonstrate behavior.</p>
            <p><strong>"Can I delete a hierarchy level?"</strong> → Yes, but only middle levels — the leaf must stay at the bottom. When adding levels, drag new ones above the original leaf.</p>
            <p><strong>"What if I make a mistake in configuration?"</strong> → Restore Default per hierarchy (one at a time — no global reset). Or regenerate — you have the rest of the week.</p>
            <p><strong>"How do I know if my formula error is a real bug or my fault?"</strong> → Compare against the base TRAIN model. If the formula works there with similar config, it\'s something you renamed or changed during configuration.</p>
            <p><strong>"My generation hit an error and now I can\'t restart"</strong> → Confirm your four roles (Page Builder, Application Owner, Integration Admin, Workspace Admin) and Dataspace assignment with the tenant admin. Most "stuck" generations are actually permissions problems.</p>
            <p><strong>"Where\'s the recording of the live kickoff?"</strong> → Posted in the Slack channel as a pinned message. Also available at [RECORDING URL].</p>
          </div>
        </details>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>COMMON QUESTIONS — SLACK CHEAT SHEET

"Generation is taking forever"
→ 20–40 min is normal. Watch the /applications page, not the App Framework page (status indicator lags inside the Application Framework).

"I see errors after generation"
→ That\'s expected. Page 09 (Error Log Review) walks the diagnostic. Most are LINE_ITEM_FORMULA — fixable by copying from the base TRAIN model.

"Which models do I use for extensions?"
→ The pre-populated TRAIN models (with TRAIN prefix), not the ones you generated. The TRAIN models have data; extensions need data to demonstrate behavior.

"Can I delete a hierarchy level?"
→ Yes, but only middle levels — the leaf must stay at the bottom. When adding levels, drag new ones above the original leaf.

"What if I make a mistake in configuration?"
→ Restore Default per hierarchy (one at a time — no global reset). Or regenerate — you have the rest of the week.

"How do I know if my formula error is a real bug or my fault?"
→ Compare against the base TRAIN model. If the formula works there with similar config, it\'s something you renamed or changed during configuration.

"My generation hit an error and now I can\'t restart"
→ Confirm your four roles (Page Builder, Application Owner, Integration Admin, Workspace Admin) and Dataspace assignment with the tenant admin. Most "stuck" generations are actually permissions problems.

"Where\'s the recording of the live kickoff?"
→ Posted in the Slack channel as a pinned message. Also available at [RECORDING URL].
</textarea>
        </details>
      </div>

      <!-- ============================================================ -->
      <h2 id="wrapup" class="section-heading">5. Wrap-up</h2>
      <p>Close the loop, get the survey filled out, hand off to the long-term Slack relationship.</p>

      <!-- Thank you -->
      <div class="asset">
        <h3>Thank-You / Wrap-Up Email</h3>
        <p class="asset-meta"><strong>Use when:</strong> Send Friday afternoon or Monday after the workshop closes. Drives survey responses and cements the Slack channel as the ongoing relationship.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Subject: Thank you — see you on the next one

Hi [FIRST NAME],

That\'s a wrap on the IFP Delivery Workshop. Thank you for the time and attention you put into the week.

Three quick things:

1. Tenant cleanup
   If you haven\'t already, please run through the Workshop Wrap-up checklist — five steps to leave the training tenant clean for the next cohort. Takes 10 minutes.
   [WORKSHOP URL]/docs/15-workshop-wrap-up.html

2. Post-workshop survey
   Five minutes: [SURVEY URL]
   We read every response and use them directly to cut what didn\'t add value and reinforce what did. The next cohort gets a better workshop because you fill this out.

3. The Discussion panel stays open
   Our IFP product experts still monitor it. Drop in any time you hit something on a real engagement and want a second opinion.

The lab guide also stays available — bookmark it. Looking forward to working with you again.

— [YOUR NAME]
</textarea>
        </details>
      </div>

      <!-- Survey reminder -->
      <div class="asset">
        <h3>Survey Reminder (Slack post or short email)</h3>
        <p class="asset-meta"><strong>Use when:</strong> 3–5 days after the wrap-up email if survey response rate is low.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>Friendly reminder — if you finished the IFP workshop last week and haven\'t filled out the post-workshop survey yet, would you spare 5 minutes? [SURVEY URL]

We use every response. Especially valuable: what was unclear, what was redundant, what should we have spent more (or less) time on.

Thank you 🙏
</textarea>
        </details>
      </div>

      <!-- Partner Alliances celebration Slack post -->
      <div class="asset">
        <h3>Internal: <code>amer_partners_alliances</code> Cohort Celebration Post</h3>
        <p class="asset-meta"><strong>Use when:</strong> End of the cohort week, after surveys are in. Posted to the Anaplan internal <code>amer_partners_alliances</code> Slack channel to celebrate participating partners, share the pre→post learning gains, and seed registrations for the next cohort. Pull the actual numbers from the Progress Report report.</p>
        <details>
          <summary>Copy plain text</summary>
          <textarea readonly>🎉 IFP Delivery Workshop — [COHORT NAME] just wrapped!

[X] delivery partners sent [Y] consultants through the workshop this week. Quick highlights:

📈 Learning progression
• Pre-workshop survey average: [X.XX]
• Post-workshop survey average: [X.XX]
• Net improvement: [+X.XX points] — biggest gains in [TOPIC area, e.g. "error log triage" or "configurator decisions"]

🏆 Top completion rates by partner
🥇 [Partner A] — [X/X] all three milestones (100%)
🥈 [Partner B] — [X/X] (XX%)
🥉 [Partner C] — [X/X] (XX%)

✅ Knowledge check pass rate: [XX]%
🎯 [XX]% of all participants completed all three milestones (pre-survey + post-survey + passed the knowledge check).

Huge shout-out to [list 3–5 partner firms that stood out — could be by volume, completion, or pre→post improvement] and to every consultant who put in the time. This is the kind of preparation that turns into smoother customer engagements.

🙏 Thanks also to our IFP product team who staffed the Slack channel all week — [count] questions answered, zero left hanging.

Next cohort opens [DATE]. If you have delivery consultants on your team who\'d benefit, get them on the list — every session so far has hit capacity, and we\'re not extending tenant access past the registered week.

[OPTIONAL: link to the Anaplan Academy registration page]
</textarea>
        </details>
      </div>

      <!-- ============================================================ -->
      <h2 id="reference" class="section-heading">6. Reference</h2>
      <p>Quick-grab facts you\'ll want when sending emails, writing Slack replies, or filling out the placeholders above.</p>

      <div class="asset">
        <h3>Workshop Fact Sheet</h3>
        <details open>
          <summary>View facts</summary>
          <div class="asset-preview">
            <p><strong>Workshop name:</strong> IFP Delivery Partner Workshop</p>
            <p><strong>One-line description:</strong> A hands-on, fully self-paced virtual program covering the complete IFP 2.1 delivery workflow — Application Framework configuration, model generation, error log resolution, and building customer-specific extensions from scratch.</p>
            <p><strong>Audience:</strong> Anaplan delivery consultants at partner firms implementing IFP — or expecting to in the next 12 months.</p>
            <p><strong>Format:</strong> Virtual, self-paced, week-long. Optional live kickoff Tuesday 12 PM ET.</p>
            <p><strong>Time commitment:</strong> 1–2 hours per day for one week (~6–10 hours total).</p>
            <p><strong>Capacity per session:</strong> 50 participants.</p>
            <p><strong>Prerequisites:</strong> MB / SA / Master Anaplanner cert. Prior Anaplan model-building experience. Familiarity with P&amp;L, balance sheet, headcount concepts.</p>
            <p><strong>Anaplan Academy course URL:</strong> <a href="https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95">https://academy.anaplan.com/learn/courses/558/integrated-financial-planning-ifp-workshop-v2/sessions/438/BQ5QK95</a></p>
            <p><strong>Session registration links:</strong></p>
            <ul>
              <li>June 9: <a href="https://academy.anaplan.com/learn/courses/558/sessions/438?generated_by=19252&amp;hash=793de368169e514c7d9ae274d32bba05cd6f2ec0">academy.anaplan.com/.../sessions/438</a></li>
              <li>June 15: <a href="https://academy.anaplan.com/learn/courses/558/sessions/440?generated_by=19252&amp;hash=73281f9a7ce5a6df522c4e12c42eb4b2bbddaccd">academy.anaplan.com/.../sessions/440</a></li>
            </ul>
            <p><strong>Workshop lab guide:</strong> participants access it directly from their My Workshops page in the portal - no separate link needed.</p>
            <p><strong>Discussion panel:</strong> available on each participant\'s workshop page, no separate link needed.</p>
            <p><strong>Tenant URL participants log in to:</strong> <code>https://us1a.app.anaplan.com/</code></p>
            <p><strong>Roles required for generation</strong> (assigned during provisioning): Page Builder, Application Owner, Integration Admin, Workspace Admin.</p>
            <p><strong>Critical pre-flight check:</strong> participants must confirm all 4 roles + Dataspace assignment before generating. Page 02 (Tenant Access) walks the check.</p>
          </div>
        </details>
      </div>

      <div class="asset">
        <h3>Tenant Cleanup Reference (for facilitator visibility)</h3>
        <details>
          <summary>The 5-step participant cleanup checklist</summary>
          <div class="asset-preview">
            <p>This is what participants run through at the end of the week. Knowing it lets you answer questions and verify the tenant before the next cohort.</p>
            <ol>
              <li>Delete the applications they configured during exercises (those <em>without</em> the <code>TRAIN</code> prefix)</li>
              <li>Delete the models generated from their configured applications (those <em>without</em> the <code>TRAIN</code> prefix)</li>
              <li>Delete any objects they added to their assigned Dataspace</li>
              <li>Reset hierarchies and configuration questions to default in the Application Framework Configurator (one hierarchy at a time — no global reset)</li>
              <li>Restore the Financial Planning model to its pre-extension ID
                <ul>
                  <li>For Application Training tenants 00–60: ID is <code>2064565</code></li>
                  <li>For EMEA tenants: confirm ID with the EMEA tenant admin</li>
                </ul>
              </li>
            </ol>
            <p>Full participant-facing version: <a href="./15-workshop-wrap-up.html">Workshop Wrap-up page</a>.</p>
          </div>
        </details>
      </div>',
        'checks' => [
        ],
        'sort_order' => 101,
    ],
];
