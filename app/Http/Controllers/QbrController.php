<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\QbrSession;
use App\Models\QbrSection;
use App\Models\ActionPlan;
use App\Services\ScorecardCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QbrController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = QbrSession::with(['partner', 'preparedByUser']);

        if ($user->role !== 'admin') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        $sessions = $query->orderByDesc('scheduled_date')->get();

        $partners = $user->role === 'admin'
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        return view('act.qbr.index', compact('sessions', 'partners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'fiscal_quarter' => 'required|string',
        ]);

        $partner = Partner::findOrFail($request->partner_id);

        $session = QbrSession::create([
            'partner_id' => $partner->id,
            'fiscal_quarter' => $request->fiscal_quarter,
            'status' => 'planning',
            'scheduled_date' => $request->scheduled_date,
            'prepared_by' => auth()->id(),
        ]);

        // Auto-generate sections
        $sectionTypes = ['scorecard_review', 'pipeline_review', 'delivery_review', 'enablement_review',
            'goals_review', 'path_review', 'task_review', 'action_review', 'open_discussion'];

        foreach ($sectionTypes as $i => $type) {
            QbrSection::create([
                'qbr_session_id' => $session->id,
                'section_type' => $type,
                'sort_order' => $i,
                'auto_generated_data' => $this->generateSectionData($partner, $type),
            ]);
        }

        return redirect()->route('act.qbr.show', $session)->with('success', 'QBR session created with auto-generated data.');
    }

    public function show(QbrSession $session)
    {
        $session->load(['partner', 'sections', 'preparedByUser']);
        return view('act.qbr.show', compact('session'));
    }

    public function updateSection(Request $request, QbrSection $section)
    {
        $section->update([
            'psm_notes' => $request->psm_notes,
            'partner_feedback' => $request->partner_feedback,
            'rating' => $request->rating,
        ]);
        return redirect()->back()->with('success', 'Section updated.');
    }

    public function complete(Request $request, QbrSession $session)
    {
        $session->update([
            'status' => 'completed',
            'completed_date' => now(),
            'overall_rating' => $request->overall_rating,
            'executive_summary' => $request->executive_summary,
        ]);
        return redirect()->route('act.qbr.index')->with('success', 'QBR completed.');
    }

    protected function generateSectionData(Partner $partner, string $type): array
    {
        return match ($type) {
            'scorecard_review' => [
                'latest_score' => DB::table('partner_scorecards')->where('partner_id', $partner->id)->orderByDesc('scored_at')->first(),
            ],
            'pipeline_review' => [
                'open_pipeline' => DB::table('opportunities')->where('partner_id', $partner->id)
                    ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount'),
                'opp_count' => DB::table('opportunities')->where('partner_id', $partner->id)
                    ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->count(),
                'won_count' => DB::table('opportunities')->where('partner_id', $partner->id)
                    ->where('stage', 'Closed Won')->where('close_date', '>=', now()->subMonths(3))->count(),
            ],
            'delivery_review' => [
                'active_projects' => DB::table('projects')->where('partner_id', $partner->id)->where('status', 'in_progress')->count(),
                'completed_projects' => DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->count(),
                'avg_csat' => DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->avg('customer_satisfaction'),
            ],
            'enablement_review' => [
                'active_certs' => DB::table('certifications')->where('partner_id', $partner->id)->where('status', 'active')->count(),
                'bp_assignments' => DB::table('partner_blueprint_assignments')->where('partner_id', $partner->id)->count(),
                'bp_completed' => DB::table('partner_blueprint_assignments')->where('partner_id', $partner->id)->where('status', 'completed')->count(),
            ],
            'task_review' => [
                'total_tasks' => DB::table('partner_tasks')->where('partner_id', $partner->id)->count(),
                'completed' => DB::table('partner_tasks')->where('partner_id', $partner->id)->where('status', 'completed')->count(),
                'overdue' => DB::table('partner_tasks')->where('partner_id', $partner->id)
                    ->whereIn('status', ['not_started', 'in_progress'])->where('due_date', '<', now())->count(),
            ],
            default => [],
        };
    }
}
