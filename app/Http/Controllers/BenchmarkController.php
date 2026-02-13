<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Services\BenchmarkService;

class BenchmarkController extends Controller
{
    public function index(BenchmarkService $service): \Illuminate\View\View
    {
        $user = auth()->user();
        $partnersQuery = $user->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name', 'geography', 'region')
            ->orderBy('name')
            ->get();

        $partnerIds = $partners->pluck('id');
        $benchmarks = $service->computeAll($partnerIds);
        $dimensions = BenchmarkService::DIMENSIONS;

        return view('measure.benchmarks.index', compact('partners', 'benchmarks', 'dimensions'));
    }

    public function partner(Partner $partner, BenchmarkService $service): \Illuminate\View\View
    {
        $user = auth()->user();
        $allPartnersQuery = $user->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $allPartnerIds = $allPartnersQuery->pluck('id');

        $benchmark = $service->computeForPartner($partner, $allPartnerIds);
        $dimensions = BenchmarkService::DIMENSIONS;

        return view('measure.benchmarks.partner', compact('partner', 'benchmark', 'dimensions'));
    }
}
