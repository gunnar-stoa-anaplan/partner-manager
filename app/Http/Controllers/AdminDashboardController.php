<?php

namespace App\Http\Controllers;

use App\Helpers\FiscalYearHelper;
use App\Models\Blueprint;
use App\Models\BlueprintEnrollment;
use App\Models\Contact;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\PartnerGoal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
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
        $totalImports = Import::count();

        // ── Section 2: Performance ──

        // Pipeline by fiscal quarter
        $pipelineByQuarter = Opportunity::select('fiscal_quarter', DB::raw('SUM(amount) as total_amount'))
            ->whereNotNull('fiscal_quarter')
            ->groupBy('fiscal_quarter')
            ->orderBy('fiscal_quarter')
            ->pluck('total_amount', 'fiscal_quarter');

        // Pipeline by stage
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

        // Project status distribution
        $projectStatusDist = Project::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Avg project completion
        $avgProjectCompletion = round(Project::avg('percent_complete') ?? 0);

        // Goals vs Actual
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
        ])->filter(fn($item) => $item['count'] > 0)->values();

        // ── Section 5: Import History ──

        $recentImports = Import::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $importStats = Import::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $importTimeline = Import::select(
                DB::raw('YEARWEEK(created_at, 1) as week'),
                DB::raw('MIN(DATE(created_at)) as week_start'),
                DB::raw('SUM(successful_rows) as successful_rows'),
                DB::raw('SUM(failed_rows) as failed_rows')
            )
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy(DB::raw('YEARWEEK(created_at, 1)'))
            ->orderBy('week')
            ->get();

        // ── Section 6: User Access ──

        $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        $allUsers = User::withCount('partners')
            ->orderBy('name')
            ->get();

        $recentUsers = User::where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

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
            ->mapWithKeys(fn($row) => [$row->geography => (float) $row->total_pipeline]);

        // ── Section 9: Database Health ──

        $dbName = config('database.connections.mysql.database', 'laravel');

        $tableStats = collect(DB::select("
            SELECT TABLE_NAME as table_name,
                   TABLE_ROWS as row_count,
                   ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb,
                   ROUND(DATA_LENGTH / 1024 / 1024, 2) as data_mb,
                   ROUND(INDEX_LENGTH / 1024 / 1024, 2) as index_mb
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
              AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
        ", [$dbName]));

        $dbTotalSizeMb = round($tableStats->sum('size_mb'), 2);
        $dbTotalRows = $tableStats->sum('row_count');

        $mysqlStatus = collect(DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
            'Uptime', 'Threads_connected', 'Threads_running',
            'Questions', 'Slow_queries', 'Connections',
            'Innodb_buffer_pool_read_requests', 'Innodb_buffer_pool_reads'
        )"))->pluck('Value', 'Variable_name');

        $mysqlUptime = (int) ($mysqlStatus['Uptime'] ?? 0);
        $mysqlUptimeDays = round($mysqlUptime / 86400, 1);
        $threadsConnected = (int) ($mysqlStatus['Threads_connected'] ?? 0);
        $threadsRunning = (int) ($mysqlStatus['Threads_running'] ?? 0);
        $totalQueries = (int) ($mysqlStatus['Questions'] ?? 0);
        $slowQueries = (int) ($mysqlStatus['Slow_queries'] ?? 0);
        $totalConnections = (int) ($mysqlStatus['Connections'] ?? 0);

        $bufferReqs = (int) ($mysqlStatus['Innodb_buffer_pool_read_requests'] ?? 0);
        $bufferReads = (int) ($mysqlStatus['Innodb_buffer_pool_reads'] ?? 0);
        $bufferHitRate = $bufferReqs > 0 ? round((1 - $bufferReads / $bufferReqs) * 100, 2) : 0;

        $mysqlVariables = collect(DB::select("SHOW GLOBAL VARIABLES WHERE Variable_name IN (
            'innodb_buffer_pool_size', 'max_connections', 'long_query_time'
        )"))->pluck('Value', 'Variable_name');

        $bufferPoolSizeMb = round((int) ($mysqlVariables['innodb_buffer_pool_size'] ?? 0) / 1024 / 1024);
        $maxConnections = (int) ($mysqlVariables['max_connections'] ?? 0);
        $longQueryTime = (float) ($mysqlVariables['long_query_time'] ?? 10);

        $phpMemoryUsage = round(memory_get_usage(true) / 1024 / 1024, 1);
        $phpMemoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
        $phpMemoryLimit = ini_get('memory_limit');

        return view('admin.dashboard', compact(
            'currentFY',
            'totalPartners', 'totalUsers', 'totalContacts', 'totalProjects',
            'totalOpportunities', 'totalPipelineValue', 'totalLeads', 'totalImports',
            'pipelineByQuarter', 'pipelineByStage', 'projectStatusDist',
            'avgProjectCompletion', 'goalsVsActual',
            'psmUtilization', 'unassignedPartners', 'pipelinePerPsm',
            'partnerFieldCompleteness', 'dataIssues',
            'recentImports', 'importStats', 'importTimeline',
            'usersByRole', 'allUsers', 'recentUsers',
            'enablementEventsByStatus', 'upcomingSessions', 'registrationsByStatus',
            'blueprintAssignmentStats', 'totalEnrollments', 'completedEnrollments',
            'partnersByGeo', 'partnersByIndustry', 'pipelineByGeo',
            'tableStats', 'dbTotalSizeMb', 'dbTotalRows', 'dbName',
            'mysqlUptimeDays', 'threadsConnected', 'threadsRunning',
            'totalQueries', 'slowQueries', 'totalConnections',
            'bufferHitRate', 'bufferPoolSizeMb', 'maxConnections', 'longQueryTime',
            'phpMemoryUsage', 'phpMemoryPeak', 'phpMemoryLimit'
        ));
    }
}
