<?php

namespace Tests\Unit\Ai;

use App\Ai\Agents\EnablementDesigner;
use App\Ai\Agents\LabDesigner;
use PHPUnit\Framework\TestCase;

class WithKnowledgeBaseTest extends TestCase
{
    public function test_enablement_designer_instructions_include_citation_rules(): void
    {
        $agent = new EnablementDesigner;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Citation & Grounding Rules', $instructions);
        $this->assertStringContainsString('Prefer knowledge base content', $instructions);
        $this->assertStringContainsString('Cite your sources', $instructions);
        $this->assertStringContainsString('SearchKnowledgeBase', $instructions);
    }

    public function test_lab_designer_instructions_include_citation_rules(): void
    {
        $agent = new LabDesigner;
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Citation & Grounding Rules', $instructions);
        $this->assertStringContainsString('Prefer knowledge base content', $instructions);
    }

    public function test_knowledge_base_section_is_empty_without_prefetch(): void
    {
        $agent = new EnablementDesigner;
        $instructions = (string) $agent->instructions();

        $this->assertStringNotContainsString('Knowledge Base Context', $instructions);
    }
}
