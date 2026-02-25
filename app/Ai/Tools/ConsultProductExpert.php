<?php

namespace App\Ai\Tools;

use App\Ai\Agents\ProductLine\AdoExpert;
use App\Ai\Agents\ProductLine\FinanceExpert;
use App\Ai\Agents\ProductLine\PolarisExpert;
use App\Ai\Agents\ProductLine\SalesExpert;
use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Ai\Agents\ProductLine\WorkforceExpert;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ConsultProductExpert implements Tool
{
    /**
     * Map of product areas to their specialized agent classes.
     *
     * @var array<string, class-string>
     */
    protected const AGENT_MAP = [
        'IFP' => FinanceExpert::class,
        'SCM' => SupplyChainExpert::class,
        'OWP' => WorkforceExpert::class,
        'SPM' => SalesExpert::class,
        'Polaris' => PolarisExpert::class,
        'ADO' => AdoExpert::class,
    ];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Consult a specialized product-line expert for deep domain guidance. '
            .'Each product area (IFP, SCM, OWP, SPM, Polaris, ADO) has a dedicated expert '
            .'with deep knowledge of that application\'s domain, Anaplan model patterns, '
            .'formulas, and real-world implementation scenarios. Always consult the relevant '
            .'expert before designing workshop or lab content.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $productArea = (string) $request->string('product_area');
        $topic = (string) $request->string('topic');

        $agentClass = self::AGENT_MAP[$productArea] ?? null;

        if ($agentClass === null) {
            return "No specialized expert is available for the \"{$productArea}\" product area. "
                .'Use the SearchKnowledgeBase tool to find relevant documentation instead.';
        }

        /** @var \App\Ai\Agents\ProductLine\FinanceExpert|\App\Ai\Agents\ProductLine\SupplyChainExpert|\App\Ai\Agents\ProductLine\WorkforceExpert|\App\Ai\Agents\ProductLine\SalesExpert|\App\Ai\Agents\ProductLine\PolarisExpert|\App\Ai\Agents\ProductLine\AdoExpert $agent */
        $agent = $agentClass::make()->withPrefetchedContext("{$productArea} {$topic}");

        $response = $agent->prompt(
            "Provide detailed domain expertise and guidance for the following topic in {$productArea}: {$topic}. "
            .'Include specific Anaplan module names, formula examples, and realistic business scenarios '
            .'that should be used in workshop or lab content for this topic.'
        );

        return (string) $response;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_area' => $schema->string()
                ->required()
                ->description('The Anaplan product area to consult: IFP, SCM, OWP, SPM, Polaris, or ADO'),
            'topic' => $schema->string()
                ->required()
                ->description('The specific topic or focus area for the workshop/lab being designed'),
        ];
    }

    /**
     * Get the map of product areas to agent classes.
     *
     * @return array<string, class-string>
     */
    public static function agentMap(): array
    {
        return self::AGENT_MAP;
    }
}
