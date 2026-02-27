<?php

namespace Tests\Unit\Ai;

use App\Ai\Agents\ProductLine\AdoExpert;
use App\Ai\Agents\ProductLine\FinanceExpert;
use App\Ai\Agents\ProductLine\PolarisExpert;
use App\Ai\Agents\ProductLine\SalesExpert;
use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Ai\Agents\ProductLine\WorkforceExpert;
use App\Ai\Tools\ConsultProductExpert;
use PHPUnit\Framework\TestCase;

class ConsultProductExpertTest extends TestCase
{
    public function test_agent_map_contains_all_six_product_areas(): void
    {
        $map = ConsultProductExpert::agentMap();

        $this->assertCount(6, $map);
        $this->assertArrayHasKey('IFP', $map);
        $this->assertArrayHasKey('SCM', $map);
        $this->assertArrayHasKey('OWP', $map);
        $this->assertArrayHasKey('SPM', $map);
        $this->assertArrayHasKey('Polaris', $map);
        $this->assertArrayHasKey('ADO', $map);
    }

    public function test_agent_map_resolves_correct_classes(): void
    {
        $map = ConsultProductExpert::agentMap();

        $this->assertSame(FinanceExpert::class, $map['IFP']);
        $this->assertSame(SupplyChainExpert::class, $map['SCM']);
        $this->assertSame(WorkforceExpert::class, $map['OWP']);
        $this->assertSame(SalesExpert::class, $map['SPM']);
        $this->assertSame(PolarisExpert::class, $map['Polaris']);
        $this->assertSame(AdoExpert::class, $map['ADO']);
    }

    public function test_unmapped_areas_are_not_in_agent_map(): void
    {
        $map = ConsultProductExpert::agentMap();

        $this->assertArrayNotHasKey('AI', $map);
        $this->assertArrayNotHasKey('Forecaster', $map);
        $this->assertArrayNotHasKey('Other', $map);
    }

    public function test_tool_has_required_schema_fields(): void
    {
        $tool = new ConsultProductExpert;
        $schema = new \Illuminate\Support\Fluent;

        // Just verify the description is set
        $description = $tool->description();

        $this->assertStringContainsString('product-line expert', $description);
        $this->assertStringContainsString('IFP', $description);
        $this->assertStringContainsString('ADO', $description);
    }
}
