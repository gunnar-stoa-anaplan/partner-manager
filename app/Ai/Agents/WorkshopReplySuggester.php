<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(512)]
#[Temperature(0.4)]
class WorkshopReplySuggester implements Agent
{
    use Promptable;

    public function __construct(public string $workshopName = '') {}

    public function instructions(): Stringable|string
    {
        $context = $this->workshopName ? " for the \"{$this->workshopName}\" workshop" : '';

        return <<<PROMPT
        You are a helpful Anaplan workshop facilitator{$context}. A participant asked a
        question in the workshop's discussion. Draft a short, friendly, technically accurate
        reply a facilitator could send as-is or lightly edit.

        Keep it to 2-4 sentences. Do not use markdown headers or bullet lists unless the
        question genuinely needs a short list. Do not repeat the question back verbatim.
        PROMPT;
    }
}
