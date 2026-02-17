<?php

namespace App\Services;

use App\Contracts\ChatServiceInterface;
use Generator;

class SimulatedChatService implements ChatServiceInterface
{
    /**
     * Keyword groups mapped to response builder methods.
     *
     * @var array<string, array<string>>
     */
    protected array $keywordMap = [
        'buildPipelineResponse' => ['pipeline', 'opportunity', 'deal', 'revenue', 'sales'],
        'buildHealthResponse' => ['health', 'risk', 'score', 'at risk', 'critical'],
        'buildGoalsResponse' => ['goal', 'target', 'attainment', 'quota'],
        'buildTasksResponse' => ['task', 'overdue', 'todo', 'action item', 'due'],
        'buildCertificationsResponse' => ['certification', 'cert', 'expiring', 'credential'],
    ];

    /** {@inheritDoc} */
    public function streamResponse(string $message, array $context): Generator
    {
        $text = $this->buildResponse($message, $context);

        $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            yield $word;
            usleep(rand(30000, 80000));
        }
    }

    protected function buildResponse(string $message, array $context): string
    {
        $lower = strtolower($message);

        foreach ($this->keywordMap as $method => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $this->{$method}($context);
                }
            }
        }

        return $this->buildOverviewResponse($context);
    }

    protected function buildPipelineResponse(array $context): string
    {
        $pipeline = $context['pipeline_summary'] ?? [];
        $total = number_format($pipeline['total_amount'] ?? 0);
        $count = $pipeline['count'] ?? 0;
        $stages = $pipeline['by_stage'] ?? [];

        $lines = ["Here's your current pipeline summary:"];
        $lines[] = "You have **{$count} open opportunities** worth **\${$total}** across your portfolio.";

        if (! empty($stages)) {
            $lines[] = '';
            $lines[] = 'Breakdown by stage:';
            foreach ($stages as $stage) {
                $stageAmount = number_format($stage['amount']);
                $lines[] = "- **{$stage['stage']}**: {$stage['count']} deals (\${$stageAmount})";
            }
        }

        if ($count === 0) {
            $lines[] = '';
            $lines[] = 'It looks like there are no open opportunities in your portfolio right now. This might be a good time to work with your partners on pipeline generation.';
        }

        return implode("\n", $lines);
    }

    protected function buildHealthResponse(array $context): string
    {
        $scores = $context['health_scores'] ?? [];
        $partnerCount = $context['partner_count'] ?? 0;

        $lines = ["Here's a health overview for your portfolio:"];

        if (empty($scores)) {
            $lines[] = 'No health snapshots are available yet. Health scores are computed daily based on scorecard results, pipeline trends, goal attainment, enablement activity, contact freshness, and delivery satisfaction.';

            return implode("\n", $lines);
        }

        $riskCounts = ['healthy' => 0, 'watch' => 0, 'at_risk' => 0, 'critical' => 0];
        foreach ($scores as $s) {
            $riskCounts[$s['risk_level']] = ($riskCounts[$s['risk_level']] ?? 0) + 1;
        }

        $lines[] = "Out of **{$partnerCount} partners**, the latest snapshots show:";
        if ($riskCounts['critical'] > 0) {
            $lines[] = "- **{$riskCounts['critical']}** critical";
        }
        if ($riskCounts['at_risk'] > 0) {
            $lines[] = "- **{$riskCounts['at_risk']}** at risk";
        }
        if ($riskCounts['watch'] > 0) {
            $lines[] = "- **{$riskCounts['watch']}** watch";
        }
        if ($riskCounts['healthy'] > 0) {
            $lines[] = "- **{$riskCounts['healthy']}** healthy";
        }

        $atRisk = array_filter($scores, fn ($s) => in_array($s['risk_level'], ['critical', 'at_risk']));
        if (! empty($atRisk)) {
            $lines[] = '';
            $lines[] = 'Partners needing attention:';
            foreach (array_slice($atRisk, 0, 5) as $s) {
                $signals = ! empty($s['signals']) ? ' — '.implode('; ', array_slice($s['signals'], 0, 2)) : '';
                $lines[] = "- **{$s['partner']}** (score: {$s['score']}, {$s['risk_level']}){$signals}";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildGoalsResponse(array $context): string
    {
        $goalData = $context['goals'] ?? [];
        $fy = $goalData['fiscal_year'] ?? 'current year';
        $goals = $goalData['goals'] ?? [];

        $lines = ["Here's your goal progress for **{$fy}**:"];

        if (empty($goals)) {
            $lines[] = 'No goals have been assigned to your partners for this fiscal year yet.';

            return implode("\n", $lines);
        }

        $totalTarget = array_sum(array_column($goals, 'target'));
        $totalActual = array_sum(array_column($goals, 'actual'));
        $overallPct = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : 0;

        $lines[] = "Overall attainment: **{$overallPct}%** across ".count($goals).' goals.';

        $atRisk = array_filter($goals, fn ($g) => $g['attainment'] < 50);
        if (! empty($atRisk)) {
            $lines[] = '';
            $lines[] = 'Goals at risk (below 50% attainment):';
            foreach (array_slice($atRisk, 0, 5) as $g) {
                $pct = round($g['attainment'], 1);
                $lines[] = "- **{$g['partner']}** — {$g['name']}: {$pct}% of target";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildTasksResponse(array $context): string
    {
        $tasks = $context['upcoming_tasks'] ?? [];
        $overdue = $tasks['overdue'] ?? [];
        $upcoming = $tasks['upcoming'] ?? [];

        $lines = ["Here's your task summary:"];

        if (! empty($overdue)) {
            $lines[] = '';
            $lines[] = '**Overdue tasks** ('.count($overdue).'):';
            foreach (array_slice($overdue, 0, 10) as $t) {
                $priority = $t['priority'] ? " [{$t['priority']}]" : '';
                $lines[] = "- **{$t['partner']}** — {$t['title']} (due {$t['due_date']}){$priority}";
            }
        } else {
            $lines[] = 'No overdue tasks — great job staying on top of things!';
        }

        if (! empty($upcoming)) {
            $lines[] = '';
            $lines[] = '**Upcoming tasks** (next 30 days, '.count($upcoming).'):';
            foreach (array_slice($upcoming, 0, 10) as $t) {
                $lines[] = "- **{$t['partner']}** — {$t['title']} (due {$t['due_date']})";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildCertificationsResponse(array $context): string
    {
        $certs = $context['certifications_summary'] ?? [];
        $active = $certs['active'] ?? 0;
        $expiring = $certs['expiring_soon'] ?? 0;
        $byType = $certs['by_type'] ?? [];

        $lines = ["Here's your certification summary:"];
        $lines[] = "Your partners hold **{$active} active certifications**.";

        if ($expiring > 0) {
            $lines[] = "**{$expiring}** are expiring within the next 60 days and may need renewal.";
        }

        if (! empty($byType)) {
            $lines[] = '';
            $lines[] = 'By type:';
            foreach ($byType as $type) {
                $lines[] = "- **{$type['type']}**: {$type['count']}";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildOverviewResponse(array $context): string
    {
        $name = explode(' ', $context['user_name'] ?? 'there')[0];
        $partnerCount = $context['partner_count'] ?? 0;
        $pipeline = $context['pipeline_summary'] ?? [];
        $pipelineTotal = number_format($pipeline['total_amount'] ?? 0);
        $pipelineCount = $pipeline['count'] ?? 0;

        $lines = ["Here's a quick overview of your portfolio, {$name}:"];
        $lines[] = '';
        $lines[] = "- **{$partnerCount}** partners in your portfolio";
        $lines[] = "- **{$pipelineCount}** open opportunities worth **\${$pipelineTotal}**";

        $scores = $context['health_scores'] ?? [];
        if (! empty($scores)) {
            $critical = count(array_filter($scores, fn ($s) => $s['risk_level'] === 'critical'));
            $atRisk = count(array_filter($scores, fn ($s) => $s['risk_level'] === 'at_risk'));
            if ($critical + $atRisk > 0) {
                $lines[] = '- **'.($critical + $atRisk).'** partners need attention (health score below 50)';
            }
        }

        $tasks = $context['upcoming_tasks'] ?? [];
        $overdueCount = count($tasks['overdue'] ?? []);
        if ($overdueCount > 0) {
            $lines[] = "- **{$overdueCount}** overdue tasks";
        }

        $certs = $context['certifications_summary'] ?? [];
        if (($certs['expiring_soon'] ?? 0) > 0) {
            $lines[] = "- **{$certs['expiring_soon']}** certifications expiring soon";
        }

        $lines[] = '';
        $lines[] = 'Ask me about **pipeline**, **health scores**, **goals**, **tasks**, or **certifications** for more details.';

        return implode("\n", $lines);
    }
}
