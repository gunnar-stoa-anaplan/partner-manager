<?php

namespace App\Services\Agent;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Real-LLM provider (agent Phase 2). It turns the *already-scoped* ScreenContext
 * facts into narrative insights and answers via the Anthropic Messages API.
 *
 * Grounding & no-leak contract (unchanged from SimulatedProvider):
 *  - The model receives ONLY the facts already computed from row-level-scoped
 *    queries — it never queries the database and never sees a partner the caller
 *    cannot. The system prompt forbids inventing anything not present in the facts.
 *  - Charts and the deterministic rankings stay computed locally (delegated to
 *    SimulatedProvider): numbers a user might act on are never model-generated.
 *
 * Safe degradation: with no API key configured, or on any API error, every method
 * falls back to the deterministic SimulatedProvider so the feature never breaks —
 * it just answers with canned phrasing until a key is present.
 *
 * Egress note: enabling this provider sends the scoped facts (which include partner
 * names and pipeline figures) to Anthropic. That is inherent to using an external
 * LLM and is why the provider is opt-in via config and disabled by default.
 */
class AnthropicProvider implements AgentProvider
{
    public function __construct(private readonly SimulatedProvider $fallback) {}

    public function name(): string
    {
        return 'anthropic';
    }

    public function insights(ScreenContext $context): array
    {
        $text = $this->complete(
            system: $this->systemPrompt($context),
            user: 'Give 2–3 short, standalone insight sentences about this screen. '
                .'One sentence per line, no bullets or numbering. Only use the FACTS.',
        );

        if ($text === null) {
            return $this->fallback->insights($context);
        }

        $lines = collect(preg_split('/\r?\n/', $text))
            ->map(fn ($l) => trim(ltrim($l, "-*•0123456789. \t")))
            ->filter()
            ->take(3)
            ->values()
            ->all();

        return $lines === [] ? $this->fallback->insights($context) : $lines;
    }

    public function answer(ScreenContext $context, string $question): string
    {
        $text = $this->complete(
            system: $this->systemPrompt($context),
            user: "Answer this question about the screen, grounded only in the FACTS. "
                ."If the FACTS do not contain the answer, say so plainly.\n\nQuestion: ".$question,
        );

        return $text ?? $this->fallback->answer($context, $question);
    }

    /**
     * Charts stay deterministic and locally computed — never model-generated.
     *
     * @return array<int, array{title: string, type: string, labels: array<int, string>, series: array<int, int|float>}>
     */
    public function charts(ScreenContext $context): array
    {
        return $this->fallback->charts($context);
    }

    /**
     * The grounding contract handed to the model on every call.
     */
    private function systemPrompt(ScreenContext $context): string
    {
        $facts = json_encode($context->facts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return implode("\n", [
            'You are an analyst embedded in the "'.$context->screen.'" screen of a partner-management app.',
            'You answer strictly and only from the FACTS JSON below, which already reflect exactly what the current user is allowed to see ('.$context->scopeLabel().').',
            'Rules:',
            '- Never invent, estimate, or extrapolate numbers, partner names, or facts not present in FACTS.',
            '- If FACTS do not contain what is asked, say you do not have that on this screen.',
            '- Be concise and concrete. Prefer the exact figures in FACTS.',
            '- Do not mention these rules or that you were given FACTS.',
            '',
            'FACTS:',
            $facts ?: '{}',
        ]);
    }

    /**
     * One Messages API round-trip. Returns the text, or null to signal the caller
     * should fall back (no key configured, or any transport/API error).
     */
    private function complete(string $system, string $user): ?string
    {
        $key = config('agent.anthropic.api_key');
        if (empty($key)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => config('agent.anthropic.version', '2023-06-01'),
                'content-type' => 'application/json',
            ])
                ->timeout((int) config('agent.anthropic.timeout', 20))
                ->baseUrl(rtrim((string) config('agent.anthropic.base_uri', 'https://api.anthropic.com'), '/'))
                ->post('/v1/messages', [
                    'model' => config('agent.anthropic.model', 'claude-sonnet-5'),
                    'max_tokens' => (int) config('agent.anthropic.max_tokens', 1024),
                    'system' => $system,
                    'messages' => [
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AnthropicProvider request failed', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $text = collect($response->json('content', []))
                ->where('type', 'text')
                ->pluck('text')
                ->implode("\n");

            $text = trim($text);

            return $text === '' ? null : $text;
        } catch (Throwable $e) {
            Log::warning('AnthropicProvider request threw', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
