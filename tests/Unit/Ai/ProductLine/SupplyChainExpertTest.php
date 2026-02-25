<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class SupplyChainExpertTest extends TestCase
{
    public function test_instructions_contain_scm_domain_keywords(): void
    {
        $agent = new SupplyChainExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('SCM', $instructions);
        $this->assertStringContainsString('Demand Planning', $instructions);
        $this->assertStringContainsString('Supply Planning', $instructions);
        $this->assertStringContainsString('S&OP', $instructions);
        $this->assertStringContainsString('Inventory', $instructions);
        $this->assertStringContainsString('MOVINGSUM', $instructions);
        $this->assertStringContainsString('RANK', $instructions);
        $this->assertStringContainsString('LAG', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new SupplyChainExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
