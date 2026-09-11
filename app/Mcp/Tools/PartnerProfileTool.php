<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class PartnerProfileTool extends Tool
{
    protected string $name = 'partner_profile';

    protected string $description = 'Full profile of ONE partner: its contacts, projects, and most recent opportunities. Requires partner_id (resolve a name via partner_list). For pipeline economics use partner_pipeline; for health use partner_scorecard.';

    public function handle(Request $request): Response
    {
        $partner = Partner::with(['contacts', 'projects', 'opportunities' => fn ($q) => $q->latest()->limit(20)])
            ->findOrFail($request->get('partner_id'));

        return Response::json([
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'salesforce_id' => $partner->salesforce_id,
                'website' => $partner->website,
                'industry' => $partner->industry,
                'region' => $partner->region,
                'geography' => $partner->geography,
            ],
            'contacts' => $partner->contacts->map(fn ($c) => [
                'name' => $c->first_name.' '.$c->last_name,
                'email' => $c->email,
                'title' => $c->title,
                'contact_type' => $c->contact_type,
            ])->all(),
            'projects' => $partner->projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name ?? $p->project_name ?? 'Unnamed',
                'status' => $p->status,
            ])->all(),
            'recent_opportunities' => $partner->opportunities->map(fn ($o) => [
                'name' => $o->opportunity_name,
                'stage' => $o->stage,
                'amount' => $o->amount,
                'close_date' => $o->close_date,
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
