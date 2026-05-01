<?php

namespace App\Services;

use App\Models\Journey;
use App\Models\JourneyAsset;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JourneyAssetInitializer
{
    public function __construct(
        protected string $templatesPath = ''
    ) {
        if ($this->templatesPath === '') {
            $this->templatesPath = resource_path('journey-templates');
        }
    }

    /**
     * Seed journey_assets rows from the persona templates for the given journey.
     *
     * @param  array<string, string>  $productContext  Substitutions for template placeholders
     *                                                 (product_id, product_name, product_short,
     *                                                 adjacent_product, adjacent_journey_id, terminal_outcome).
     * @return int Count of journey_assets rows created.
     */
    public function initialize(Journey $journey, array $productContext): int
    {
        $persona = $journey->persona;
        $personaDir = $this->personaDirectory($persona);
        $created = 0;

        DB::transaction(function () use ($journey, $persona, $personaDir, $productContext, &$created) {
            for ($stage = 1; $stage <= 5; $stage++) {
                $template = $this->loadStageTemplate($personaDir, $persona, $stage);
                $assets = $this->extractAssets($template, $stage, $productContext);
                foreach ($assets as $row) {
                    $created += $this->upsertJourneyAsset($journey, $row);
                }
            }
        });

        return $created;
    }

    protected function personaDirectory(string $persona): string
    {
        $map = [
            'delivery' => 'delivery',
            'sales' => 'sales',
            'pre_sales' => 'pre-sales',
            'pre-sales' => 'pre-sales',
        ];
        $dir = $map[strtolower($persona)] ?? null;
        if ($dir === null) {
            throw new RuntimeException("Unknown persona '{$persona}' for template lookup");
        }
        $path = $this->templatesPath.DIRECTORY_SEPARATOR.$dir;
        if (! is_dir($path)) {
            throw new RuntimeException("Template directory not found: {$path}");
        }

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadStageTemplate(string $personaDir, string $persona, int $stage): array
    {
        $personaPrefix = strtolower($persona) === 'pre-sales' || strtolower($persona) === 'pre_sales'
            ? 'pre-sales'
            : strtolower($persona);
        $file = $personaDir.DIRECTORY_SEPARATOR."{$personaPrefix}-journey-template-stage{$stage}.json";
        if (! is_file($file)) {
            throw new RuntimeException("Template file not found: {$file}");
        }
        $decoded = json_decode((string) file_get_contents($file), true);
        if (! is_array($decoded)) {
            throw new RuntimeException("Failed to decode template: {$file}");
        }

        return $decoded;
    }

    /**
     * Pull the canonical asset list for a stage:
     *  - Direct task assets (tasks[].asset) when completion mechanism is content-based
     *  - Per-product authoring obligations (assets / items) for rubrics, templates, processes
     *
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $productContext
     * @return list<array<string, mixed>>
     */
    protected function extractAssets(array $template, int $stage, array $productContext): array
    {
        $rows = [];
        $seen = [];

        $tasks = $template['tasks'] ?? [];
        foreach ($tasks as $task) {
            if (! is_array($task)) {
                continue;
            }
            $mechanism = $task['completion_mechanism'] ?? null;
            $skipMechanisms = ['external_system', 'prereq_journey_check'];
            if ($mechanism !== null && in_array($mechanism, $skipMechanisms, true)) {
                continue;
            }
            $assetName = $task['asset'] ?? null;
            if (! is_string($assetName) || $assetName === '') {
                continue;
            }
            $resolvedName = $this->substitutePlaceholders($assetName, $productContext);
            $taskId = $this->substitutePlaceholders((string) ($task['task_id'] ?? ''), $productContext);
            $key = $stage.'|'.$taskId.'|'.$resolvedName;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = [
                'stage_number' => $stage,
                'task_id' => $taskId,
                'asset_name' => $resolvedName,
                'asset_kind' => $task['source_system'] ?? null,
                'description' => isset($task['description'])
                    ? $this->substitutePlaceholders((string) $task['description'], $productContext)
                    : null,
                'required' => (bool) ($task['required'] ?? true),
            ];
        }

        $obligations = $template['_per_product_authoring_obligations'] ?? null;
        if (is_array($obligations)) {
            $list = $obligations['assets'] ?? $obligations['items'] ?? [];
            foreach ($list as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $name = $entry['asset'] ?? $entry['item'] ?? null;
                if (! is_string($name) || $name === '') {
                    continue;
                }
                $resolvedName = $this->substitutePlaceholders($name, $productContext);
                $taskId = '_authoring_obligation';
                $key = $stage.'|'.$taskId.'|'.$resolvedName;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $rows[] = [
                    'stage_number' => $stage,
                    'task_id' => $taskId,
                    'asset_name' => $resolvedName,
                    'asset_kind' => $entry['block'] ?? 'authoring_obligation',
                    'description' => isset($entry['note']) && is_string($entry['note'])
                        ? $this->substitutePlaceholders($entry['note'], $productContext)
                        : null,
                    'required' => true,
                ];
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, string>  $productContext
     */
    protected function substitutePlaceholders(string $text, array $productContext): string
    {
        $map = [
            '{{PRODUCT_ID}}' => $productContext['product_id'] ?? '',
            '{{PRODUCT_NAME}}' => $productContext['product_name'] ?? '',
            '{{PRODUCT_SHORT}}' => $productContext['product_short'] ?? ($productContext['product_id'] ?? ''),
            '{{ADJACENT_PRODUCT}}' => $productContext['adjacent_product'] ?? '',
            '{{ADJACENT_JOURNEY_ID}}' => $productContext['adjacent_journey_id'] ?? '',
            '{{TERMINAL_OUTCOME}}' => $productContext['terminal_outcome'] ?? '',
            '{{PRODUCT_VERSION}}' => $productContext['product_version'] ?? '',
        ];

        return strtr($text, $map);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function upsertJourneyAsset(Journey $journey, array $row): int
    {
        $existing = JourneyAsset::query()
            ->where('journey_id', $journey->id)
            ->where('stage_number', $row['stage_number'])
            ->where('task_id', $row['task_id'])
            ->where('asset_name', $row['asset_name'])
            ->first();
        if ($existing !== null) {
            return 0;
        }
        JourneyAsset::create([
            'journey_id' => $journey->id,
            'stage_number' => $row['stage_number'],
            'task_id' => $row['task_id'],
            'asset_name' => $row['asset_name'],
            'asset_kind' => $row['asset_kind'] ?? null,
            'description' => $row['description'] ?? null,
            'required' => $row['required'] ?? true,
            'status' => 'missing',
        ]);

        return 1;
    }
}
