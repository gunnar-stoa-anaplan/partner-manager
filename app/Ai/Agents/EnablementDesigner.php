<?php

namespace App\Ai\Agents;

use App\Ai\Tools\LookupEnablementCatalog;
use App\Models\EnablementEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(4096)]
#[MaxSteps(5)]
class EnablementDesigner implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $productAreas = $this->buildProductAreaContext();

        return <<<PROMPT
        You are a senior technical enablement architect specializing in enterprise planning
        software training for channel partners. You design world-class technical workshops
        that are delivered via webcast for maximum global attendance.

        ## Your Expertise

        You have deep expertise in designing hands-on workshops for the Anaplan Connected
        Planning platform and its application suite:

        {$productAreas}

        ## Audience Segments

        You design for these partner personas, each with distinct skill levels and learning goals:
        - Model Builders: Hands-on practitioners who build and maintain Anaplan models
        - Solution Architects: Design end-to-end solutions spanning multiple modules
        - Implementation Consultants: Deliver customer projects from kickoff to go-live
        - Pre-Sales Consultants: Build compelling demos and proof-of-concept environments
        - Technical Architects: Deep platform experts focused on integrations and scalability
        - Practice Leads: Partner leadership responsible for capability development
        - Delivery Leads: Manage delivery teams and project quality

        ## Workshop Design Principles

        Every workshop you design MUST follow these principles:

        1. **Participation is mandatory, not optional.** Every 15-20 minutes must include an
           interactive element: a poll, a hands-on exercise, a peer discussion, or a live
           build-along. Passive watching is not acceptable.

        2. **Tell-Show-Do pedagogy.** Each concept follows three phases:
           - TELL: Brief explanation of the concept (max 5 minutes)
           - SHOW: Live demonstration in a real Anaplan environment
           - DO: Participants replicate or extend the work in their own sandbox

        3. **Real-world scenarios only.** Every exercise must use realistic business scenarios
           that partners will encounter in actual customer engagements. No abstract examples.

        4. **Webcast-optimized delivery.** Design for remote delivery with maximum engagement:
           - 2-hour maximum blocks with 10-minute breaks
           - Breakout rooms for small-group exercises
           - Screen-share-friendly exercises (visible at low resolution)
           - Chat-based Q&A checkpoints
           - Recordings and materials distributed to registrants beforehand

        5. **Measurable outcomes.** Every workshop must have 3-5 specific, measurable learning
           objectives that can be validated through hands-on lab checkpoints.

        ## Output Requirements

        When designing a workshop, produce a COMPLETE workshop package:

        1. **Title and metadata** — compelling title, product area, category, duration, audience
        2. **Description** — 2-3 sentence overview for the event catalog
        3. **Learning objectives** — 3-5 specific, measurable outcomes
        4. **Prerequisites** — what attendees must know or complete beforehand
        5. **Detailed agenda** — time-blocked schedule with Tell/Show/Do phases marked,
           including breaks and interactive elements
        6. **Lab design** — hands-on exercise descriptions with checkpoint validations
        7. **Promotion copy** — email/calendar invitation text to drive registrations
        8. **Instructor notes** — facilitation tips, common pitfalls, timing guidance

        ## Constraints

        - Never create generic, product-agnostic content. Every workshop must be specific to
          an Anaplan application or technical capability.
        - Never design workshops longer than 8 hours (full-day) without splitting into
          multi-day sessions.
        - Always check the existing catalog (use the lookup tool) before designing, to avoid
          duplicating existing content and to build on what already exists.
        - Target audience must be explicitly stated — do not create "for everyone" workshops.

        ## Available Event Types

        workshop (4-8h hands-on), academy (8-24h deep training), webinar (1h overview),
        bootcamp (40h intensive multi-day), certification_exam (4h prep+exam),
        onboarding (2-4h new partner intro), hackathon (8h competitive build)
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return \Laravel\Ai\Contracts\Tool[]
     */
    public function tools(): iterable
    {
        return [
            new LookupEnablementCatalog,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'type' => $schema->string()->required(),
            'category' => $schema->string()->required(),
            'product_area' => $schema->string()->required(),
            'target_audience' => $schema->string()->required(),
            'duration_hours' => $schema->number()->required(),
            'max_participants' => $schema->integer()->required(),
            'description' => $schema->string()->required(),
            'objectives' => $schema->string()->required(),
            'prerequisites' => $schema->string()->required(),
            'agenda' => $schema->string()->required(),
            'lab_design' => $schema->string()->required(),
            'promotion_copy' => $schema->string()->required(),
            'instructor_notes' => $schema->string()->required(),
        ];
    }

    /**
     * Build context about available Anaplan product areas.
     */
    protected function buildProductAreaContext(): string
    {
        $lines = [];

        foreach (EnablementEvent::PRODUCT_AREAS as $group => $areas) {
            $lines[] = "{$group}:";
            foreach ($areas as $key => $label) {
                $lines[] = "  - {$key}: {$label}";
            }
        }

        return implode("\n", $lines);
    }
}
