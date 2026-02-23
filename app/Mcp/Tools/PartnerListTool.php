<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search and filter partners. Returns a list of partners with basic info.')]
class PartnerListTool extends Tool
{
    public function handle(Request $request): Response
    {
        $query = Partner::query()->withCount(['opportunities', 'projects', 'contacts']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($region = $request->get('region')) {
            $query->where('region', $region);
        }

        if ($geography = $request->get('geography')) {
            $query->where('geography', $geography);
        }

        if ($industry = $request->get('industry')) {
            $query->where('industry', $industry);
        }

        $limit = min($request->get('limit') ?? 25, 100);

        $partners = $query->limit($limit)->get();

        return Response::json([
            'count' => $partners->count(),
            'partners' => $partners->map(fn (Partner $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'industry' => $p->industry,
                'region' => $p->region,
                'geography' => $p->geography,
                'opportunities_count' => $p->opportunities_count,
                'projects_count' => $p->projects_count,
                'contacts_count' => $p->contacts_count,
            ])->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Filter partners by name (partial match)'),
            'region' => $schema->string()->description('Filter by region'),
            'geography' => $schema->string()->description('Filter by geography'),
            'industry' => $schema->string()->description('Filter by industry'),
            'limit' => $schema->integer()->description('Max results (default 25, max 100)'),
        ];
    }
}
