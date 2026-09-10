<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeasureController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'at-a-glance');
        $user = auth()->user();
        $fy = $this->currentFiscalYear();
        $fyInt = $this->fyToInt($fy); // opportunities.fiscal_year is an integer column

        $partnerIds = $user->isAdmin()
            ? Partner::pluck('id')
            : Partner::where('user_id', $user->id)->pluck('id');

        $data = match ($tab) {
            'at-a-glance' => $this->atAGlance($partnerIds, $fy),
            'pipeline' => $this->pipeline($partnerIds, $fy),
            'leads' => $this->leads($partnerIds, $fy),
            'funding' => $this->funding($partnerIds, $fy),
            'revenue' => $this->revenue($partnerIds, $fy),
            'enablement' => $this->enablement($partnerIds, $fy),
            'delivery' => $this->delivery($partnerIds, $fy),
            default => $this->atAGlance($partnerIds, $fy),
        };

        $data['tab'] = $tab;
        $data['fy'] = $fy;

        return view('measure.index', $data);
    }

    protected function atAGlance($partnerIds, $fy): array
    {
        $fyInt = $this->fyToInt($fy);
        $avgScore = DB::table('partner_scorecards')
            ->whereIn('partner_id', $partnerIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('partner_scorecards')->groupBy('partner_id');
            })
            ->avg('overall_score') ?? 0;

        $totalPipeline = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');

        $won12m = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('close_date', '>=', now()->subMonths(12))->count();
        $lost12m = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Lost')->where('close_date', '>=', now()->subMonths(12))->count();
        $winRate = ($won12m + $lost12m) > 0 ? round(($won12m / ($won12m + $lost12m)) * 100, 1) : 0;

        $sourcedRevenue = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->where('revenue_type', 'sourced')->sum('amount');

        $influencedRevenue = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->where('revenue_type', 'influenced')->sum('amount');

        $activeProjects = DB::table('projects')->whereIn('partner_id', $partnerIds)
            ->where('status', 'in_progress')->count();

        $activeCerts = DB::table('certifications')->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')->count();

        $overdueTasks = DB::table('partner_tasks')->whereIn('partner_id', $partnerIds)
            ->whereIn('status', ['not_started', 'in_progress'])
            ->where('requirement_level', 'required')
            ->whereNotNull('due_date')->where('due_date', '<', now())->count();

        // Revenue by quarter
        $revenueByQuarter = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->selectRaw('fiscal_quarter, revenue_type, SUM(amount) as total')
            ->groupBy('fiscal_quarter', 'revenue_type')
            ->get();

        // Path stage distribution
        $stageDistribution = DB::table('partner_path_enrollments as e')
            ->join('partner_path_stages as s', 'e.current_stage_id', '=', 's.id')
            ->whereIn('e.partner_id', $partnerIds)
            ->where('e.status', 'active')
            ->selectRaw('s.name, s.color, COUNT(*) as count')
            ->groupBy('s.name', 's.color', 's.stage_number')
            ->orderBy('s.stage_number')
            ->get();

        return compact('avgScore', 'totalPipeline', 'winRate', 'sourcedRevenue', 'influencedRevenue',
            'activeProjects', 'activeCerts', 'overdueTasks', 'revenueByQuarter', 'stageDistribution');
    }

    protected function pipeline($partnerIds, $fy): array
    {
        $pipelineValue = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');
        $avgDeal = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->avg('amount') ?? 0;
        $staleDeals = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('updated_at', '<', now()->subDays(30))->count();
        $productAreas = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->whereNotNull('product_area')->distinct()->count('product_area');

        $byStage = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('stage, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('stage')->get();

        $byOrigin = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw("COALESCE(origin, 'unknown') as origin, fiscal_quarter, SUM(amount) as total")
            ->groupBy('origin', 'fiscal_quarter')->get();

        $staleList = DB::table('opportunities as o')
            ->join('partners as p', 'o.partner_id', '=', 'p.id')
            ->whereIn('o.partner_id', $partnerIds)
            ->whereNotIn('o.stage', ['Closed Won', 'Closed Lost'])
            ->where('o.updated_at', '<', now()->subDays(30))
            ->select('o.*', 'p.name as partner_name')
            ->orderBy('o.updated_at')
            ->limit(20)->get();

        return compact('pipelineValue', 'avgDeal', 'staleDeals', 'productAreas', 'byStage', 'byOrigin', 'staleList');
    }

    protected function leads($partnerIds, $fy): array
    {
        $totalLeads = DB::table('leads')->whereIn('partner_id', $partnerIds)->count();
        $byStatus = DB::table('leads')->whereIn('partner_id', $partnerIds)
            ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $byPartner = DB::table('leads as l')
            ->join('partners as p', 'l.partner_id', '=', 'p.id')
            ->whereIn('l.partner_id', $partnerIds)
            ->selectRaw('p.name, l.status, COUNT(*) as count')
            ->groupBy('p.name', 'l.status')->get();

        return compact('totalLeads', 'byStatus', 'byPartner');
    }

    protected function funding($partnerIds, $fy): array
    {
        $totalFunding = DB::table('fundings')->whereIn('partner_id', $partnerIds)->sum('amount');
        $byType = DB::table('fundings')->whereIn('partner_id', $partnerIds)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')->groupBy('type')->get();
        $byPartner = DB::table('fundings as f')
            ->join('partners as p', 'f.partner_id', '=', 'p.id')
            ->whereIn('f.partner_id', $partnerIds)
            ->selectRaw('p.name, SUM(f.amount) as total')->groupBy('p.name')->orderByDesc('total')->get();

        return compact('totalFunding', 'byType', 'byPartner');
    }

    protected function revenue($partnerIds, $fy): array
    {
        $fyInt = $this->fyToInt($fy);
        $totalBookings = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)->sum('amount');
        $sourcedNnacv = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->where('revenue_type', 'sourced')->sum('amount');
        $influencedNnacv = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->where('revenue_type', 'influenced')->sum('amount');
        $avgAcv = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)->avg('amount') ?? 0;

        $byQuarter = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->selectRaw('fiscal_quarter, revenue_type, SUM(amount) as total')
            ->groupBy('fiscal_quarter', 'revenue_type')->get();

        $byProductArea = DB::table('opportunities')->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')->where('fiscal_year', $fyInt)
            ->whereNotNull('product_area')
            ->selectRaw('product_area, SUM(amount) as total')
            ->groupBy('product_area')->orderByDesc('total')->get();

        $byPartner = DB::table('opportunities as o')
            ->join('partners as p', 'o.partner_id', '=', 'p.id')
            ->whereIn('o.partner_id', $partnerIds)
            ->where('o.stage', 'Closed Won')->where('o.fiscal_year', $fyInt)
            ->selectRaw('p.name, SUM(o.amount) as total')
            ->groupBy('p.name')->orderByDesc('total')->get();

        return compact('totalBookings', 'sourcedNnacv', 'influencedNnacv', 'avgAcv', 'byQuarter', 'byProductArea', 'byPartner');
    }

    protected function enablement($partnerIds, $fy): array
    {
        $activeCerts = DB::table('certifications')->whereIn('partner_id', $partnerIds)->where('status', 'active')->count();
        $expiring90 = DB::table('certifications')->whereIn('partner_id', $partnerIds)->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])->count();
        $registered = DB::table('enablement_registrations')->whereIn('partner_id', $partnerIds)->count();
        $attended = DB::table('enablement_registrations')->whereIn('partner_id', $partnerIds)->where('status', 'attended')->count();
        $attendanceRate = $registered > 0 ? round(($attended / $registered) * 100, 1) : 0;

        $totalBp = DB::table('partner_blueprint_assignments')->whereIn('partner_id', $partnerIds)->count();
        $completedBp = DB::table('partner_blueprint_assignments')->whereIn('partner_id', $partnerIds)->where('status', 'completed')->count();
        $bpCompletionRate = $totalBp > 0 ? round(($completedBp / $totalBp) * 100, 1) : 0;

        $certsByType = DB::table('certifications')->whereIn('partner_id', $partnerIds)->where('status', 'active')
            ->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type');

        $bpByPartner = DB::table('partner_blueprint_assignments as pba')
            ->join('partners as p', 'pba.partner_id', '=', 'p.id')
            ->whereIn('pba.partner_id', $partnerIds)
            ->selectRaw('p.name, pba.status, COUNT(*) as count')
            ->groupBy('p.name', 'pba.status')->get();

        return compact('activeCerts', 'expiring90', 'attendanceRate', 'bpCompletionRate', 'certsByType', 'bpByPartner');
    }

    protected function delivery($partnerIds, $fy): array
    {
        $activeProjects = DB::table('projects')->whereIn('partner_id', $partnerIds)->where('status', 'in_progress')->count();
        $avgCsat = DB::table('projects')->whereIn('partner_id', $partnerIds)
            ->where('status', 'completed')->whereNotNull('customer_satisfaction')->avg('customer_satisfaction') ?? 0;
        $completed = DB::table('projects')->whereIn('partner_id', $partnerIds)->where('status', 'completed')->count();
        $onTime = DB::table('projects')->whereIn('partner_id', $partnerIds)->where('status', 'completed')
            ->where('percent_complete', '>=', 100)->count();
        $onTimeRate = $completed > 0 ? round(($onTime / $completed) * 100, 1) : 0;
        $referenceable = DB::table('projects')->whereIn('partner_id', $partnerIds)->where('status', 'completed')
            ->where('is_referenceable', true)->count();
        $refRate = $completed > 0 ? round(($referenceable / $completed) * 100, 1) : 0;

        $openCases = DB::table('cases')->whereIn('partner_id', $partnerIds)->whereIn('status', ['open', 'in_progress', 'escalated'])->count();
        $escalatedCount = DB::table('cases')->whereIn('partner_id', $partnerIds)->where('escalated', true)->count();
        $totalCases = DB::table('cases')->whereIn('partner_id', $partnerIds)->count();
        $escalationRate = $totalCases > 0 ? round(($escalatedCount / $totalCases) * 100, 1) : 0;

        $projectsByStatus = DB::table('projects')->whereIn('partner_id', $partnerIds)
            ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');

        $casesByPartner = DB::table('cases as c')
            ->join('partners as p', 'c.partner_id', '=', 'p.id')
            ->whereIn('c.partner_id', $partnerIds)
            ->selectRaw('p.name, c.severity, COUNT(*) as count')
            ->groupBy('p.name', 'c.severity')->get();

        return compact('activeProjects', 'avgCsat', 'onTimeRate', 'refRate', 'openCases', 'escalationRate',
            'projectsByStatus', 'casesByPartner');
    }

    protected function currentFiscalYear(): string
    {
        $fyStart = (int) AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;

        return 'FY'.substr($year + 1, 2);
    }

    /** "FY27" → 2027, for tables whose fiscal_year column is an integer. */
    protected function fyToInt(string $fy): int
    {
        $n = (int) preg_replace('/\D/', '', $fy);

        return $n < 100 ? 2000 + $n : $n;
    }
}
