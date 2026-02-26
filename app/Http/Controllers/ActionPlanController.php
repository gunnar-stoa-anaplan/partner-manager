<?php

namespace App\Http\Controllers;

use App\Models\ActionPlan;
use App\Models\ActionPlanItem;
use App\Models\Partner;
use Illuminate\Http\Request;

class ActionPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ActionPlan::with(['partner', 'items', 'ownerUser', 'creator']);

        if (! $user->isAdmin()) {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $plans = $query->orderByRaw("FIELD(status, 'overdue', 'in_progress', 'open', 'completed', 'cancelled')")
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get();

        $partners = $user->isAdmin()
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        $grouped = $plans->groupBy('status');

        return view('plan.action-plans.index', compact('plans', 'grouped', 'partners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $plan = ActionPlan::create([
            'partner_id' => $request->partner_id,
            'title' => $request->title,
            'description' => $request->description,
            'source' => $request->source ?? 'psm_initiative',
            'priority' => $request->priority,
            'owner_type' => $request->owner_type ?? 'psm',
            'owner_user_id' => auth()->id(),
            'due_date' => $request->due_date,
            'created_by' => auth()->id(),
            'fiscal_quarter' => $request->fiscal_quarter,
        ]);

        if ($request->has('items')) {
            foreach ($request->items as $i => $item) {
                ActionPlanItem::create([
                    'action_plan_id' => $plan->id,
                    'description' => $item['description'],
                    'due_date' => $item['due_date'] ?? null,
                    'assigned_to' => $item['assigned_to'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('plan.action-plans.index')->with('success', 'Action plan created.');
    }

    public function updateItemStatus(Request $request, ActionPlanItem $item)
    {
        $item->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'done' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Item updated.');
    }
}
