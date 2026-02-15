<?php

namespace App\Services;

use App\Models\Lab;
use App\Models\LabCheckpoint;
use App\Models\LabCheckpointResult;
use App\Models\LabParticipant;
use Illuminate\Support\Facades\Log;

class LabValidationService
{
    /**
     * Get an AnaplanApiService instance for a specific lab's tenant.
     */
    private function apiForLab(Lab $lab): AnaplanApiService
    {
        return new AnaplanApiService($lab->tenantConfigKey());
    }

    /**
     * Validate a single checkpoint for a participant.
     */
    public function validateCheckpoint(LabParticipant $participant, LabCheckpoint $checkpoint): LabCheckpointResult
    {
        $attemptNumber = $participant->checkpointResults()
            ->where('lab_checkpoint_id', $checkpoint->id)
            ->max('attempt_number') + 1;

        $modelId = $participant->assignedModel?->anaplan_id;

        if (! $modelId) {
            return $this->createResult($participant, $checkpoint, $attemptNumber, false, 'No Anaplan model assigned to this participant.');
        }

        $lab = $participant->lab;
        $api = $this->apiForLab($lab);

        if (! $api->isConfigured()) {
            return $this->createResult($participant, $checkpoint, $attemptNumber, false, 'Anaplan credentials not configured for tenant "'.strtoupper($lab->tenant ?? 'owp').'".');
        }

        $config = $checkpoint->validation_config;

        try {
            $result = match ($checkpoint->checkpoint_type) {
                'formula' => $this->validateFormula($api, $modelId, $config),
                'structure' => $this->validateStructure($api, $modelId, $config),
                'list' => $this->validateList($api, $modelId, $config),
                'value' => $this->validateValue($api, $modelId, $config),
                'config' => $this->validateConfig($api, $modelId, $config),
            };
        } catch (\Throwable $e) {
            Log::error('Lab checkpoint validation error', [
                'checkpoint_id' => $checkpoint->id,
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);

            return $this->createResult($participant, $checkpoint, $attemptNumber, false, 'Validation error: unable to reach Anaplan API.', [
                'exception' => $e->getMessage(),
            ]);
        }

        $checkpointResult = $this->createResult(
            $participant,
            $checkpoint,
            $attemptNumber,
            $result['passed'],
            $result['error_message'],
            $result['details'] ?? null,
        );

        $participant->updateProgress();

        return $checkpointResult;
    }

    /**
     * Capture the golden model snapshot for a lab.
     *
     * @return array<string, mixed>
     */
    public function captureGoldenSnapshot(Lab $lab): array
    {
        $api = $this->apiForLab($lab);
        $goldenModel = $lab->goldenModel;
        $modelId = $goldenModel->anaplan_id;
        $workspaceId = $goldenModel->workspace?->anaplan_id;

        $snapshot = [
            'captured_at' => now()->toIso8601String(),
            'tenant' => $lab->tenant,
            'model' => [
                'anaplan_id' => $modelId,
                'name' => $goldenModel->name,
            ],
            'modules' => [],
            'lists' => [],
        ];

        // Fetch modules
        $modules = $workspaceId
            ? $api->fetchModules($workspaceId, $modelId)
            : null;

        if ($modules) {
            foreach ($modules as $module) {
                $moduleData = [
                    'anaplan_id' => $module['id'],
                    'name' => $module['name'],
                    'line_items' => [],
                ];

                // Fetch line items with full detail
                $lineItems = $api->fetchLineItems($modelId, $module['id'], true);
                if ($lineItems) {
                    foreach ($lineItems as $li) {
                        $moduleData['line_items'][] = [
                            'anaplan_id' => $li['id'],
                            'name' => $li['name'],
                            'format' => $li['format'] ?? null,
                            'formula' => $li['formula'] ?? null,
                            'formulaScope' => $li['formulaScope'] ?? null,
                            'summary' => $li['summary'] ?? null,
                            'cellCount' => $li['cellCount'] ?? null,
                        ];
                    }
                }

                $snapshot['modules'][] = $moduleData;
            }
        }

        // Fetch lists
        $lists = $workspaceId
            ? $api->fetchLists($workspaceId, $modelId)
            : null;

        if ($lists) {
            foreach ($lists as $list) {
                $listData = [
                    'anaplan_id' => $list['id'],
                    'name' => $list['name'],
                    'item_count' => $list['permittedItems'] ?? null,
                    'items' => [],
                ];

                // Fetch list items
                $items = $api->fetchListItems($modelId, $list['id']);
                if ($items) {
                    foreach ($items as $item) {
                        $listData['items'][] = [
                            'name' => $item['name'] ?? $item['code'] ?? null,
                            'code' => $item['code'] ?? null,
                        ];
                    }
                }

                $snapshot['lists'][] = $listData;
            }
        }

        return $snapshot;
    }

    /**
     * Validate formula: compare line item formula against expected.
     *
     * @return array{passed: bool, error_message: string|null, details: array|null}
     */
    private function validateFormula(AnaplanApiService $api, string $modelId, array $config): array
    {
        $moduleId = $config['module_anaplan_id'] ?? null;
        $lineItemId = $config['line_item_anaplan_id'] ?? null;
        $expectedFormula = $config['expected_formula'] ?? '';

        if (! $moduleId || ! $lineItemId) {
            return ['passed' => false, 'error_message' => 'Checkpoint configuration incomplete.', 'details' => null];
        }

        $lineItems = $api->fetchLineItems($modelId, $moduleId, true);
        if ($lineItems === null) {
            return ['passed' => false, 'error_message' => 'Unable to access model. Check API permissions.', 'details' => null];
        }

        $lineItem = collect($lineItems)->firstWhere('id', $lineItemId);
        if (! $lineItem) {
            // Try matching by name as fallback
            $lineItemName = $config['line_item_name'] ?? null;
            $lineItem = $lineItemName ? collect($lineItems)->firstWhere('name', $lineItemName) : null;
        }

        if (! $lineItem) {
            return [
                'passed' => false,
                'error_message' => 'Line item "'.($config['line_item_name'] ?? $lineItemId).'" not found in module.',
                'details' => ['expected_line_item' => $config['line_item_name'] ?? $lineItemId],
            ];
        }

        $actualFormula = $lineItem['formula'] ?? '';
        $passed = trim($actualFormula) === trim($expectedFormula);

        return [
            'passed' => $passed,
            'error_message' => $passed ? null : 'Formula does not match. Check your formula for "'.($config['line_item_name'] ?? 'this line item').'".',
            'details' => [
                'expected' => $expectedFormula,
                'actual' => $actualFormula,
            ],
        ];
    }

    /**
     * Validate structure: check that modules/line items exist.
     *
     * @return array{passed: bool, error_message: string|null, details: array|null}
     */
    private function validateStructure(AnaplanApiService $api, string $modelId, array $config): array
    {
        $type = $config['type'] ?? 'module';
        $name = $config['name'] ?? '';

        if ($type === 'module') {
            $expectedLineItems = $config['expected_line_items'] ?? [];
            $lineItems = null;
            $moduleId = $config['module_anaplan_id'] ?? null;

            if ($moduleId) {
                $lineItems = $api->fetchLineItems($modelId, $moduleId, false);
            }

            if ($lineItems === null && ! $moduleId) {
                return [
                    'passed' => false,
                    'error_message' => 'Module "'.$name.'" could not be verified. Module ID not configured.',
                    'details' => null,
                ];
            }

            if ($lineItems === null) {
                return [
                    'passed' => false,
                    'error_message' => 'Module "'.$name.'" not found or not accessible.',
                    'details' => null,
                ];
            }

            $actualNames = collect($lineItems)->pluck('name')->toArray();
            $missing = array_diff($expectedLineItems, $actualNames);

            $passed = empty($missing);

            return [
                'passed' => $passed,
                'error_message' => $passed ? null : 'Missing line items in "'.$name.'": '.implode(', ', $missing),
                'details' => [
                    'expected' => $expectedLineItems,
                    'actual' => $actualNames,
                    'missing' => array_values($missing),
                ],
            ];
        }

        return ['passed' => false, 'error_message' => 'Unknown structure type: '.$type, 'details' => null];
    }

    /**
     * Validate list: compare list items against expected.
     *
     * @return array{passed: bool, error_message: string|null, details: array|null}
     */
    private function validateList(AnaplanApiService $api, string $modelId, array $config): array
    {
        $listId = $config['list_anaplan_id'] ?? null;
        $expectedItems = $config['expected_items'] ?? [];

        if (! $listId) {
            return ['passed' => false, 'error_message' => 'List ID not configured.', 'details' => null];
        }

        $items = $api->fetchListItems($modelId, $listId);
        if ($items === null) {
            return ['passed' => false, 'error_message' => 'Unable to access list. Check API permissions.', 'details' => null];
        }

        $actualNames = collect($items)->pluck('name')->toArray();

        $missing = array_diff($expectedItems, $actualNames);
        $extra = array_diff($actualNames, $expectedItems);
        $passed = empty($missing);

        return [
            'passed' => $passed,
            'error_message' => $passed ? null : 'List "'.$config['list_name'].'" is missing items: '.implode(', ', $missing),
            'details' => [
                'expected' => $expectedItems,
                'actual' => $actualNames,
                'missing' => array_values($missing),
                'extra' => array_values($extra),
            ],
        ];
    }

    /**
     * Validate value: run export, compare cell values.
     *
     * @return array{passed: bool, error_message: string|null, details: array|null}
     */
    private function validateValue(AnaplanApiService $api, string $modelId, array $config): array
    {
        $exportId = $config['export_anaplan_id'] ?? null;
        $expectedValue = $config['expected_value'] ?? null;
        $tolerance = $config['tolerance'] ?? 0.01;

        if (! $exportId) {
            return ['passed' => false, 'error_message' => 'Export not configured for this checkpoint.', 'details' => null];
        }

        // Run the export
        $taskId = $api->runExport($modelId, $exportId);
        if (! $taskId) {
            return ['passed' => false, 'error_message' => 'Unable to run export. Check API permissions.', 'details' => null];
        }

        // Poll for completion (max 30 seconds)
        $maxAttempts = 15;
        $task = null;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $task = $api->getExportTaskStatus($modelId, $exportId, $taskId);
            if ($task && ($task['taskState'] ?? '') === 'COMPLETE') {
                break;
            }
            usleep(2_000_000); // 2 seconds
        }

        if (! $task || ($task['taskState'] ?? '') !== 'COMPLETE') {
            return ['passed' => false, 'error_message' => 'Export timed out. Please try again.', 'details' => null];
        }

        // Read the export data
        $csvData = $api->readExportFile($modelId, $exportId, 0);
        if (! $csvData) {
            return ['passed' => false, 'error_message' => 'Unable to read export data.', 'details' => null];
        }

        // Parse CSV and find the value
        $lines = explode("\n", trim($csvData));
        if (count($lines) < 2) {
            return ['passed' => false, 'error_message' => 'Export returned no data.', 'details' => ['csv_lines' => count($lines)]];
        }

        // Simple: take last column of second row as the value
        $dataRow = str_getcsv($lines[1]);
        $actualValue = (float) end($dataRow);

        $passed = abs($actualValue - (float) $expectedValue) <= $tolerance;

        return [
            'passed' => $passed,
            'error_message' => $passed ? null : 'Value does not match expected result for "'.($config['line_item_name'] ?? 'this checkpoint').'".',
            'details' => [
                'expected' => $expectedValue,
                'actual' => $actualValue,
                'tolerance' => $tolerance,
                'within_tolerance' => $passed,
            ],
        ];
    }

