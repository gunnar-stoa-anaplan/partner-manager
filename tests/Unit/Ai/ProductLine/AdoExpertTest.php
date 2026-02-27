<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\AdoExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class AdoExpertTest extends TestCase
{
    public function test_instructions_contain_ado_domain_keywords(): void
    {
        $agent = new AdoExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('ADO', $instructions);
        $this->assertStringContainsString('Page Builder', $instructions);
        $this->assertStringContainsString('Custom Views', $instructions);
        $this->assertStringContainsString('App Publishing', $instructions);
        $this->assertStringContainsString('Action', $instructions);
        $this->assertStringContainsString('Component', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new AdoExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
