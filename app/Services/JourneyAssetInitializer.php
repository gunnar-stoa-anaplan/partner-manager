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
    /**
     * Seed journey_assets rows from the persona templates for the given journey.
     *
     * @param  array<string, string>  $productContext  Substitutions for template placeholders.
     * @param  string|null  $templateSlug  Override the persona-based directory lookup with a
     *                                     specific template slug (e.g. 'anaplan-overview' for
     *                                     foundational journeys). When set, looks for templates
     *                                     in resources/journey-templates/{slug}/.
     */
    public function initialize(Journey $journey, array $productContext, ?string $templateSlug = null): int
    {
        $prefix = $templateSlug !== null ? $templateSlug : $journey->persona;
        $templateDir = $templateSlug !== null
            ? $this->customTemplateDirectory($templateSlug)
            : $this->personaDirectory($journey->persona);
        $created = 0;

        DB::transaction(function () use ($journey, $prefix, $templateDir, $productContext, &$created) {
            for ($stage = 1; $stage <= 5; $stage++) {
                $template = $this->loadStageTemplate($templateDir, $prefix, $stage);
                if ($template === null) {
                    continue;
                }
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

    protected function customTemplateDirectory(string $templateSlug): string
    {
        $path = $this->templatesPath.DIRECTORY_SEPARATOR.$templateSlug;
        if (! is_dir($path)) {
            throw new RuntimeException("Template directory not found: {$path}");
        }

        return $path;
    }

    /**
     * Load a stage template from disk. Returns null if the stage file does not
     * exist (foundational journeys only have stages 1-3, for example).
     *
     * Filename conventions tried in order:
     *  - {prefix}-journey-template-stage{N}.json  (per-app convention)
     *  - {prefix}-stage{N}.json                   (foundational convention)
     *  - stage{N}.json                            (generic fallback)
     *
     * @return array<string, mixed>|null
     */
    protected function loadStageTemplate(string $personaDir, string $prefix, int $stage): ?array
    {
        $prefixSlug = strtolower($prefix) === 'pre_sales' ? 'pre-sales' : strtolower($prefix);
        $candidates = [
            $personaDir.DIRECTORY_SEPARATOR."{$prefixSlug}-journey-template-stage{$stage}.json",
            $personaDir.DIRECTORY_SEPARATOR."{$prefixSlug}-stage{$stage}.json",
            $personaDir.DIRECTORY_SEPARATOR."stage{$stage}.json",
        ];
        foreach ($candidates as $file) {
            if (! is_file($file)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($file), true);
            if (! is_array($decoded)) {
                throw new RuntimeException("Failed to decode template: {$file}");
            }

            return $decoded;
        }

        return null;
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
            $key = $stage.'|'.$resolvedName;
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
                $key = $stage.'|'.$resolvedName;
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
