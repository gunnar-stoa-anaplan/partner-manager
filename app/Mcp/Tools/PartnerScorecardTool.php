<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class PartnerScorecardTool extends Tool
{
    protected string $name = 'partner_scorecard';

    protected string $description = 'ONE partner\'s health scorecard: overall score plus goal progress, certifications, and key metrics. Requires partner_id. For what needs attention use signal_feed; for capability/staffing gaps use delivery_readiness.';

    public function handle(Request $request): Response
    {
        $partner = Partner::with(['goals', 'certifications', 'resources'])
            ->withCount(['opportunities', 'projects', 'contacts', 'leads'])
            ->findOrFail($request->get('partner_id'));

        $goals = $partner->goals->map(fn ($g) => [
            'name' => $g->name ?? $g->goal_name ?? 'Goal',
            'target' => $g->target_value ?? $g->target,
            'current' => $g->current_value ?? $g->current,
            'status' => $g->status,
        ]);

        return Response::json([
            'partner' => $partner->name,
            'counts' => [
                'opportunities' => $partner->opportunities_count,
                'projects' => $partner->projects_count,
                'contacts' => $partner->contacts_count,
                'leads' => $partner->leads_count,
                'certifications' => $partner->certifications->count(),
                'certified_resources' => $partner->resources->where('status', 'Completed')->count(),
            ],
            'goals' => $goals->all(),
            'pipeline_value' => $partner->opportunities()->sum('amount'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'partner_id' => $schema->integer()->description('The partner ID')->required(),
        ];
    }
}
