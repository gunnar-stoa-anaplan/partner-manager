<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Resources\DataDictionaryResource;
use App\Mcp\Servers\PartnerManagerServer;
use Illuminate\Support\Str;
use ReflectionClass;
use Tests\TestCase;

/**
 * "Eval" for MCP-client-facing documentation: the metadata a client actually
 * receives (server instructions, tool names + descriptions, the data-dictionary
 * resource) must be present, disambiguating, and — importantly for laravel/mcp
 * v0.5.x — surfaced via PROPERTIES, not the inert #[Description]/#[Name]
 * attributes (which this version ignores). A deterministic proxy for "a client
 * can find and pick the right tool from the metadata alone."
 */
class McpServerDocsTest extends TestCase
{
    private const EXPECTED_TOOL_NAMES = [
        'partner_list', 'partner_profile', 'partner_pipeline', 'partner_scorecard',
        'analytics_summary', 'integration_status', 'signal_feed', 'signal_explain',
        'delivery_readiness', 'strategy_plan',
    ];

    /** @return array{name:string, version:string, instructions:string, tools:array, resources:array} */
    private function serverMeta(): array
    {
        $r = new ReflectionClass(PartnerManagerServer::class);
        $server = $r->newInstanceWithoutConstructor();
        $read = fn (string $p) => tap($r->getProperty($p), fn ($x) => $x->setAccessible(true))->getValue($server);

        return [
            'name' => $read('name'),
            'version' => $read('version'),
            'instructions' => $read('instructions'),
            'tools' => $read('tools'),
            'resources' => $read('resources'),
        ];
    }

    public function test_server_identity_and_instructions_reach_clients(): void
    {
        $meta = $this->serverMeta();

        $this->assertSame('Partner Manager', $meta['name']);
        $this->assertNotSame('0.0.1', $meta['version']);

        $instructions = $meta['instructions'];
        $this->assertGreaterThan(400, strlen($instructions), 'server instructions should be substantive');
        // The scope contract must be stated for clients.
        $this->assertStringContainsStringIgnoringCase('scope', $instructions);
        $this->assertStringContainsStringIgnoringCase('read-only', $instructions);
        // Every tool is referenced in the selection guide.
        foreach (self::EXPECTED_TOOL_NAMES as $name) {
            $this->assertStringContainsString($name, $instructions, "instructions should mention $name");
        }
    }

    public function test_every_tool_has_a_clean_name_and_disambiguating_description(): void
    {
        $meta = $this->serverMeta();
        $this->assertCount(count(self::EXPECTED_TOOL_NAMES), $meta['tools']);

        $names = [];
        $descriptions = [];

        foreach ($meta['tools'] as $toolClass) {
            $tool = app($toolClass);
            $name = $tool->name();
            $description = $tool->description();

            // Name is the explicit snake_case name, not the kebab class default.
            $this->assertContains($name, self::EXPECTED_TOOL_NAMES, "$toolClass exposes an expected name");
            $this->assertNotSame(Str::kebab(class_basename($toolClass)), $name, "$toolClass must set an explicit name property");

            // Description is real, not the headline fallback of the class name.
            $this->assertNotSame(Str::headline(class_basename($toolClass)), $description, "$toolClass must set a description property");
            $this->assertGreaterThan(60, strlen($description), "$toolClass description should be substantive");

            $names[] = $name;
            $descriptions[] = $description;
        }

        $this->assertSame($names, array_unique($names), 'tool names must be unique');
        $this->assertSame($descriptions, array_unique($descriptions), 'tool descriptions must be unique (disambiguated)');
    }

    public function test_data_dictionary_resource_is_registered_and_documented(): void
    {
        $meta = $this->serverMeta();
        $this->assertContains(DataDictionaryResource::class, $meta['resources']);

        $resource = app(DataDictionaryResource::class);
        $this->assertSame('data-dictionary', $resource->name());
        $this->assertGreaterThan(40, strlen($resource->description()));
    }
}
