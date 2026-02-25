<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\FinanceExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class FinanceExpertTest extends TestCase
{
    public function test_instructions_contain_ifp_domain_keywords(): void
    {
        $agent = new FinanceExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('IFP', $instructions);
        $this->assertStringContainsString('Budgeting', $instructions);
        $this->assertStringContainsString('Forecasting', $instructions);
        $this->assertStringContainsString('Consolidation', $instructions);
        $this->assertStringContainsString('Revenue', $instructions);
        $this->assertStringContainsString('P&L', $instructions);
        $this->assertStringContainsString('CUMULATE', $instructions);
        $this->assertStringContainsString('OFFSET', $instructions);
        $this->assertStringContainsString('LOOKUP', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new FinanceExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
