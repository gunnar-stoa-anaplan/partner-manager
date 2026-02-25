<?php

namespace Tests\Unit\Ai\ProductLine;

use App\Ai\Agents\ProductLine\SalesExpert;
use App\Ai\Tools\SearchKnowledgeBase;
use PHPUnit\Framework\TestCase;

class SalesExpertTest extends TestCase
{
    public function test_instructions_contain_spm_domain_keywords(): void
    {
        $agent = new SalesExpert;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('SPM', $instructions);
        $this->assertStringContainsString('Territory', $instructions);
        $this->assertStringContainsString('Quota', $instructions);
        $this->assertStringContainsString('Incentive Compensation', $instructions);
        $this->assertStringContainsString('Pipeline', $instructions);
        $this->assertStringContainsString('RANK', $instructions);
        $this->assertStringContainsString('CUMULATE', $instructions);
        $this->assertStringContainsString('COLLECT', $instructions);
    }

    public function test_has_search_knowledge_base_tool(): void
    {
        $agent = new SalesExpert;
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(1, $tools);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[0]);
    }
}
