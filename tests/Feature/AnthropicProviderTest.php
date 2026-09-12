<?php

namespace Tests\Feature;

use App\Services\Agent\AnthropicProvider;
use App\Services\Agent\ScreenContext;
use App\Services\Agent\SimulatedProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The real-LLM provider is a thin, grounded scaffold. These tests pin the two
 * behaviours that matter before an API key exists: it degrades to the simulated
 * provider without a key or on error, and when a key IS present it sends the
 * scoped facts as grounding and parses the reply. No real network calls are made.
 */
class AnthropicProviderTest extends TestCase
{
    private function context(): ScreenContext
    {
        return new ScreenContext(
            screen: 'pipeline',
            facts: [
                'open_pipeline_value' => 1_250_000,
                'open_opportunity_count' => 12,
                'stage_breakdown' => [
                    ['stage' => 'Proposal', 'value' => 800_000],
                    ['stage' => 'Qualification', 'value' => 450_000],
                ],
            ],
            unrestricted: false,
        );
    }

    private function provider(): AnthropicProvider
    {
        return new AnthropicProvider(new SimulatedProvider);
    }

    public function test_degrades_to_simulated_when_no_api_key(): void
    {
        config(['agent.anthropic.api_key' => null]);
        Http::fake(); // any accidental call would return an empty 200; assert none happen

        $answer = $this->provider()->answer($this->context(), 'what is my pipeline?');

        $this->assertStringContainsString('Simulated analysis', $answer);
        Http::assertNothingSent();
    }

    public function test_uses_api_and_grounds_in_facts_when_key_present(): void
    {
        config(['agent.anthropic.api_key' => 'test-key', 'agent.anthropic.model' => 'claude-sonnet-5']);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => 'Open pipeline is $1.25M across 12 opportunities.'],
                ],
            ], 200),
        ]);

        $answer = $this->provider()->answer($this->context(), 'what is my pipeline?');

        $this->assertSame('Open pipeline is $1.25M across 12 opportunities.', $answer);

        // The scoped facts must be sent as grounding, and the caller's question too.
        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($body['system'], 'open_pipeline_value')
                && str_contains($body['system'], '1250000')
                && $body['messages'][0]['content'] !== ''
                && $request->hasHeader('x-api-key', 'test-key');
        });
    }

    public function test_falls_back_on_api_error(): void
    {
        config(['agent.anthropic.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response(['error' => 'overloaded'], 529)]);

        $answer = $this->provider()->answer($this->context(), 'what is my pipeline?');

        $this->assertStringContainsString('Simulated analysis', $answer);
    }

    public function test_insights_parses_lines_and_strips_bullets(): void
    {
        config(['agent.anthropic.api_key' => 'test-key']);
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [[
                    'type' => 'text',
                    'text' => "- Pipeline is concentrated in Proposal.\n- 12 open deals in view.\n- Weighted view is healthy.",
                ]],
            ], 200),
        ]);

        $insights = $this->provider()->insights($this->context());

        $this->assertCount(3, $insights);
        $this->assertSame('Pipeline is concentrated in Proposal.', $insights[0]);
        $this->assertStringNotContainsString('- ', $insights[1]);
    }

    public function test_charts_are_deterministic_and_match_simulated(): void
    {
        config(['agent.anthropic.api_key' => 'test-key']);
        Http::fake(); // charts must not call the API at all

        $ctx = $this->context();
        $anthropicCharts = $this->provider()->charts($ctx);
        $simulatedCharts = (new SimulatedProvider)->charts($ctx);

        $this->assertEquals($simulatedCharts, $anthropicCharts);
        $this->assertNotEmpty($anthropicCharts);
        Http::assertNothingSent();
    }
}
