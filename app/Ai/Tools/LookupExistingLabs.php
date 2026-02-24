<?php

namespace App\Ai\Tools;

use App\Models\Lab;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupExistingLabs implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search existing labs by tenant (IFP, OWP, SPM) to see what hands-on exercises '
            .'already exist. Returns lab names, descriptions, checkpoint counts, types, and '
            .'total points. Use this to avoid duplicating existing labs and to understand '
            .'the patterns used in checkpoint design.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Lab::query()->withCount('checkpoints');

        if ($tenant = $request['tenant'] ?? null) {
            $query->where('tenant', strtolower($tenant));
        }

        $labs = $query->orderBy('tenant')->orderBy('name')->get();

        if ($labs->isEmpty()) {
            return 'No labs found matching the criteria.';
        }

        $lines = ["Found {$labs->count()} labs:"];

        foreach ($labs as $lab) {
            $lines[] = '';
            $lines[] = "- {$lab->name} [{$lab->tenant}]";
            $lines[] = "  Status: {$lab->status} | Duration: {$lab->duration_minutes} min | Checkpoints: {$lab->checkpoints_count}";

            if ($lab->description) {
                $lines[] = "  Description: {$lab->description}";
            }

            if ($lab->checkpoints_count > 0) {
                $checkpoints = $lab->checkpoints;
                $types = $checkpoints->pluck('checkpoint_type')->countBy();
                $typesSummary = $types->map(fn ($count, $type) => "{$type}: {$count}")->implode(', ');
                $lines[] = "  Checkpoint types: {$typesSummary}";
                $lines[] = "  Total points: {$checkpoints->sum('points')}";
            }
        }

        $lines[] = '';
        $lines[] = 'Available tenants: ifp, owp, spm';
        $lines[] = 'Checkpoint types: formula (formula validation), structure (model structure), config (configuration), value (cell values), list (list membership)';

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tenant' => $schema->string()
                ->description('Filter by Anaplan tenant: ifp, owp, or spm'),
        ];
    }
}
