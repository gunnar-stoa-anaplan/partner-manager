<?php

namespace App\Ai\Agents;

use App\Models\Partner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(1024)]
#[Temperature(0.4)]
class PartnerAdvisor implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public Partner $partner) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $context = $this->buildPartnerContext();

        return <<<PROMPT
        You are a partner success advisor for a channel partner management platform.
        Given a partner's current metrics and activity data, provide a brief health
        assessment with specific, actionable recommendations.

        Be direct and practical. Focus on the 2-3 most impactful actions the partner
        success manager should take next. Reference specific metrics when explaining
        your reasoning.

        Here is the partner's current data:
        {$context}
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'health_score' => $schema->integer()->min(1)->max(100)->required(),
            'risk_level' => $schema->string()->required(),
            'summary' => $schema->string()->required(),
            'top_recommendation' => $schema->string()->required(),
            'second_recommendation' => $schema->string()->required(),
            'third_recommendation' => $schema->string()->required(),
            'key_strength' => $schema->string()->required(),
            'key_risk' => $schema->string()->required(),
        ];
    }

    /**
     * Build a text summary of the partner's current state.
     */
    protected function buildPartnerContext(): string
    {
        $partner = $this->partner;
        $lines = [];

        $lines[] = "Partner: {$partner->name}";
        $lines[] = "Industry: {$partner->industry}";
        $lines[] = "Geography: {$partner->geography} / {$partner->region}";

        // Scorecard
        $scorecard = $partner->scorecards()->latest('scored_at')->first();
        if ($scorecard) {
            $lines[] = '';
            $lines[] = '--- Scorecard ---';
            $lines[] = "Overall: {$scorecard->overall_score}/100 (Tier: {$scorecard->tier})";
            foreach ($scorecard->getDimensionScores() as $dim => $score) {
                $lines[] = "  {$dim}: {$score}/100";
            }
        }

        // Health
        $health = $partner->healthSnapshots()->latest('snapshot_date')->first();
        if ($health) {
            $lines[] = '';
            $lines[] = '--- Health ---';
            $lines[] = "Score: {$health->score}/100, Risk: {$health->risk_level}";
            if ($health->signals) {
                foreach ($health->signals as $signal) {
                    $label = $signal['label'] ?? $signal['type'] ?? 'signal';
                    $lines[] = "  Signal: {$label}";
                }
            }
        }

        // Activity counts
        $lines[] = '';
        $lines[] = '--- Activity ---';
        $lines[] = "Projects: {$partner->projects->count()}";
        $lines[] = "Contacts: {$partner->contacts->count()}";
        $lines[] = "Open opportunities: {$partner->opportunities()->where('stage', '!=', 'Closed Won')->where('stage', '!=', 'Closed Lost')->count()}";
        $lines[] = "Certifications: {$partner->certifications()->count()}";
        $lines[] = "Deal registrations (last 90d): {$partner->dealRegistrations()->where('created_at', '>=', now()->subDays(90))->count()}";
        $lines[] = "Enablement registrations: {$partner->enablementRegistrations()->count()}";

        // Goals
        $goalCount = $partner->goals()->count();
        $lines[] = "Goals set: {$goalCount}";

        return implode("\n", $lines);
    }
}
