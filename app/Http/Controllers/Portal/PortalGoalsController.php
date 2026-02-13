<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PartnerAssignedGoal;
use Illuminate\Support\Facades\Auth;

class PortalGoalsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $goals = PartnerAssignedGoal::where('partner_id', $partner->id)
            ->orderBy('category')
            ->get();

        $categories = $goals->groupBy('category')->map(function ($group) {
            $withTarget = $group->where('target_value', '>', 0);

            return [
                'goals' => $group,
                'avg_attainment' => $withTarget->count() > 0
                    ? round($withTarget->avg(fn ($g) => min(($g->actual_value / $g->target_value) * 100, 100)), 1)
                    : 0,
            ];
        });

        return view('portal.goals.index', compact('contact', 'partner', 'goals', 'categories'));
    }
}
