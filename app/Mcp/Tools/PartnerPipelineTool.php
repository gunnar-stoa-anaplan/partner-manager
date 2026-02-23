<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a partner\'s opportunity pipeline with stage breakdown and revenue totals.')]
class PartnerPipelineTool extends Tool
{
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
