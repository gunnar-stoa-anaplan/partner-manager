<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class PartnerPipelineTool extends Tool
{
    protected string $name = 'partner_pipeline';

    protected string $description = 'ONE partner\'s opportunity pipeline: total and weighted pipeline (USD), a per-stage breakdown, and the deal list. Requires partner_id. For pipeline across ALL partners in scope use analytics_summary instead.';

    public function handle(Request $request): Response
    {
        $partner = Partner::findOrFail($request->get('partner_id'));

        $opportunities = $partner->opportunities()
            ->select('opportunity_name', 'stage', 'amount', 'close_date', 'probability', 'type', 'fiscal_quarter', 'fiscal_year')
            ->orderBy('close_date')
            ->get();

        $byStage = $opportunities->groupBy('stage')->map(fn ($group) => [
            'count' => $group->count(),
            'total_amount' => $group->sum('amount'),
            'avg_probability' => round($group->avg('probability'), 1),
        ]);

        return Response::json([
            'partner' => $partner->name,
            'total_pipeline' => $opportunities->sum('amount'),
            'opportunity_count' => $opportunities->count(),
            'weighted_pipeline' => round($opportunities->sum(fn ($o) => ($o->amount ?? 0) * (($o->probability ?? 0) / 100)), 2),
            'by_stage' => $byStage->all(),
            'opportunities' => $opportunities->map(fn ($o) => [
                'name' => $o->opportunity_name,
                'stage' => $o->stage,
                'amount' => $o->amount,
                'close_date' => $o->close_date,
                'probability' => $o->probability,
                'type' => $o->type,
                'fiscal_quarter' => $o->fiscal_quarter,
                'fiscal_year' => $o->fiscal_year,
            ])->all(),
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
