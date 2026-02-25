<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\PolarisExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class PolarisExpertTest extends TestCase
{
    public function test_instructions_contain_polaris_domain_keywords(): void
    {
        $agent = new PolarisExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Polaris', $instructions);
        $this->assertStringContainsString('UX', $instructions);
        $this->assertStringContainsString('Board', $instructions);
        $this->assertStringContainsString('Migration', $instructions);
        $this->assertStringContainsString('Widget', $instructions);
        $this->assertStringContainsString('Pages', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new PolarisExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
