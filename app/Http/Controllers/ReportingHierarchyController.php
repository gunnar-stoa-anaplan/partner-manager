<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReportingHierarchyRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportingHierarchyController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canManageHierarchy(), 403);

        $users = User::with(['manager', 'directReports'])
            ->orderBy('name')
            ->get();

        $managers = User::orderBy('name')->get();

        return view('reporting-hierarchy.index', compact('users', 'managers'));
    }

    /**
     * Return all users as JSON for the org chart.
     */
    public function data(): JsonResponse
    {
        abort_unless(auth()->user()->canManageHierarchy(), 403);

        $users = User::with('role')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'manager_id' => $u->manager_id,
                'role' => $u->role?->display_name ?? 'No Role',
                'role_slug' => $u->role?->slug,
            ]);

        return response()->json($users);
    }

    public function update(UpdateReportingHierarchyRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $user->update([
            'manager_id' => $request->manager_id,
        ]);

        if ($request->wantsJson()) {
            $user->load(['manager', 'directReports']);

            return response()->json([
                'success' => true,
                'message' => "Manager updated for {$user->name}.",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'manager_id' => $user->manager_id,
                    'manager_name' => $user->manager?->name,
                    'direct_report_count' => $user->directReports->count(),
                    'role' => $user->role?->display_name ?? 'No Role',
                ],
            ]);
        }

        return redirect()
            ->route('reporting-hierarchy.index')
            ->with('success', "Manager updated for {$user->name}.");
    }
}
