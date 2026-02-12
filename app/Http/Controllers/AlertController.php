<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerAlert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PartnerAlert::with('partner');

        if ($user->role !== 'admin') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('partner_id')) $query->where('partner_id', $request->partner_id);

        $alerts = $query->orderByRaw("FIELD(severity, 'critical', 'warning', 'info', 'positive')")
            ->orderByDesc('created_at')
            ->paginate(25);

        $stats = [
            'critical' => PartnerAlert::where('status', 'active')->where('severity', 'critical')
                ->when($user->role !== 'admin', fn($q) => $q->whereIn('partner_id', Partner::where('user_id', $user->id)->pluck('id')))->count(),
            'warning' => PartnerAlert::where('status', 'active')->where('severity', 'warning')
                ->when($user->role !== 'admin', fn($q) => $q->whereIn('partner_id', Partner::where('user_id', $user->id)->pluck('id')))->count(),
            'total_active' => PartnerAlert::where('status', 'active')
                ->when($user->role !== 'admin', fn($q) => $q->whereIn('partner_id', Partner::where('user_id', $user->id)->pluck('id')))->count(),
        ];

        $partners = $user->role === 'admin'
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        return view('act.alerts.index', compact('alerts', 'stats', 'partners'));
    }

    public function acknowledge(PartnerAlert $alert)
    {
        $alert->update(['status' => 'acknowledged', 'acknowledged_by' => auth()->id()]);
        return redirect()->back()->with('success', 'Alert acknowledged.');
    }

    public function resolve(Request $request, PartnerAlert $alert)
    {
        $alert->update(['status' => 'resolved', 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'Alert resolved.');
    }

    public function dismiss(Request $request, PartnerAlert $alert)
    {
        $alert->update(['status' => 'dismissed']);
        return redirect()->back()->with('success', 'Alert dismissed.');
    }
}
