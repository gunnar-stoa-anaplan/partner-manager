<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReportingHierarchyRequest;
use App\Models\User;
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

    public function update(UpdateReportingHierarchyRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'manager_id' => $request->manager_id,
        ]);

        return redirect()
            ->route('reporting-hierarchy.index')
            ->with('success', "Manager updated for {$user->name}.");
    }
}
