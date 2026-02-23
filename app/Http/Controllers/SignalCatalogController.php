<?php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Services\SignalEngineService;
use Illuminate\View\View;

class SignalCatalogController extends Controller
{
    public function __invoke(SignalEngineService $engine): View
    {
        $catalog = $engine->getCatalog();

        $signalStats = Signal::query()
            ->selectRaw('source_processor, count(*) as total, sum(status = "active") as active')
            ->groupBy('source_processor')
            ->pluck('total', 'source_processor')
            ->toArray();

        $activeStats = Signal::query()
            ->selectRaw('source_processor, count(*) as active')
            ->where('status', 'active')
            ->groupBy('source_processor')
            ->pluck('active', 'source_processor')
            ->toArray();

        return view('act.signals.catalog', compact('catalog', 'signalStats', 'activeStats'));
    }
}