    /**
     * Validate config: check line item format, summary, etc.
     *
     * @return array{passed: bool, error_message: string|null, details: array|null}
     */
    private function validateConfig(AnaplanApiService $api, string $modelId, array $config): array
    {
        $moduleId = $config['module_anaplan_id'] ?? null;
        $lineItemId = $config['line_item_anaplan_id'] ?? null;

        if (! $moduleId || ! $lineItemId) {
            return ['passed' => false, 'error_message' => 'Checkpoint configuration incomplete.', 'details' => null];
        }

        $lineItems = $api->fetchLineItems($modelId, $moduleId, true);
        if ($lineItems === null) {
            return ['passed' => false, 'error_message' => 'Unable to access model.', 'details' => null];
        }

        $lineItem = collect($lineItems)->firstWhere('id', $lineItemId);
        if (! $lineItem) {
            return ['passed' => false, 'error_message' => 'Line item not found.', 'details' => null];
        }

        $errors = [];

        if (isset($config['expected_format'])) {
            $actualFormat = $lineItem['format'] ?? null;
            if ($actualFormat !== $config['expected_format']) {
                $errors[] = "Format: expected {$config['expected_format']}, found {$actualFormat}";
            }
        }

        if (isset($config['expected_summary'])) {
            $actualSummary = $lineItem['summary'] ?? null;
            if ($actualSummary !== $config['expected_summary']) {
                $errors[] = "Summary method: expected {$config['expected_summary']}, found {$actualSummary}";
            }
        }

        $passed = empty($errors);

        return [
            'passed' => $passed,
            'error_message' => $passed ? null : 'Configuration issues: '.implode('; ', $errors),
            'details' => [
                'checks' => $errors,
                'actual_format' => $lineItem['format'] ?? null,
                'actual_summary' => $lineItem['summary'] ?? null,
            ],
        ];
    }

    private function createResult(
        LabParticipant $participant,
        LabCheckpoint $checkpoint,
        int $attemptNumber,
        bool $passed,
        ?string $errorMessage = null,
        ?array $details = null,
    ): LabCheckpointResult {
        return LabCheckpointResult::create([
            'lab_participant_id' => $participant->id,
            'lab_checkpoint_id' => $checkpoint->id,
            'passed' => $passed,
            'validated_at' => now(),
            'error_message' => $errorMessage,
            'validation_details' => $details,
            'attempt_number' => $attemptNumber,
        ]);
    }
}
