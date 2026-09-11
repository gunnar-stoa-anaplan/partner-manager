<?php

namespace App\Services\Agent;

/**
 * Deterministic, templated analysis over the scoped ScreenContext facts. The
 * numbers are real (built from the caller's scoped queries); only the phrasing
 * is canned. Swapping to a real LLM provider is a config change — same context,
 * same facts, richer language. Makes no external calls.
 */
class SimulatedProvider implements AgentProvider
{
    public function name(): string
    {
        return 'simulated';
    }

    public function insights(ScreenContext $context): array
    {
        $f = $context->facts;
        $scope = $context->scopeLabel();
        $out = [];

        if (isset($f['open_pipeline_value'], $f['open_opportunity_count'])) {
            $out[] = sprintf(
                'Open pipeline across %s is %s over %s opportunit%s.',
                $scope,
                $this->money($f['open_pipeline_value']),
                number_format((int) $f['open_opportunity_count']),
                ((int) $f['open_opportunity_count']) === 1 ? 'y' : 'ies',
            );
        }

        if (isset($f['weighted_pipeline_value'])) {
            $out[] = sprintf('Probability-weighted pipeline is %s.', $this->money($f['weighted_pipeline_value']));
        }

        if (isset($f['partner_count'])) {
            $out[] = sprintf('%s partner%s in view%s.',
                number_format((int) $f['partner_count']),
                ((int) $f['partner_count']) === 1 ? '' : 's',
                $context->filters ? ' after the active filters' : '',
            );
        }

        if (isset($f['top_stage_label'], $f['top_stage_value'])) {
            $out[] = sprintf('The largest stage by value is %s at %s.',
                $f['top_stage_label'], $this->money($f['top_stage_value']));
        }

        if ($out === []) {
            $out[] = sprintf('No summarizable figures for this screen yet within %s.', $scope);
        }

        // Simulated-provider marker so it is never mistaken for a real analysis.
        $out[] = '(Simulated analysis — connect an LLM provider for narrative answers.)';

        return $out;
    }

    public function answer(ScreenContext $context, string $question): string
    {
        $q = strtolower($question);
        $f = $context->facts;
        $scope = $context->scopeLabel();

        $lines = [];

        if ($this->mentions($q, ['pipeline', 'acv', 'value', 'revenue', 'deal'])) {
            if (isset($f['open_pipeline_value'])) {
                $lines[] = sprintf('Open pipeline in %s is %s across %s opportunities (weighted %s).',
                    $scope,
                    $this->money($f['open_pipeline_value']),
                    number_format((int) ($f['open_opportunity_count'] ?? 0)),
                    $this->money($f['weighted_pipeline_value'] ?? 0));
            }
        }

        if ($this->mentions($q, ['partner', 'account', 'how many'])) {
            if (isset($f['partner_count'])) {
                $lines[] = sprintf('%s partners are in view within %s.', number_format((int) $f['partner_count']), $scope);
            }
        }

        if ($this->mentions($q, ['stage', 'funnel'])) {
            if (isset($f['top_stage_label'])) {
                $lines[] = sprintf('The top stage by value is %s (%s).', $f['top_stage_label'], $this->money($f['top_stage_value'] ?? 0));
            }
        }

        if ($lines === []) {
            $lines[] = sprintf(
                'I can answer from what this screen shows within %s. Available figures: %s.',
                $scope,
                $f ? implode(', ', array_keys($f)) : 'none',
            );
        }

        $lines[] = '(Simulated analysis grounded in your scoped data — a real LLM provider will give fuller answers.)';

        return implode(' ', $lines);
    }

    private function mentions(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }

        return false;
    }

    private function money(int|float $v): string
    {
        $v = (float) $v;
        if (abs($v) >= 1_000_000) {
            return '$'.rtrim(rtrim(number_format($v / 1_000_000, 1), '0'), '.').'M';
        }
        if (abs($v) >= 1_000) {
            return '$'.rtrim(rtrim(number_format($v / 1_000, 1), '0'), '.').'K';
        }

        return '$'.number_format($v);
    }
}
