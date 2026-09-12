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

        if (! empty($f['delivery_enablement_gaps'])) {
            $topGap = $f['delivery_enablement_gaps'][0];
            if (($topGap['gap'] ?? 0) > 0) {
                $out[] = sprintf(
                    '%s has the largest delivery-enablement gap in %s (%d open deals vs %d active certifications).',
                    $topGap['partner'], $scope, $topGap['open_opps'], $topGap['certified'],
                );
            }
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

        if ($this->mentions($q, ['enablement', 'enable', 'deliver', 'delivery', 'readiness', 'certif', 'gap', 'resource'])) {
            $gaps = collect($f['delivery_enablement_gaps'] ?? [])->filter(fn ($g) => ($g['gap'] ?? 0) > 0)->take(5);
            if ($gaps->isNotEmpty()) {
                $ranked = $gaps->map(fn ($g, $i) => sprintf('%d) %s (gap %d: %d open deals, %d certified)',
                    $i + 1, $g['partner'], $g['gap'], $g['open_opps'], $g['certified']))->implode('; ');
                $lines[] = sprintf('Partners in %s most needing delivery enablement (open deals minus active certifications): %s.', $scope, $ranked);
            } else {
                $lines[] = sprintf('No delivery-enablement gaps in %s — active certifications keep pace with open pipeline.', $scope);
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

    public function charts(ScreenContext $context): array
    {
        $f = $context->facts;
        $charts = [];

        if (! empty($f['stage_breakdown'])) {
            $charts[] = [
                'title' => 'Open pipeline by stage',
                'type' => 'bar',
                'labels' => array_map(fn ($r) => (string) $r['stage'], $f['stage_breakdown']),
                'series' => array_map(fn ($r) => round((float) $r['value']), $f['stage_breakdown']),
            ];
        }

        $gaps = collect($f['delivery_enablement_gaps'] ?? [])->filter(fn ($g) => ($g['gap'] ?? 0) > 0)->take(8);
        if ($gaps->isNotEmpty()) {
            $charts[] = [
                'title' => 'Partners by delivery-enablement gap',
                'type' => 'bar',
                'labels' => $gaps->pluck('partner')->map(fn ($n) => (string) $n)->all(),
                'series' => $gaps->pluck('gap')->map(fn ($g) => (int) $g)->all(),
            ];
        }

        return $charts;
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
