<?php

namespace App\Ai\Tools;

use App\Services\KnowledgeBaseSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchKnowledgeBase implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the organizational knowledge base using semantic similarity. '
            .'Returns the most relevant document excerpts matching the query. '
            .'Use this to ground your responses in organizational knowledge, '
            .'reference materials, best practices, and documentation.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(KnowledgeBaseSearchService::class);

        $results = $service->search(
            query: (string) $request->string('query'),
            limit: $request['limit'] ?? null,
        );

        return $service->formatAsToolOutput($results);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->required()
                ->description('The search query to find relevant knowledge base content'),
            'limit' => $schema->integer()
                ->description('Maximum number of results to return (default: 10)'),
        ];
    }
}
