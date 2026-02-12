<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerScorecard;
use App\Models\ScorecardWeight;
use App\Services\ScorecardCalculationService;
use Illuminate\Http\Request;

class ScorecardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $partnersQuery = $user->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name', 'geography', 'industry')->get();

        // Get latest scorecard for each partner
        $scorecards = PartnerScorecard::whereIn('partner_id', $partners->pluck('id'))
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('partner_scorecards')->groupBy('partner_id');
            })
            ->get()
            ->keyBy('partner_id');

        $weights = ScorecardWeight::where('is_active', true)->pluck('weight', 'dimension');

        // Portfolio averages
        $avgScores = [
            'overall' => $scorecards->avg('overall_score') ?? 0,
            'certification' => $scorecards->avg('certification_score') ?? 0,
            'pipeline' => $scorecards->avg('pipeline_score') ?? 0,
            'delivery' => $scorecards->avg('delivery_score') ?? 0,
            'engagement' => $scorecards->avg('engagement_score') ?? 0,
            'growth' => $scorecards->avg('growth_score') ?? 0,
            'self_sufficiency' => $scorecards->avg('self_sufficiency_score') ?? 0,
        ];

        return view('plan.scorecard.index', compact('partners', 'scorecards', 'weights', 'avgScores'));
    }

    public function show(Partner $partner)
    {
        $scorecards = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->limit(12)
            ->get();

        $latest = $scorecards->first();

        return view('plan.scorecard.show', compact('partner', 'scorecards', 'latest'));
    }

    public function recalculate(Partner $partner, ScorecardCalculationService $service)
    {
        $service->calculateForPartner($partner);
        return redirect()->back()->with('success', 'Scorecard recalculated.');
    }

    public function weights()
    {
        $weights = ScorecardWeight::orderBy('dimension')->get();
        return view('admin.scorecard-weights.index', compact('weights'));
    }

    public function updateWeights(Request $request)
    {
        $dimensions = $request->input('dimensions', []);
        $weightValues = $request->input('weights', []);
        $active = $request->input('active', []);

        foreach ($dimensions as $i => $dimension) {
            ScorecardWeight::where('dimension', $dimension)->update([
                'weight' => ($weightValues[$i] ?? 0) / 100,
                'is_active' => isset($active[$dimension]),
                'updated_by' => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', 'Scorecard weights updated.');
    }
}
