<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Models\Product;
use App\Models\PsmObjective;
use App\Models\User;
use App\Models\WeeklyFocus;
use App\Models\WeeklyFocusItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritiesSeeder extends Seeder
{
    /**
     * Partner-specific objectives keyed by partner name.
     * Each objective uses realistic values aligned with actual seeded data.
     *
     * @var array<string, list<array>>
     */
    private const PARTNER_OBJECTIVES = [
        // ── Gunnar's partners ──────────────────────────────────────
        'Deloitte' => [
            ['title' => 'OWP delivery blueprint completions — 0 of 14 resources ready', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 6, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 6, 'description' => 'CRITICAL: Deloitte has $800K in OWP pipeline closing Q2-Q3 but zero resources have completed the OWP delivery blueprint. 14 enrollments started, none completed. Must accelerate or deals close with no delivery capacity.'],
            ['title' => 'IFP blueprint completions to 15', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 15, 'baseline' => 4, 'current' => 9, 'status' => 'active', 'weeks_out' => 3, 'description' => 'Deloitte needs deeper IFP delivery bench — push through remaining blueprint completions.'],
            ['title' => 'Build pipeline to $5M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 5000000, 'baseline' => 3200000, 'current' => 4463382, 'status' => 'active', 'weeks_out' => 6, 'description' => 'Deloitte pipeline target for Q2 — on track, need one more large opportunity.'],
            ['title' => 'Register 8 delivery resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 8, 'baseline' => 2, 'current' => 4, 'status' => 'active', 'weeks_out' => 4, 'description' => 'Expand Deloitte delivery bench to support growing project volume.'],
            ['title' => '10 active certifications', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 10, 'baseline' => 5, 'current' => 8, 'status' => 'completed', 'weeks_out' => -2, 'description' => 'Cert push completed — Deloitte now has solid coverage across RPM and IFP.'],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 1, 'status' => 'completed', 'weeks_out' => -5, 'description' => 'Marketplace profile approved and live.'],
        ],
        'PwC' => [
            ['title' => '15 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 15, 'baseline' => 6, 'current' => 11, 'status' => 'active', 'weeks_out' => 4, 'description' => 'PwC leading in enablement — keep momentum on blueprint completions.'],
            ['title' => 'Build pipeline to $5M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 5000000, 'baseline' => 2800000, 'current' => 4238395, 'status' => 'active', 'weeks_out' => 5, 'description' => 'PwC pipeline nearly at target — focus on progressing existing deals to close.'],
            ['title' => 'Submit marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 2, 'description' => 'PwC profile submitted but not yet approved — follow up with marketplace team.'],
            ['title' => '8 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 8, 'baseline' => 3, 'current' => 6, 'status' => 'active', 'weeks_out' => 3, 'description' => 'Need 2 more active certs to hit target.'],
            ['title' => 'Identify 2 new SCM opportunities', 'category' => 'pipeline', 'metric_type' => 'manual', 'target' => 2, 'baseline' => 0, 'current' => 1, 'status' => 'active', 'weeks_out' => 4, 'description' => 'PwC has SCM expertise but no current SCM deals — need to activate.'],
        ],
        'Infosys' => [
            ['title' => 'Build pipeline to $2M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 2000000, 'baseline' => 800000, 'current' => 1396822, 'status' => 'active', 'weeks_out' => 5, 'description' => 'Infosys pipeline growing — need to convert more influenced deals to sourced.'],
            ['title' => '8 active certifications', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 8, 'baseline' => 3, 'current' => 6, 'status' => 'active', 'weeks_out' => 4, 'description' => 'Infosys cert count lagging behind tier expectations.'],
            ['title' => '10 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 10, 'baseline' => 2, 'current' => 5, 'status' => 'active', 'weeks_out' => 5, 'description' => 'Blueprint adoption slow — schedule enablement workshop to accelerate.'],
            ['title' => 'Register 6 delivery resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 6, 'baseline' => 2, 'current' => 4, 'status' => 'active', 'weeks_out' => 3, 'description' => 'Infosys delivery bench needs expansion for upcoming projects.'],
            ['title' => '3 enablement sessions attended', 'category' => 'enablement', 'metric_type' => 'enablement_sessions', 'target' => 3, 'baseline' => 0, 'current' => 3, 'status' => 'completed', 'weeks_out' => -3, 'description' => 'Infosys team attended all three Q1 enablement sessions.'],
        ],
        'IBM' => [
            ['title' => 'Build pipeline to $2M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 2000000, 'baseline' => 900000, 'current' => 1605483, 'status' => 'active', 'weeks_out' => 4, 'description' => 'IBM pipeline strong but need to close gap to $2M target.'],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 2, 'description' => 'IBM marketplace profile still in draft — need partner contact to finalize.'],
            ['title' => '10 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 10, 'baseline' => 4, 'current' => 7, 'status' => 'active', 'weeks_out' => 5, 'description' => 'IBM has strong talent pool — push for more cert completions.'],
            ['title' => '8 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 8, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 6, 'description' => 'Blueprint adoption needs acceleration — schedule 1:1 with enablement lead.'],
            ['title' => 'Identify 1 ADO opportunity', 'category' => 'pipeline', 'metric_type' => 'manual', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 4, 'description' => 'IBM has ADO capability but no current ADO pipeline.'],
            ['title' => 'Register 6 resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 6, 'baseline' => 1, 'current' => 4, 'status' => 'active', 'weeks_out' => 3, 'description' => 'IBM resource registration behind — need to push partner admin.'],
        ],
        'BCG' => [
            ['title' => 'Build pipeline to $1M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 1000000, 'baseline' => 300000, 'current' => 635643, 'status' => 'active', 'weeks_out' => 5, 'description' => 'BCG pipeline growing steadily — need 2-3 more opportunities to hit $1M.'],
            ['title' => '6 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 6, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 4, 'description' => 'BCG cert count low — schedule cert prep sessions with partner team.'],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 2, 'description' => 'BCG marketplace profile in draft — follow up with partner marketing.'],
            ['title' => '8 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 8, 'baseline' => 0, 'current' => 3, 'status' => 'active', 'weeks_out' => 6, 'description' => 'Blueprint adoption just starting — need to establish rhythm.'],
            ['title' => 'Register 5 delivery resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 5, 'baseline' => 0, 'current' => 4, 'status' => 'active', 'weeks_out' => 2, 'description' => 'Almost there — one more resource registration needed.'],
            ['title' => '2 enablement sessions attended', 'category' => 'enablement', 'metric_type' => 'enablement_sessions', 'target' => 2, 'baseline' => 0, 'current' => 2, 'status' => 'completed', 'weeks_out' => -2, 'description' => 'BCG team completed initial enablement sessions.'],
        ],

        // ── Rick's partners ────────────────────────────────────────
        'Accenture' => [
            ['title' => 'IFP blueprint completions to 12', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 12, 'baseline' => 5, 'current' => 9, 'status' => 'active', 'weeks_out' => 3],
            ['title' => 'Build pipeline to $6M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 6000000, 'baseline' => 3500000, 'current' => 5100000, 'status' => 'active', 'weeks_out' => 5],
            ['title' => '12 active certifications', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 12, 'baseline' => 7, 'current' => 12, 'status' => 'completed', 'weeks_out' => -1],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 1, 'status' => 'completed', 'weeks_out' => -4],
        ],
        'KPMG' => [
            ['title' => 'Build pipeline to $1.5M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 1500000, 'baseline' => 500000, 'current' => 980000, 'status' => 'active', 'weeks_out' => 4],
            ['title' => '6 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 6, 'baseline' => 1, 'current' => 4, 'status' => 'active', 'weeks_out' => 3],
            ['title' => 'Register 4 delivery resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 4, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 3],
        ],
        'Wipro' => [
            ['title' => '8 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 8, 'baseline' => 2, 'current' => 6, 'status' => 'active', 'weeks_out' => 4],
            ['title' => '5 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 5, 'baseline' => 2, 'current' => 5, 'status' => 'completed', 'weeks_out' => -1],
            ['title' => 'Identify 1 RPM opportunity', 'category' => 'pipeline', 'metric_type' => 'manual', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 3],
        ],
        'HCL' => [
            ['title' => 'Build pipeline to $1M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 1000000, 'baseline' => 400000, 'current' => 720000, 'status' => 'active', 'weeks_out' => 5],
            ['title' => 'Register 5 resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 5, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 3],
            ['title' => '3 enablement sessions attended', 'category' => 'enablement', 'metric_type' => 'enablement_sessions', 'target' => 3, 'baseline' => 0, 'current' => 3, 'status' => 'completed', 'weeks_out' => -2],
        ],
        'Bain' => [
            ['title' => '6 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 6, 'baseline' => 0, 'current' => 2, 'status' => 'active', 'weeks_out' => 5],
            ['title' => '4 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 4, 'baseline' => 0, 'current' => 2, 'status' => 'active', 'weeks_out' => 4],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'missed', 'weeks_out' => -1],
        ],

        // ── Nicole's partners ──────────────────────────────────────
        'EY' => [
            ['title' => 'Build pipeline to $2M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 2000000, 'baseline' => 800000, 'current' => 1400000, 'status' => 'active', 'weeks_out' => 5],
            ['title' => '10 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 10, 'baseline' => 3, 'current' => 7, 'status' => 'active', 'weeks_out' => 4],
            ['title' => '8 active certifications', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 8, 'baseline' => 4, 'current' => 8, 'status' => 'completed', 'weeks_out' => -1],
        ],
        'TCS' => [
            ['title' => '6 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 6, 'baseline' => 1, 'current' => 4, 'status' => 'active', 'weeks_out' => 3],
            ['title' => 'Register 5 resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 5, 'baseline' => 1, 'current' => 4, 'status' => 'active', 'weeks_out' => 2],
            ['title' => 'Build pipeline to $1M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 1000000, 'baseline' => 200000, 'current' => 600000, 'status' => 'active', 'weeks_out' => 5],
        ],
        'Tech Mahindra' => [
            ['title' => '4 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 4, 'baseline' => 0, 'current' => 2, 'status' => 'active', 'weeks_out' => 4],
            ['title' => 'Register 4 resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 4, 'baseline' => 0, 'current' => 2, 'status' => 'active', 'weeks_out' => 3],
            ['title' => 'Identify 1 IFP opportunity', 'category' => 'pipeline', 'metric_type' => 'manual', 'target' => 1, 'baseline' => 0, 'current' => 1, 'status' => 'completed', 'weeks_out' => -2],
        ],

        // ── Ben's partners ─────────────────────────────────────────
        'Capgemini' => [
            ['title' => '10 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 10, 'baseline' => 3, 'current' => 7, 'status' => 'active', 'weeks_out' => 4],
            ['title' => 'Build pipeline to $2M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 2000000, 'baseline' => 700000, 'current' => 1300000, 'status' => 'active', 'weeks_out' => 5],
            ['title' => '6 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 6, 'baseline' => 2, 'current' => 6, 'status' => 'completed', 'weeks_out' => -1],
        ],
        'Cognizant' => [
            ['title' => '8 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 8, 'baseline' => 1, 'current' => 5, 'status' => 'active', 'weeks_out' => 4],
            ['title' => 'Register 5 delivery resources', 'category' => 'enablement', 'metric_type' => 'resources_registered', 'target' => 5, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 3],
            ['title' => 'Build pipeline to $1M', 'category' => 'pipeline', 'metric_type' => 'pipeline_value', 'target' => 1000000, 'baseline' => 300000, 'current' => 650000, 'status' => 'active', 'weeks_out' => 5],
        ],
        'McKinsey' => [
            ['title' => '6 blueprint completions', 'category' => 'enablement', 'metric_type' => 'blueprint_completions', 'target' => 6, 'baseline' => 1, 'current' => 3, 'status' => 'active', 'weeks_out' => 5],
            ['title' => '4 certifications active', 'category' => 'certification', 'metric_type' => 'certifications', 'target' => 4, 'baseline' => 0, 'current' => 2, 'status' => 'active', 'weeks_out' => 4],
            ['title' => 'Complete marketplace profile', 'category' => 'general', 'metric_type' => 'marketplace_completion', 'target' => 1, 'baseline' => 0, 'current' => 0, 'status' => 'active', 'weeks_out' => 2],
        ],
    ];

    /**
     * Per-PSM weekly summaries for richer narrative (12 weeks, newest first).
     *
     * @var array<string, list<string>>
     */
    private const WEEKLY_SUMMARIES = [
        'gunnar.stoa@gmail.com' => [
            'PRIORITY: Deloitte OWP delivery gap — $800K pipeline closing Q2-Q3, zero blueprint completions. Scheduling sprint with enablement. Also driving IFP completions for PwC.',
            'Escalated Deloitte OWP readiness to enablement team. PwC pipeline near $5M target. BCG resource registration almost done.',
            'Scheduled QBRs with Deloitte and Infosys leadership. Deloitte OWP workshop dates being coordinated with product team.',
            'Deloitte pipeline hit $4.4M — strong overall but OWP delivery readiness still at zero. Working on BCG cert prep sessions.',
            'Infosys team completed all Q1 enablement sessions. PwC SCM opportunity identified. Identified Deloitte OWP gap this week.',
            'Pushed Deloitte marketplace profile through approval. BCG blueprint adoption starting.',
            'PwC blueprint completions accelerating. IBM pipeline review with sales team.',
            'Infosys cert renewals processed. Deloitte delivery bench expansion in progress.',
            'BCG onboarded 2 new delivery resources this week. PwC cert push underway.',
            'Deloitte IFP blueprint sprint — moved from 4 to 7 completions. Strong momentum.',
            'IBM marketplace profile drafted. Infosys pipeline development call with regional lead.',
            'Kicked off Q1 priorities — baseline captured for all partners. Focus on enablement and pipeline.',
        ],
        'rick.palmer@example.com' => [
            'Accenture blueprint completions at 9/12 — strong push this week. KPMG resource registration progressing.',
            'Bain marketplace profile deadline missed — rescheduling. Wipro RPM opportunity exploration.',
            'Accenture pipeline approaching $6M. HCL delivery resources growing.',
            'KPMG blueprint adoptions at 4/6 — need to maintain pace. Accenture certs completed.',
            'Wipro certification target achieved. Bain blueprint adoption slow start.',
            'HCL enablement sessions completed. Accenture marketplace profile approved.',
            'KPMG pipeline development ongoing. Wipro blueprint completions accelerating.',
            'Accenture QBR prep in progress. Bain cert push initiated.',
            'HCL pipeline growing — $720K toward $1M target. Wipro enablement coordination.',
            'Bain just getting started — initial baseline set for all metrics.',
            'KPMG resource registration underway. Accenture pipeline review scheduled.',
            'Q1 kickoff — mapped priorities for Accenture and KPMG. Bain onboarding.',
        ],
        'nicole.demary@example.com' => [
            'EY pipeline at $1.4M — need $600K more. TCS resource registration nearly complete.',
            'Tech Mahindra IFP opportunity identified and closed. EY certifications complete.',
            'TCS blueprint completions at 4/6. Tech Mahindra resource onboarding.',
            'EY blueprint completions progressing well. TCS pipeline development focus.',
            'EY cert push completed — all 8 targets met. Tech Mahindra blueprint kickoff.',
            'TCS resource registration started. EY pipeline review with leadership.',
            'Tech Mahindra enablement planning. EY blueprint workshop scheduled.',
            'TCS enablement sessions underway. EY pipeline at $1.1M.',
            'EY pipeline growing steadily. Tech Mahindra initial assessments.',
            'TCS getting ramped up — baseline captured. EY cert push started.',
            'Tech Mahindra onboarding calls. EY enablement planning.',
            'Q1 kickoff — priorities mapped for EY, TCS, Tech Mahindra.',
        ],
        'ben.wilson@example.com' => [
            'Capgemini pipeline at $1.3M — strong momentum. Cognizant resource registration at 3/5.',
            'McKinsey blueprint adoption slow — scheduling enablement. Capgemini certs completed.',
            'Cognizant pipeline development ongoing. McKinsey marketplace profile drafted.',
            'Capgemini blueprint completions at 7/10. Cognizant enablement coordination.',
            'McKinsey cert push started. Capgemini marketplace profile submitted.',
            'Cognizant blueprint at 5/8 — need push. McKinsey initial assessments.',
            'Capgemini cert target achieved. Cognizant pipeline at $650K.',
            'McKinsey enablement workshops scheduled. Capgemini pipeline review.',
            'Cognizant resource onboarding progressing. McKinsey blueprint kickoff.',
            'Capgemini enablement sprint. Cognizant cert discussions.',
            'McKinsey onboarding. Capgemini pipeline development push.',
            'Q1 kickoff — priorities mapped for Capgemini, Cognizant, McKinsey.',
        ],
    ];

    /**
     * Partner-specific action items grouped by partner name.
     *
     * @var array<string, list<string>>
     */
    private const PARTNER_ACTION_ITEMS = [
        'Deloitte' => [
            'URGENT: Schedule OWP blueprint sprint with Deloitte delivery leads — $800K pipeline at risk',
            'Escalate Deloitte OWP readiness gap to enablement team — 0/14 completions',
            'Schedule QBR with Deloitte practice lead — include OWP delivery risk',
            'Review Deloitte IFP delivery blueprint progress with enablement',
            'Coordinate Deloitte OWP workshop dates with product specialist',
            'Prepare Deloitte pipeline summary for leadership review — highlight OWP gap',
            'Follow up on Deloitte marketplace profile analytics',
            'Send Deloitte team the OWP delivery blueprint requirements and timeline',
        ],
        'PwC' => [
            'Follow up with PwC on marketplace profile approval',
            'Schedule PwC blueprint kickoff with new resources',
            'Review PwC SCM opportunity with sales overlay',
            'Coordinate PwC cert prep sessions',
            'Send PwC team the workshop recording links',
            'Prepare PwC health summary for 1:1 with Bikram',
        ],
        'Infosys' => [
            'Schedule Infosys enablement workshop follow-up',
            'Review Infosys pipeline with regional sales lead',
            'Follow up on Infosys cert renewals coming due',
            'Coordinate Infosys resource registration with partner admin',
            'Prepare Infosys QBR deck with delivery metrics',
            'Send Infosys team the updated blueprint catalog',
        ],
        'IBM' => [
            'Draft IBM marketplace profile with partner marketing',
            'Schedule IBM blueprint kickoff meeting',
            'Review IBM pipeline with ADO product specialist',
            'Coordinate IBM cert push with enablement team',
            'Follow up on IBM resource registration with HR contact',
            'Prepare IBM partner path progress review',
        ],
        'BCG' => [
            'Schedule BCG marketplace profile review call',
            'Coordinate BCG cert prep sessions with training team',
            'Follow up on BCG delivery resource onboarding',
            'Review BCG blueprint progress with enablement lead',
            'Send BCG updated workshop schedule for Q2',
            'Prepare BCG pipeline development strategy',
        ],
        'Accenture' => [
            'Schedule Accenture QBR with exec sponsor',
            'Review Accenture blueprint completion timeline',
            'Follow up on Accenture pipeline opportunities with sales',
            'Coordinate Accenture marketplace analytics review',
        ],
        'KPMG' => [
            'Schedule KPMG enablement workshop',
            'Review KPMG resource registration progress',
            'Follow up on KPMG pipeline development',
        ],
        'Wipro' => [
            'Coordinate Wipro RPM opportunity research',
            'Review Wipro blueprint progress',
            'Schedule Wipro enablement session follow-up',
        ],
        'HCL' => [
            'Follow up on HCL resource registration',
            'Review HCL pipeline with sales overlay',
            'Coordinate HCL delivery team onboarding',
        ],
        'Bain' => [
            'Schedule Bain marketplace profile working session',
            'Review Bain cert prep schedule',
            'Coordinate Bain blueprint kickoff',
        ],
        'EY' => [
            'Schedule EY pipeline review with sales lead',
            'Review EY blueprint completion status',
            'Follow up on EY delivery resource pipeline',
        ],
        'TCS' => [
            'Coordinate TCS resource registration push',
            'Review TCS blueprint progress',
            'Schedule TCS pipeline development call',
        ],
        'Tech Mahindra' => [
            'Follow up on Tech Mahindra resource onboarding',
            'Review Tech Mahindra blueprint adoption plan',
            'Coordinate Tech Mahindra enablement schedule',
        ],
        'Capgemini' => [
            'Schedule Capgemini QBR preparation meeting',
            'Review Capgemini pipeline with sales team',
            'Follow up on Capgemini blueprint completions',
        ],
        'Cognizant' => [
            'Coordinate Cognizant resource registration',
            'Review Cognizant pipeline development strategy',
            'Schedule Cognizant enablement workshop',
        ],
        'McKinsey' => [
            'Draft McKinsey marketplace profile',
            'Coordinate McKinsey cert prep sessions',
            'Review McKinsey enablement plan',
        ],
    ];

    /**
     * Health snapshot data per partner for suggestions engine.
     *
     * @var array<string, array{score: float, risk_level: string, signals: list<string>, dimensions: array}>
     */
    private const HEALTH_SNAPSHOTS = [
        'Deloitte' => ['score' => 38.0, 'risk_level' => 'at_risk', 'signals' => ['CRITICAL: $800K OWP pipeline closing Q2-Q3 with zero delivery blueprint completions', 'OWP delivery readiness gap — 0 of 14 resources certified for upcoming engagements', 'Blueprint adoption strong for IFP but zero completions for OWP and Forecaster'], 'dimensions' => ['scorecard' => 45.0, 'pipeline_trend' => 85.0, 'goal_attainment' => 30.0, 'enablement' => 15.0, 'contact_freshness' => 80.0, 'delivery_csat' => 25.0]],
        'PwC' => ['score' => 72.0, 'risk_level' => 'healthy', 'signals' => ['Marketplace profile pending approval', 'Strong pipeline but SCM gap'], 'dimensions' => ['scorecard' => 75.0, 'pipeline_trend' => 80.0, 'goal_attainment' => 65.0, 'enablement' => 72.0, 'contact_freshness' => 70.0, 'delivery_csat' => 70.0]],
        'Infosys' => ['score' => 55.0, 'risk_level' => 'monitor', 'signals' => ['Low enablement dimension — blueprint completions below threshold', 'Pipeline grew but cert count lagging tier expectations'], 'dimensions' => ['scorecard' => 55.0, 'pipeline_trend' => 60.0, 'goal_attainment' => 50.0, 'enablement' => 40.0, 'contact_freshness' => 65.0, 'delivery_csat' => 60.0]],
        'IBM' => ['score' => 48.0, 'risk_level' => 'at_risk', 'signals' => ['Low enablement dimension — blueprint completions below threshold', 'No marketplace profile submitted', 'Stale deals — 3 opportunities unchanged for 45+ days'], 'dimensions' => ['scorecard' => 50.0, 'pipeline_trend' => 55.0, 'goal_attainment' => 40.0, 'enablement' => 30.0, 'contact_freshness' => 55.0, 'delivery_csat' => 58.0]],
        'BCG' => ['score' => 38.0, 'risk_level' => 'critical', 'signals' => ['Low enablement dimension — blueprint completions below threshold', 'No marketplace profile submitted', 'Pipeline declined significantly over last 90 days', 'Certifications below minimum threshold'], 'dimensions' => ['scorecard' => 35.0, 'pipeline_trend' => 30.0, 'goal_attainment' => 35.0, 'enablement' => 25.0, 'contact_freshness' => 50.0, 'delivery_csat' => 55.0]],
        'Accenture' => ['score' => 82.0, 'risk_level' => 'healthy', 'signals' => ['Top performer — pipeline and enablement both strong'], 'dimensions' => ['scorecard' => 85.0, 'pipeline_trend' => 88.0, 'goal_attainment' => 80.0, 'enablement' => 78.0, 'contact_freshness' => 82.0, 'delivery_csat' => 80.0]],
        'KPMG' => ['score' => 52.0, 'risk_level' => 'monitor', 'signals' => ['Pipeline below target', 'Resource registration behind schedule'], 'dimensions' => ['scorecard' => 50.0, 'pipeline_trend' => 48.0, 'goal_attainment' => 55.0, 'enablement' => 45.0, 'contact_freshness' => 60.0, 'delivery_csat' => 55.0]],
        'Wipro' => ['score' => 60.0, 'risk_level' => 'monitor', 'signals' => ['No RPM pipeline identified', 'Blueprint adoption needs acceleration'], 'dimensions' => ['scorecard' => 60.0, 'pipeline_trend' => 55.0, 'goal_attainment' => 58.0, 'enablement' => 55.0, 'contact_freshness' => 68.0, 'delivery_csat' => 65.0]],
        'HCL' => ['score' => 50.0, 'risk_level' => 'at_risk', 'signals' => ['Pipeline below target', 'Resource registration significantly behind'], 'dimensions' => ['scorecard' => 48.0, 'pipeline_trend' => 45.0, 'goal_attainment' => 50.0, 'enablement' => 42.0, 'contact_freshness' => 58.0, 'delivery_csat' => 55.0]],
        'Bain' => ['score' => 32.0, 'risk_level' => 'critical', 'signals' => ['Marketplace profile deadline missed', 'Low enablement dimension — blueprint completions below threshold', 'Certifications below minimum threshold', 'No resources registered'], 'dimensions' => ['scorecard' => 30.0, 'pipeline_trend' => 25.0, 'goal_attainment' => 30.0, 'enablement' => 20.0, 'contact_freshness' => 45.0, 'delivery_csat' => 42.0]],
        'EY' => ['score' => 65.0, 'risk_level' => 'monitor', 'signals' => ['Pipeline needs push to hit $2M target', 'Blueprint completions on track'], 'dimensions' => ['scorecard' => 65.0, 'pipeline_trend' => 60.0, 'goal_attainment' => 62.0, 'enablement' => 68.0, 'contact_freshness' => 70.0, 'delivery_csat' => 65.0]],
        'TCS' => ['score' => 45.0, 'risk_level' => 'at_risk', 'signals' => ['Pipeline significantly below target', 'Resource registration behind schedule', 'Low enablement dimension — blueprint completions below threshold'], 'dimensions' => ['scorecard' => 42.0, 'pipeline_trend' => 38.0, 'goal_attainment' => 45.0, 'enablement' => 35.0, 'contact_freshness' => 55.0, 'delivery_csat' => 52.0]],
        'Tech Mahindra' => ['score' => 42.0, 'risk_level' => 'at_risk', 'signals' => ['Low enablement dimension — blueprint completions below threshold', 'Resource registration behind schedule'], 'dimensions' => ['scorecard' => 40.0, 'pipeline_trend' => 35.0, 'goal_attainment' => 42.0, 'enablement' => 30.0, 'contact_freshness' => 50.0, 'delivery_csat' => 55.0]],
        'Capgemini' => ['score' => 68.0, 'risk_level' => 'healthy', 'signals' => ['Pipeline growing well', 'Blueprint adoption strong'], 'dimensions' => ['scorecard' => 70.0, 'pipeline_trend' => 72.0, 'goal_attainment' => 65.0, 'enablement' => 68.0, 'contact_freshness' => 65.0, 'delivery_csat' => 68.0]],
        'Cognizant' => ['score' => 50.0, 'risk_level' => 'monitor', 'signals' => ['Resource registration behind', 'Pipeline below target'], 'dimensions' => ['scorecard' => 48.0, 'pipeline_trend' => 45.0, 'goal_attainment' => 50.0, 'enablement' => 48.0, 'contact_freshness' => 55.0, 'delivery_csat' => 58.0]],
        'McKinsey' => ['score' => 35.0, 'risk_level' => 'critical', 'signals' => ['No marketplace profile submitted', 'Low enablement dimension — blueprint completions below threshold', 'Certifications below minimum threshold'], 'dimensions' => ['scorecard' => 32.0, 'pipeline_trend' => 28.0, 'goal_attainment' => 35.0, 'enablement' => 25.0, 'contact_freshness' => 48.0, 'delivery_csat' => 42.0]],
    ];

    private const MANAGER_REVIEW_NOTES = [
        "Good progress on enablement. Let's discuss pipeline targets in our 1:1.",
        'Strong week on certifications. Keep pushing blueprint adoption for the lower-tier partners.',
        'Pipeline development on track. Follow up on marketplace profiles — we need those finalized.',
        "Nice momentum on resource registration. Let's plan the QBR prep for next sprint.",
        'Solid progress across the board. Focus on the at-risk partners next week.',
        'Blueprint completions looking good. Need more attention on cert renewals.',
        'Great work on pipeline growth. Keep the enablement workshops going.',
        'Good recovery on the critical partners. Maintain focus on BCG/Bain enablement.',
    ];

    public function run(): void
    {
        $this->command->info('Seeding Priorities & Progress data...');

        $psms = User::whereHas('role', fn ($q) => $q->where('slug', 'psm'))->get();
        $bikram = User::where('email', 'bikram.sen@example.com')->first();

        if ($psms->isEmpty()) {
            $this->command->warn('No PSMs found. Run GunnarSeeder first.');

            return;
        }

        $this->seedHealthSnapshots();
        $this->command->info('  ✓ Seeded partner health snapshots');

        $this->fixDeloitteOwpScenario();
        $this->command->info('  ✓ Fixed Deloitte OWP delivery gap scenario');

        foreach ($psms as $psm) {
            $partners = $psm->psmPartners;
            if ($partners->isEmpty()) {
                continue;
            }

            $this->seedObjectivesForPsm($psm, $partners);
            $this->seedWeeklyFocusesForPsm($psm, $partners, $bikram);
            $this->command->info("  ✓ Seeded priorities for {$psm->name} ({$partners->count()} partners)");
        }

        $objectiveStats = PsmObjective::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $this->command->info('Priorities seeding complete.');
        $this->command->info("  Objectives: {$objectiveStats->sum()} total — active: {$objectiveStats->get('active', 0)}, completed: {$objectiveStats->get('completed', 0)}, missed: {$objectiveStats->get('missed', 0)}");
        $this->command->info('  Weekly focuses: '.WeeklyFocus::count().' | Action items: '.WeeklyFocusItem::count());
    }

    private function seedHealthSnapshots(): void
    {
        foreach (self::HEALTH_SNAPSHOTS as $partnerName => $data) {
            $partner = Partner::where('name', $partnerName)->first();
            if (! $partner) {
                continue;
            }

            // Create 12 weekly snapshots with gradual score changes
            for ($w = 11; $w >= 0; $w--) {
                $weekDate = now()->subWeeks($w);
                // Score varies slightly each week (±3 points), trending toward current
                $weeklyVariance = ($w > 0) ? rand(-3, 3) : 0;
                $trendOffset = ($w / 12) * rand(5, 15) * ($data['score'] > 50 ? -1 : 1);
                $weekScore = max(10, min(95, $data['score'] + $trendOffset + $weeklyVariance));

                PartnerHealthSnapshot::updateOrCreate(
                    ['partner_id' => $partner->id, 'snapshot_date' => $weekDate->toDateString()],
                    [
                        'score' => round($weekScore, 1),
                        'risk_level' => match (true) {
                            $weekScore >= 70 => 'healthy',
                            $weekScore >= 50 => 'monitor',
                            $weekScore >= 30 => 'at_risk',
                            default => 'critical',
                        },
                        'signals' => $data['signals'],
                        'dimensions' => $data['dimensions'],
                    ]
                );
            }
        }
    }

    /**
     * Fix Deloitte OWP opportunity close dates to Q2/Q3 2026 and ensure
     * zero OWP delivery blueprint completions — creating a realistic
     * pipeline-vs-readiness gap scenario.
     */
    private function fixDeloitteOwpScenario(): void
    {
        $deloitte = Partner::where('name', 'Deloitte')->first();
        $owp = Product::where('slug', 'operational-workforce-planning')->first();

        if (! $deloitte || ! $owp) {
            return;
        }

        // Update OWP opportunities to have close dates in Q2/Q3 2026
        $owpOpps = DB::table('opportunities')
            ->where('partner_id', $deloitte->id)
            ->where('product_id', $owp->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->get();

        $closeDates = ['2026-05-15', '2026-06-30', '2026-08-15'];

        foreach ($owpOpps as $i => $opp) {
            DB::table('opportunities')->where('id', $opp->id)->update([
                'close_date' => $closeDates[$i] ?? '2026-07-30',
                'fiscal_quarter' => $i < 2 ? 'Q2' : 'Q3',
                'fiscal_year' => 2026,
            ]);
        }

        // Ensure all OWP blueprint enrollments for Deloitte are NOT completed
        $owpBlueprints = DB::table('blueprints')
            ->where('name', 'like', '%Operational Workforce Planning%')
            ->where('status', 'published')
            ->pluck('id');

        if ($owpBlueprints->isNotEmpty()) {
            $assignmentIds = DB::table('partner_blueprint_assignments')
                ->where('partner_id', $deloitte->id)
                ->whereIn('blueprint_id', $owpBlueprints)
                ->pluck('id');

            if ($assignmentIds->isNotEmpty()) {
                DB::table('blueprint_enrollments')
                    ->whereIn('partner_blueprint_assignment_id', $assignmentIds)
                    ->update(['status' => 'in_progress', 'completed_at' => null]);
            }
        }
    }

    private function seedObjectivesForPsm(User $psm, $partners): void
    {
        foreach ($partners as $partner) {
            $objectives = self::PARTNER_OBJECTIVES[$partner->name] ?? [];

            foreach ($objectives as $obj) {
                $targetDate = in_array($obj['status'], ['completed', 'missed'])
                    ? now()->subWeeks(abs($obj['weeks_out']))
                    : now()->addWeeks($obj['weeks_out']);

                $completedAt = null;
                if ($obj['status'] === 'completed') {
                    $completedAt = $targetDate->copy()->subDays(rand(1, 7));
                }

                PsmObjective::updateOrCreate(
                    ['user_id' => $psm->id, 'partner_id' => $partner->id, 'title' => $obj['title']],
                    [
                        'description' => $obj['description'] ?? null,
                        'category' => $obj['category'],
                        'metric_type' => $obj['metric_type'],
                        'metric_config' => null,
                        'target_value' => $obj['target'],
                        'baseline_value' => $obj['baseline'],
                        'current_value' => $obj['current'],
                        'target_date' => $targetDate,
                        'status' => $obj['status'],
                        'completed_at' => $completedAt,
                    ]
                );
            }
        }
    }

    private function seedWeeklyFocusesForPsm(User $psm, $partners, ?User $bikram): void
    {
        $summaries = self::WEEKLY_SUMMARIES[$psm->email] ?? [];
        $currentWeek = WeeklyFocus::currentWeekStart();

        for ($w = 0; $w < 12; $w++) {
            $weekStart = $currentWeek->copy()->subWeeks($w);
            $isCurrentWeek = $w === 0;
            $isPast = $w > 0;

            $focus = WeeklyFocus::updateOrCreate(
                ['user_id' => $psm->id, 'week_start' => $weekStart->toDateString()],
                [
                    'summary' => $summaries[$w] ?? ($isCurrentWeek
                        ? 'Focused on partner enablement and pipeline development this week.'
                        : 'Worked on objectives and partner engagement.'),
                ]
            );

            // Seed 4-6 action items per week, tied to specific partners
            $itemCount = rand(4, 6);
            $partnerItems = [];

            foreach ($partners as $partner) {
                $items = self::PARTNER_ACTION_ITEMS[$partner->name] ?? [];
                foreach ($items as $item) {
                    $partnerItems[] = ['partner' => $partner, 'description' => $item];
                }
            }

            $shuffled = collect($partnerItems)->shuffle()->take($itemCount);

            foreach ($shuffled->values() as $idx => $itemData) {
                if ($isPast) {
                    // Older weeks: more items completed; recent weeks: more in-progress
                    $completionRate = min(90, 50 + ($w * 5));
                    $rand = rand(1, 100);
                    $status = match (true) {
                        $rand <= $completionRate => 'completed',
                        $rand <= $completionRate + 10 => 'dropped',
                        default => 'planned',
                    };
                } else {
                    // Current week: mix of planned, in-progress, and some completed
                    $status = collect(['planned', 'planned', 'in_progress', 'in_progress', 'completed'])->random();
                }

                WeeklyFocusItem::updateOrCreate(
                    ['weekly_focus_id' => $focus->id, 'description' => $itemData['description']],
                    [
                        'partner_id' => $itemData['partner']->id,
                        'status' => $status,
                        'completed_at' => $status === 'completed' ? $weekStart->copy()->addDays(rand(0, 4)) : null,
                        'sort_order' => $idx,
                    ]
                );
            }

            // Manager reviews on weeks 2+ (every other week reviewed)
            if ($isPast && $w >= 2 && $w % 2 === 0 && $bikram) {
                $focus->update([
                    'reviewed_at' => $weekStart->copy()->addDays(5),
                    'reviewed_by' => $bikram->id,
                    'manager_notes' => self::MANAGER_REVIEW_NOTES[array_rand(self::MANAGER_REVIEW_NOTES)],
                ]);
            }
        }
    }
}
