<?php

namespace App\Http\Controllers\Strategy;

use App\Helpers\FiscalYearHelper;
use App\Http\Controllers\Controller;
use App\Models\BlueprintEnrollment;
use App\Models\Contact;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\PartnerGoal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CoeProgramDashboardController extends Controller
{
    public function __invoke(): View
    {
        $fiscalPeriod = FiscalYearHelper::getCurrentFiscalPeriod();
        $currentFY = $fiscalPeriod['fiscal_year'];

        // ── Section 1: Summary Cards ──
        $totalPartners = Partner::count();
        $totalUsers = User::count();
        $totalContacts = Contact::count();
        $totalProjects = Project::count();
        $totalOpportunities = Opportunity::count();
        $totalPipelineValue = Opportunity::sum('amount');
        $totalLeads = Lead::count();

        // ── Section 2: Performance ──
        $pipelineByQuarter = Opportunity::select('fiscal_quarter', DB::raw('SUM(amount) as total_amount'))
            ->whereNotNull('fiscal_quarter')
            ->groupBy('fiscal_quarter')
            ->orderBy('fiscal_quarter')
            ->pluck('total_amount', 'fiscal_quarter');

        $stageOrder = [
            'Prospecting', 'Qualification', 'Needs Analysis',
            'Value Proposition', 'Proposal/Price Quote',
            'Negotiation/Review', 'Closed Won', 'Closed Lost',
        ];
        $pipelineByStage = Opportunity::select('stage', DB::raw('SUM(amount) as total_amount'))
            ->whereIn('stage', $stageOrder)
            ->groupBy('stage')
            ->pluck('total_amount', 'stage')
            ->sortBy(function ($val, $key) use ($stageOrder) {
                return array_search($key, $stageOrder);
            });

        $projectStatusDist = Project::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $avgProjectCompletion = round(Project::avg('percent_complete') ?? 0);

        $goalsData = PartnerGoal::where('fiscal_year', $currentFY)
            ->selectRaw('SUM(sourced_nnacv_target) as sourced_target, SUM(influenced_nnacv_target) as influenced_target')
            ->first();
        $sourcedActual = Opportunity::where('fiscal_year', $currentFY)
            ->where('revenue_type', 'sourced')
            ->sum('amount');
        $influencedActual = Opportunity::where('fiscal_year', $currentFY)
            ->where('revenue_type', 'influenced')
            ->sum('amount');
        $goalsVsActual = [
            'sourced_target' => (float) ($goalsData->sourced_target ?? 0),
            'sourced_actual' => (float) $sourcedActual,
            'influenced_target' => (float) ($goalsData->influenced_target ?? 0),
            'influenced_actual' => (float) $influencedActual,
        ];

        // ── Section 3: PSM Utilization ──
        $psmUtilization = User::where('role', 'psm')
            ->withCount('partners')
            ->orderByDesc('partners_count')
            ->get();

        $unassignedPartners = Partner::whereNull('user_id')->count();

        $pipelinePerPsm = DB::table('partners')
            ->join('users', 'partners.user_id', '=', 'users.id')
            ->join('opportunities', 'opportunities.partner_id', '=', 'partners.id')
            ->where('users.role', 'psm')
            ->select('users.name', DB::raw('SUM(opportunities.amount) as total_pipeline'), DB::raw('COUNT(opportunities.id) as opp_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_pipeline')
            ->get();

        // ── Section 4: Data Health ──
        $partnersNoIndustry = Partner::whereNull('industry')->orWhere('industry', '')->count();
        $partnersNoGeo = Partner::whereNull('geography')->orWhere('geography', '')->count();
        $partnersNoSfId = Partner::whereNull('salesforce_id')->orWhere('salesforce_id', '')->count();
        $partnersNoContacts = Partner::doesntHave('contacts')->count();
        $partnersNoOpps = Partner::doesntHave('opportunities')->count();
        $contactsNoEmail = Contact::whereNull('email')->orWhere('email', '')->count();
        $contactsNoTitle = Contact::whereNull('title')->orWhere('title', '')->count();
        $oppsNoAmount = Opportunity::whereNull('amount')->orWhere('amount', 0)->count();
        $oppsNoStage = Opportunity::whereNull('stage')->orWhere('stage', '')->count();
        $oppsNoCloseDate = Opportunity::whereNull('close_date')->count();
        $stalePartners = Partner::where('updated_at', '<', now()->subDays(30))->count();

        $partnerFieldCompleteness = [
            'Industry' => $totalPartners > 0 ? round((($totalPartners - $partnersNoIndustry) / $totalPartners) * 100) : 0,
            'Geography' => $totalPartners > 0 ? round((($totalPartners - $partnersNoGeo) / $totalPartners) * 100) : 0,
            'Salesforce ID' => $totalPartners > 0 ? round((($totalPartners - $partnersNoSfId) / $totalPartners) * 100) : 0,
            'Has Contacts' => $totalPartners > 0 ? round((($totalPartners - $partnersNoContacts) / $totalPartners) * 100) : 0,
            'Has Opportunities' => $totalPartners > 0 ? round((($totalPartners - $partnersNoOpps) / $totalPartners) * 100) : 0,
        ];

        $dataIssues = collect([
            ['issue' => 'Partners without Contacts', 'count' => $partnersNoContacts, 'severity' => 'danger'],
            ['issue' => 'Opportunities without Amount', 'count' => $oppsNoAmount, 'severity' => 'danger'],
            ['issue' => 'Opportunities without Stage', 'count' => $oppsNoStage, 'severity' => 'danger'],
            ['issue' => 'Partners without Industry', 'count' => $partnersNoIndustry, 'severity' => 'warning'],
            ['issue' => 'Partners without Geography', 'count' => $partnersNoGeo, 'severity' => 'warning'],
            ['issue' => 'Contacts without Email', 'count' => $contactsNoEmail, 'severity' => 'warning'],
            ['issue' => 'Opportunities without Close Date', 'count' => $oppsNoCloseDate, 'severity' => 'warning'],
            ['issue' => 'Partners without Salesforce ID', 'count' => $partnersNoSfId, 'severity' => 'info'],
            ['issue' => 'Contacts without Title', 'count' => $contactsNoTitle, 'severity' => 'info'],
            ['issue' => 'Partners without Opportunities', 'count' => $partnersNoOpps, 'severity' => 'info'],
            ['issue' => 'Stale Partners (30+ days)', 'count' => $stalePartners, 'severity' => 'info'],
        ])->filter(fn ($item) => $item['count'] > 0)->values();

        // ── Section 7: Enablement ──
        $enablementEventsByStatus = EnablementEvent::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $upcomingSessions = EnablementSession::with('event')
            ->where('start_at', '>', now())
            ->where('start_at', '<=', now()->addDays(30))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        $registrationsByStatus = EnablementRegistration::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $blueprintAssignmentStats = [
            'total' => PartnerBlueprintAssignment::count(),
            'active' => PartnerBlueprintAssignment::where('status', 'active')->count(),
            'completed' => PartnerBlueprintAssignment::where('status', 'completed')->count(),
            'paused' => PartnerBlueprintAssignment::where('status', 'paused')->count(),
        ];

        $totalEnrollments = BlueprintEnrollment::count();
        $completedEnrollments = BlueprintEnrollment::where('status', 'completed')->count();

        // ── Section 8: Distribution ──
        $partnersByGeo = Partner::select('geography', DB::raw('COUNT(*) as count'))
            ->whereNotNull('geography')
            ->where('geography', '!=', '')
            ->groupBy('geography')
            ->orderByDesc('count')
            ->pluck('count', 'geography');

        $partnersByIndustry = Partner::select('industry', DB::raw('COUNT(*) as count'))
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->groupBy('industry')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'industry');

        $pipelineByGeo = DB::table('partners')
            ->join('opportunities', 'opportunities.partner_id', '=', 'partners.id')
            ->whereNotNull('partners.geography')
            ->where('partners.geography', '!=', '')
            ->select('partners.geography', DB::raw('SUM(opportunities.amount) as total_pipeline'))
            ->groupBy('partners.geography')
            ->orderByDesc('total_pipeline')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->geography => (float) $row->total_pipeline]);

        return view('strategy.coe-program-dashboard', compact(
            'currentFY',
            'totalPartners', 'totalUsers', 'totalContacts', 'totalProjects',
            'totalOpportunities', 'totalPipelineValue', 'totalLeads',
            'pipelineByQuarter', 'pipelineByStage', 'projectStatusDist',
            'avgProjectCompletion', 'goalsVsActual',
            'psmUtilization', 'unassignedPartners', 'pipelinePerPsm',
            'partnerFieldCompleteness', 'dataIssues',
            'enablementEventsByStatus', 'upcomingSessions', 'registrationsByStatus',
            'blueprintAssignmentStats', 'totalEnrollments', 'completedEnrollments',
            'partnersByGeo', 'partnersByIndustry', 'pipelineByGeo',
        ));
    }
}
