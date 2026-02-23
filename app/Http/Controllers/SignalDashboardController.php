<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Signal;
use App\Services\SignalEngineService;
use Illuminate\Http\Request;

class SignalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Signal::with('partner');

        if ($user->role !== 'admin' && $user->role !== 'coe') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        $signals = $query->orderByRaw("FIELD(severity, 'critical', 'warning', 'info', 'positive')")
            ->orderByDesc('confidence_score')
            ->orderByDesc('created_at')
            ->paginate(25);

        $baseQuery = Signal::where('status', 'active')
            ->when($user->role !== 'admin' && $user->role !== 'coe', function ($q) use ($user) {
                $q->whereIn('partner_id', Partner::where('user_id', $user->id)->pluck('id'));
            });

        $channelStats = [];
        foreach (Signal::CHANNELS as $key => $label) {
            $channelStats[$key] = (clone $baseQuery)->where('channel', $key)->count();
        }

        $stats = [
            'channels' => $channelStats,
            'critical' => (clone $baseQuery)->where('severity', 'critical')->count(),
            'high_confidence' => (clone $baseQuery)->where('confidence_score', '>=', 0.8)->count(),
            'total_active' => (clone $baseQuery)->count(),
        ];

        $partners = ($user->role === 'admin' || $user->role === 'coe')
            ? Partner::select('id', 'name')->orderBy('name')->get()
            : Partner::where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get();

        return view('act.signals.index', compact('signals', 'stats', 'partners'));
    }

    public function show(Signal $signal, SignalEngineService $engine)
    {
        $signal->load('partner', 'acknowledgedByUser', 'linkedAlert');

        $correlatedSignals = $signal->getCorrelatedSignals();

        $partnerSignals = Signal::where('partner_id', $signal->partner_id)
            ->where('id', '!=', $signal->id)
            ->active()
            ->orderByDesc('confidence_score')
            ->limit(10)
            ->get();

        $catalog = $engine->getCatalog();
        $processorMeta = $catalog[$signal->source_processor] ?? null;

        return view('act.signals.show', compact('signal', 'correlatedSignals', 'partnerSignals', 'processorMeta'));
    }

    public function acknowledge(Signal $signal)
    {
        $signal->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Signal acknowledged.');
    }

    public function resolve(Signal $signal)
    {
        $signal->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Signal resolved.');
    }

    public function dismiss(Signal $signal)
    {
        $signal->update(['status' => 'dismissed']);

        return redirect()->back()->with('success', 'Signal dismissed.');
    }
}
