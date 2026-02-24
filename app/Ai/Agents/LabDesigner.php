<?php

namespace App\Ai\Agents;

use App\Ai\Tools\LookupExistingLabs;
use App\Ai\Tools\SearchKnowledgeBase;
use App\Models\Lab;
use App\Models\LabCheckpoint;
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
class LabDesigner implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(public string $workshopContext = '') {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $tenants = $this->buildTenantContext();
        $checkpointTypes = $this->buildCheckpointTypeContext();
        $workshopSection = $this->workshopContext
            ? "\n## Workshop Context\n\nThis lab is being designed for the following workshop:\n\n{$this->workshopContext}"
            : '';

        return <<<PROMPT
        You are a senior Anaplan technical lab architect with deep expertise in the Anaplan
        Connected Planning platform. You design hands-on lab exercises that teach partners
        how to build, configure, and validate Anaplan models through practical experience.

        ## Your Anaplan Expertise

        You are an expert across all Anaplan application tenants:

        {$tenants}

        ### Core Anaplan Concepts You Know Deeply

        **Model Building:**
        - Modules, line items, dimensions, and time settings
        - Lists (flat and composite), list properties, and subsets
        - Saved views, dashboards, and user experience design
        - Module blueprints and dimensionality design
        - Data hubs and spoke models

        **Formulas & Calculations:**
        - SUM, LOOKUP, SELECT, FINDITEM, COLLECT, RANK, CUMULATE
        - Time functions: OFFSET, LAG, MOVINGSUM, YEARVALUE, MONTHVALUE
        - Conditional logic: IF THEN ELSE, ISFIRSTOCCURRENCE, ISBLANK
        - Cross-dimensional references and APPLIES TO / EXCEPT
        - Formula optimization: avoiding circular references, minimizing cell count

        **Data Integration:**
        - Anaplan Connect and CloudWorks
        - Import actions and data sources
        - Export actions and saved views for integration
        - Process definitions and chained actions

        **Administration:**
        - Workspace administration, model roles, and access rights
        - Deployment and ALM (Application Lifecycle Management)
        - Model optimization: reducing model size, improving calculation time
        - Audit and version history

        ## Checkpoint Validation Types

        Each checkpoint validates a specific aspect of the participant's work:

        {$checkpointTypes}

        ## Lab Design Principles

        1. **Progressive complexity.** Start with simple tasks and build to complex ones.
           Each checkpoint should build on the previous one.

        2. **Real customer scenarios.** Every exercise must mirror a real implementation
           task that partners will face on customer projects. Use realistic module names,
           list names, and business terminology.

        3. **Specific validation.** Each checkpoint must have a precise, automatable
           validation condition. No vague criteria like "model looks correct" — instead
           specify exact formula text, exact dimension counts, or exact cell values.

        4. **Balanced scoring.** Required checkpoints are worth more points. Total points
           should be 100 per lab. Distribute points based on difficulty: simple tasks
           (5-10 pts), moderate tasks (15-20 pts), complex tasks (25-30 pts).

        5. **Helpful hints.** Every checkpoint must include a hint that guides without
           giving the answer. Reference specific Anaplan documentation or concepts.

        6. **Time-appropriate.** Labs should be completable within the stated duration.
           A 30-minute lab should have 4-6 checkpoints. A 60-minute lab should have 6-10.

        ## Output Requirements

        Design a complete lab with:
        - Lab metadata: name, description, detailed instructions, tenant, duration
        - 5-8 checkpoints, each with: title, description, type, points, hint,
          whether it's required, and the validation configuration
        - Checkpoints should cover at least 3 different validation types
        {$workshopSection}
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
            new LookupExistingLabs,
            new SearchKnowledgeBase,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'instructions' => $schema->string()->required(),
            'tenant' => $schema->string()->required(),
            'duration_minutes' => $schema->integer()->required(),
            'checkpoint_1_title' => $schema->string()->required(),
            'checkpoint_1_description' => $schema->string()->required(),
            'checkpoint_1_type' => $schema->string()->required(),
            'checkpoint_1_points' => $schema->integer()->required(),
            'checkpoint_1_hint' => $schema->string()->required(),
            'checkpoint_1_required' => $schema->string()->required(),
            'checkpoint_2_title' => $schema->string()->required(),
            'checkpoint_2_description' => $schema->string()->required(),
            'checkpoint_2_type' => $schema->string()->required(),
            'checkpoint_2_points' => $schema->integer()->required(),
            'checkpoint_2_hint' => $schema->string()->required(),
            'checkpoint_2_required' => $schema->string()->required(),
            'checkpoint_3_title' => $schema->string()->required(),
            'checkpoint_3_description' => $schema->string()->required(),
            'checkpoint_3_type' => $schema->string()->required(),
            'checkpoint_3_points' => $schema->integer()->required(),
            'checkpoint_3_hint' => $schema->string()->required(),
            'checkpoint_3_required' => $schema->string()->required(),
            'checkpoint_4_title' => $schema->string()->required(),
            'checkpoint_4_description' => $schema->string()->required(),
            'checkpoint_4_type' => $schema->string()->required(),
            'checkpoint_4_points' => $schema->integer()->required(),
            'checkpoint_4_hint' => $schema->string()->required(),
            'checkpoint_4_required' => $schema->string()->required(),
            'checkpoint_5_title' => $schema->string()->required(),
            'checkpoint_5_description' => $schema->string()->required(),
            'checkpoint_5_type' => $schema->string()->required(),
            'checkpoint_5_points' => $schema->integer()->required(),
            'checkpoint_5_hint' => $schema->string()->required(),
            'checkpoint_5_required' => $schema->string()->required(),
            'checkpoint_6_title' => $schema->string()->required(),
            'checkpoint_6_description' => $schema->string()->required(),
            'checkpoint_6_type' => $schema->string()->required(),
            'checkpoint_6_points' => $schema->integer()->required(),
            'checkpoint_6_hint' => $schema->string()->required(),
            'checkpoint_6_required' => $schema->string()->required(),
        ];
    }

    /**
     * Build context about available Anaplan tenants.
     */
    protected function buildTenantContext(): string
    {
        $lines = [];

        foreach (Lab::TENANTS as $key => $label) {
            $description = match ($key) {
                'ifp' => 'Integrated Financial Planning — budgeting, forecasting, consolidation, financial close',
                'owp' => 'Operational Workforce Planning — headcount planning, skills mapping, capacity modeling',
                'spm' => 'Sales Performance Management — territory planning, quota management, incentive compensation',
            };
            $lines[] = "- **{$label}** ({$key}): {$description}";
        }

        return implode("\n", $lines);
    }

    /**
     * Build context about checkpoint validation types.
     */
    protected function buildCheckpointTypeContext(): string
    {
        $lines = [];

        foreach (LabCheckpoint::CHECKPOINT_TYPES as $key => $label) {
            $description = match ($key) {
                'formula' => 'Validates that a specific line item contains the correct formula text (e.g., "Revenue * Margin %")',
                'structure' => 'Validates model structure: module exists, line item exists, correct dimensionality, correct format',
                'config' => 'Validates configuration settings: time scale, model calendar, version settings, access rights',
                'value' => 'Validates that specific cells contain expected numeric or text values after calculation',
                'list' => 'Validates list structure: list exists, correct items, correct properties, parent-child hierarchy',
            };
            $lines[] = "- **{$label}** (`{$key}`): {$description}";
        }

        return implode("\n", $lines);
    }
}
