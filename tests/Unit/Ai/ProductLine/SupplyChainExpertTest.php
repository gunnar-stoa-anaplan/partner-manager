<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class SupplyChainExpertTest extends TestCase
{
    public function test_instructions_cover_all_eight_scm_applications(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Demand Planning (DP)', $instructions);
        $this->assertStringContainsString('Statistical Forecasting (SF)', $instructions);
        $this->assertStringContainsString('Inventory Planning (IP)', $instructions);
        $this->assertStringContainsString('Supply Planning (SP)', $instructions);
        $this->assertStringContainsString('Rough Cut Capacity Planning (RCCP)', $instructions);
        $this->assertStringContainsString('Integrated Business Planning (IBP)', $instructions);
        $this->assertStringContainsString('Spend Analysis and Forecasting', $instructions);
        $this->assertStringContainsString('Trade Promotion Management (TPM)', $instructions);
    }

    public function test_instructions_contain_process_step_references(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('DP/001', $instructions);
        $this->assertStringContainsString('ST/001', $instructions);
        $this->assertStringContainsString('DA/001', $instructions);
    }

    public function test_instructions_contain_anaplan_platform_concepts(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('PlanIQ', $instructions);
        $this->assertStringContainsString('CloudWorks', $instructions);
        $this->assertStringContainsString('Hub-and-Spoke', $instructions);
        $this->assertStringContainsString('cell count', $instructions);
        $this->assertStringContainsString('sparsity', $instructions);
        $this->assertStringContainsString('ALM', $instructions);
        $this->assertStringContainsString('Data Orchestrator', $instructions);
        $this->assertStringContainsString('Polaris', $instructions);
    }

    public function test_instructions_contain_formula_patterns(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('OFFSET', $instructions);
        $this->assertStringContainsString('CUMULATE', $instructions);
        $this->assertStringContainsString('COLLECT', $instructions);
        $this->assertStringContainsString('SELECT', $instructions);
        $this->assertStringContainsString('LOOKUP', $instructions);
        $this->assertStringContainsString('IF THEN ELSE', $instructions);
        $this->assertStringContainsString('APPLIES TO', $instructions);
        $this->assertStringContainsString('MOVINGSUM', $instructions);
        $this->assertStringContainsString('RANK', $instructions);
        $this->assertStringContainsString('LAG', $instructions);
    }

    public function test_instructions_contain_implementation_guidance(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Module Blueprints', $instructions);
        $this->assertStringContainsString('Anti-Patterns', $instructions);
        $this->assertStringContainsString('Architecture Rationale', $instructions);
        $this->assertStringContainsString('Knowledge Base', $instructions);
        $this->assertStringContainsString('V2 Upgrade Methodology', $instructions);
    }

    public function test_instructions_contain_progressive_lab_design(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Progressive Lab Design', $instructions);
        $this->assertStringContainsString('Foundation Lab', $instructions);
        $this->assertStringContainsString('Intermediate Lab', $instructions);
        $this->assertStringContainsString('Advanced Lab', $instructions);
        $this->assertStringContainsString('Mastery Lab', $instructions);
    }

    public function test_instructions_contain_demand_analysis_foundation(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Product Chaining', $instructions);
        $this->assertStringContainsString('Product Effectivity', $instructions);
        $this->assertStringContainsString('Demand Classification', $instructions);
        $this->assertStringContainsString('Outlier Analysis', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new SupplyChainExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
