<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyPlanReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'strategy_plan_id' => 'required|exists:strategy_plans,id',
            'review_type' => 'required|in:monthly,quarterly,annual,ad_hoc',
            'review_date' => 'required|date',
        ]);

        StrategyPlanReview::create([
            ...$request->only('strategy_plan_id', 'review_type', 'review_date'),
            'reviewed_by' => auth()->id(),
            'status' => 'scheduled',
        ]);

        return redirect()->route('strategy.plans.show', $request->strategy_plan_id)
            ->with('success', 'Review scheduled successfully.');
    }

    public function show(StrategyPlanReview $review): View
    {
        $review->load(['plan.targets', 'plan.assumptions', 'plan.budgets', 'reviewer']);

        return view('strategy.reviews.show', compact('review'));
    }

    public function complete(Request $request, StrategyPlanReview $review): RedirectResponse
    {
        $request->validate([
            'summary' => 'required|string',
            'findings' => 'nullable|array',
        ]);

        $review->update([
            'status' => 'completed',
            'summary' => $request->summary,
            'findings' => $request->findings,
            'review_date' => today(),
        ]);

        return redirect()->route('strategy.reviews.show', $review)
            ->with('success', 'Review completed.');
    }
}
