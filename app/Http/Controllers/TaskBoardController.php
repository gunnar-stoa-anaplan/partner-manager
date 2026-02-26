<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTask;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskBoardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PartnerTask::with(['partner', 'template', 'assignedUser', 'assignedContact']);

        if (! $user->isAdmin()) {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('requirement_level')) {
            $query->where('requirement_level', $request->requirement_level);
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->orderBy('due_date')
            ->get();

        $partners = $user->isAdmin()
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        // Group by status for kanban
        $grouped = $tasks->groupBy('status');

        $stats = [
            'total' => $tasks->count(),
            'overdue' => $tasks->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
            'completed_this_week' => $tasks->where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfWeek())->count(),
            'required_incomplete' => $tasks->where('requirement_level', 'required')
                ->whereIn('status', ['not_started', 'in_progress'])->count(),
        ];

        return view('plan.tasks.index', compact('tasks', 'grouped', 'partners', 'stats'));
    }

    public function updateStatus(Request $request, PartnerTask $task)
    {
        $request->validate(['status' => 'required|in:not_started,in_progress,completed,blocked,waived']);

        $data = ['status' => $request->status];

        if ($request->status === 'in_progress' && ! $task->started_at) {
            $data['started_at'] = now();
        }
        if ($request->status === 'completed') {
            $data['completed_at'] = now();
            $data['completed_by'] = auth()->id();
            $data['completion_evidence'] = $request->input('evidence');
        }
        if ($request->status === 'waived') {
            $data['waived_by'] = auth()->id();
            $data['waived_reason'] = $request->input('reason');
        }

        $task->update($data);

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function addComment(Request $request, PartnerTask $task)
    {
        $request->validate(['body' => 'required|string']);

        TaskComment::create([
            'partner_task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }
}
