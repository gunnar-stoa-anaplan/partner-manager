<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\WorkforceExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class WorkforceExpertTest extends TestCase
{
    public function test_instructions_contain_owp_domain_keywords(): void
    {
        $agent = new WorkforceExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('OWP', $instructions);
        $this->assertStringContainsString('Headcount', $instructions);
        $this->assertStringContainsString('Skills', $instructions);
        $this->assertStringContainsString('Capacity', $instructions);
        $this->assertStringContainsString('Attrition', $instructions);
        $this->assertStringContainsString('Compensation', $instructions);
        $this->assertStringContainsString('LOOKUP', $instructions);
        $this->assertStringContainsString('CUMULATE', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new WorkforceExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
