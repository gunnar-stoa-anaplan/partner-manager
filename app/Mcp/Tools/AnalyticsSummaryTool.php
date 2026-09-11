<?php

namespace App\Mcp\Tools;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Resource;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AnalyticsSummaryTool extends Tool
{
    protected string $name = 'analytics_summary';

    protected string $description = 'Portfolio-wide totals across ALL partners in your scope: partner counts by region/industry, total and by-stage pipeline, project status, and enablement counts. Takes no arguments. For a single partner use partner_pipeline or partner_profile, not this.';

    public function handle(Request $request): Response
    {
        return Response::json([
            'partners' => [
                'total' => Partner::count(),
                'by_region' => Partner::query()
                    ->selectRaw('region, count(*) as count')
                    ->groupBy('region')
                    ->pluck('count', 'region')
                    ->all(),
                'by_industry' => Partner::query()
                    ->selectRaw('industry, count(*) as count')
                    ->whereNotNull('industry')
                    ->groupBy('industry')
                    ->pluck('count', 'industry')
                    ->all(),
            ],
            'pipeline' => [
                'total_value' => Opportunity::sum('amount'),
                'opportunity_count' => Opportunity::count(),
                'by_stage' => Opportunity::query()
                    ->selectRaw('stage, count(*) as count, sum(amount) as total')
                    ->groupBy('stage')
                    ->get()
                    ->keyBy('stage')
                    ->map(fn ($row) => [
                        'count' => $row->count,
                        'total' => $row->total,
                    ])
                    ->all(),
            ],
            'projects' => [
                'total' => Project::count(),
                'by_status' => Project::query()
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->all(),
            ],
            'enablement' => [
                'total_contacts' => Contact::count(),
                'total_resources' => Resource::count(),
                'certified_resources' => Resource::where('status', 'Completed')->count(),
            ],
            'leads' => [
                'total' => Lead::count(),
            ],
        ]);
    }
}
