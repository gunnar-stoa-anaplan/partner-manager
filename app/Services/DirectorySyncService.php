<?php

namespace App\Services;

use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DirectorySyncService
{
    /**
     * Sync users from a CSV file using the given connection's field mappings.
     */
    public function syncFromCsv(IntegrationConnection $connection, string $filePath, IntegrationJob $job): DirectorySyncResult
    {
        $rows = $this->parseCsv($filePath);
        $mappings = FieldMapping::where('connection_id', $connection->id)
            ->where('target_model', 'User')
            ->get();

        $job->update([
            'status' => 'running',
            'total_rows' => count($rows),
            'started_at' => now(),
        ]);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $managerMap = []; // external_id => manager_external_id (for second pass)

        DB::beginTransaction();

        try {
            // First pass: create/update users
            foreach ($rows as $index => $row) {
                $mapped = $this->applyMappings($row, $mappings);

                if (empty($mapped['external_id']) || empty($mapped['email'])) {
                    $errors[] = "Row {$index}: Missing required field (external_id or email).";
                    $skipped++;

                    continue;
                }

                // Track manager mapping for second pass
                if (! empty($mapped['manager_external_id'])) {
                    $managerMap[$mapped['external_id']] = $mapped['manager_external_id'];
                }

                // Find existing user by external_id or email fallback
                $user = User::where('external_id', $mapped['external_id'])->first();

                if (! $user) {
                    $user = User::where('email', $mapped['email'])
                        ->whereNull('directory_source')
                        ->first();

                    if ($user) {
                        // Locally-managed user matched by email — skip to avoid overwriting
                        $skipped++;

                        continue;
                    }
                }

                $syncData = [
                    'name' => $mapped['name'] ?? $mapped['email'],
                    'email' => $mapped['email'],
                    'external_id' => $mapped['external_id'],
                    'directory_source' => 'csv',
                    'last_synced_at' => now(),
                ];

                if ($user) {
                    // Only update synced fields
                    $user->update($syncData);
                    $updated++;
                } else {
                    // Create new user with default role and random password
                    User::create(array_merge($syncData, [
                        'role' => config('team.directory.default_role', 'psm'),
                        'password' => Hash::make(Str::random(32)),
                    ]));
                    $created++;
                }
            }

            // Second pass: resolve manager relationships
            foreach ($managerMap as $externalId => $managerExternalId) {
                $user = User::where('external_id', $externalId)->first();
                $manager = User::where('external_id', $managerExternalId)->first();

                if ($user && $manager) {
                    $user->update(['manager_id' => $manager->id]);
                } elseif ($user && ! $manager) {
                    $errors[] = "Could not resolve manager (external_id: {$managerExternalId}) for user {$user->email}.";
                }
            }

            DB::commit();

            $job->update([
                'status' => 'completed',
                'rows_processed' => $created + $updated,
                'rows_failed' => $skipped,
                'error_log' => ! empty($errors) ? implode("\n", $errors) : null,
                'summary' => compact('created', 'updated', 'skipped'),
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            $errors[] = $e->getMessage();

            $job->update([
                'status' => 'failed',
                'error_log' => implode("\n", $errors),
                'completed_at' => now(),
            ]);
        }

        return new DirectorySyncResult($created, $updated, $skipped, $errors);
    }

    /**
     * Parse a CSV file into an array of associative arrays.
     *
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return [];
        }

        $headers = array_map('trim', $headers);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Apply field mappings to transform a CSV row into target fields.
     *
     * @param  array<string, string>  $row
     * @param  \Illuminate\Support\Collection<int, FieldMapping>  $mappings
     * @return array<string, string|null>
     */
    private function applyMappings(array $row, $mappings): array
    {
        $mapped = [];

        foreach ($mappings as $mapping) {
            $value = $row[$mapping->source_field] ?? $mapping->default_value;

            if ($value !== null && $mapping->transform) {
                $value = $this->applyTransform($value, $mapping->transform);
            }

            $mapped[$mapping->target_field] = $value;
        }

        return $mapped;
    }

    /**
     * Apply a transform to a field value.
     */
    private function applyTransform(string $value, string $transform): string
    {
        return match ($transform) {
            'lowercase' => strtolower($value),
            'uppercase' => strtoupper($value),
            'trim' => trim($value),
            default => $value,
        };
    }
}
