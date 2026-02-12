<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerSolution;
use App\Models\SolutionStageHistory;
use Illuminate\Http\Request;

class SolutionLifecycleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = PartnerSolution::with('partner');

        if ($user->role !== 'admin') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        $solutions = $query->orderBy('lifecycle_stage')->orderBy('name')->get();
        $grouped = $solutions->groupBy('lifecycle_stage');

        $stages = ['build', 'market', 'sell', 'grow'];
        $stageCounts = [];
        foreach ($stages as $s) {
            $stageCounts[$s] = $grouped->get($s, collect())->count();
        }

        $partners = $user->role === 'admin'
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        return view('engage.solutions.index', compact('solutions', 'grouped', 'stageCounts', 'stages', 'partners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'name' => 'required|string|max:255',
            'lifecycle_stage' => 'required|in:build,market,sell,grow',
        ]);

        $solution = PartnerSolution::create([
            'partner_id' => $request->partner_id,
            'name' => $request->name,
            'description' => $request->description,
            'product_area' => $request->product_area,
            'industry_focus' => $request->industry_focus,
            'lifecycle_stage' => $request->lifecycle_stage,
            'stage_entered_at' => now(),
            'estimated_acv' => $request->estimated_acv,
            'deployment_duration_weeks' => $request->deployment_duration_weeks,
            'demo_url' => $request->demo_url,
            'documentation_url' => $request->documentation_url,
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('engage.solutions.index')->with('success', 'Solution created.');
    }

    public function advanceStage(Request $request, PartnerSolution $solution)
    {
        $stages = ['build' => 'market', 'market' => 'sell', 'sell' => 'grow'];
        $nextStage = $stages[$solution->lifecycle_stage] ?? null;

        if (!$nextStage) {
            return redirect()->back()->with('error', 'Solution is already at final stage.');
        }

        SolutionStageHistory::create([
            'partner_solution_id' => $solution->id,
            'from_stage' => $solution->lifecycle_stage,
            'to_stage' => $nextStage,
            'transitioned_at' => now(),
            'transitioned_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        $solution->update([
            'lifecycle_stage' => $nextStage,
            'stage_entered_at' => now(),
        ]);

        return redirect()->back()->with('success', "Solution advanced to {$nextStage} stage.");
    }
}
