<?php

namespace App\Services;

use App\Helpers\FiscalYearHelper;
use App\Models\Account;
use App\Models\Contact;
use App\Models\DealRegistration;
use App\Models\FieldMapping;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;

class DataIngestionService
{
    /** @var array<string, int> */
    private array $partnerCache = [];

    private const ALLOWED_MODELS = [
        'partners',
        'contacts',
        'opportunities',
        'projects',
        'leads',
        'accounts',
        'resources',
        'deal-registrations',
    ];

    public function __construct(private ChangeDetectionService $changeDetection) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function ingest(
        string $model,
        array $rows,
        ?int $userId = null,
        ?int $connectionId = null,
        ?string $snapshotDate = null,
        string $source = 'api',
    ): IngestResult {
        $result = new IngestResult;
        $result->total = count($rows);

        if (! in_array($model, self::ALLOWED_MODELS)) {
            $result->addError(0, "Unknown model: {$model}");
            $result->failed = $result->total;

            return $result;
        }

        // Resolve deprecated/renamed field names to current names
        $rows = $this->applyFieldAliases($model, $rows);

        // Apply field mappings if a connection is specified
        if ($connectionId) {
            $rows = $this->applyFieldMappings($rows, $connectionId, $this->targetModelName($model));
        }

        $import = Import::create([
            'type' => $model,
            'source' => $source,
            'file_name' => "{$source}_import_".now()->format('Ymd_His').'.json',
            'file_path' => '',
            'status' => 'processing',
            'user_id' => $userId,
            'started_at' => now(),
        ]);

        $result->importId = $import->id;
        $this->partnerCache = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($model, $row, $import, $result, $userId, $snapshotDate);
                } catch (\Exception $e) {
                    $result->failed++;
                    $result->addError($index + 1, $e->getMessage());
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $import->update(['status' => 'failed', 'error_log' => $e->getMessage(), 'completed_at' => now()]);

            $result->failed = $result->total;
            $result->created = 0;
            $result->updated = 0;

            return $result;
        }

        // Post-commit change detection for opportunities
        if ($model === 'opportunities') {
            $this->changeDetection->detectChangesForImport($import);
        }

        $import->update([
            'status' => 'completed',
            'total_rows' => $result->total,
            'successful_rows' => $result->created + $result->updated,
            'failed_rows' => $result->failed,
            'partners_created' => $result->partnersCreated,
            "{$this->singularModel($model)}_created" => $result->created,
            "{$this->singularModel($model)}_updated" => $result->updated,
            'error_log' => ! empty($result->errors) ? implode("\n", $result->errors) : null,
            'completed_at' => now(),
        ]);

        return $result;
    }

    public static function allowedModels(): array
    {
        return self::ALLOWED_MODELS;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function processRow(string $model, array $row, Import $import, IngestResult $result, ?int $userId, ?string $snapshotDate): void
    {
        match ($model) {
            'partners' => $this->upsertPartner($row, $result, $userId),
            'contacts' => $this->upsertContact($row, $import, $result, $userId),
            'opportunities' => $this->upsertOpportunity($row, $import, $result, $userId, $snapshotDate),
            'projects' => $this->upsertProject($row, $result, $userId),
            'leads' => $this->upsertLead($row, $result),
            'accounts' => $this->upsertAccount($row, $result),
            'resources' => $this->upsertResource($row, $import, $result, $userId),
            'deal-registrations' => $this->upsertDealRegistration($row, $result, $userId),
        };
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertPartner(array $row, IngestResult $result, ?int $userId): void
    {
        $name = trim($row['name'] ?? '');
        if (! $name) {
            throw new \InvalidArgumentException('Partner name is required.');
        }

        $partner = Partner::where('name', $name)->first();

        $data = array_filter([
            'name' => $name,
            'salesforce_id' => $row['salesforce_id'] ?? null,
            'website' => $row['website'] ?? null,
            'industry' => $row['industry'] ?? null,
            'region' => $row['region'] ?? null,
            'geography' => $row['geography'] ?? null,
            'user_id' => $userId,
        ], fn ($v) => $v !== null);

        if ($partner) {
            $partner->update($data);
            $result->updated++;
        } else {
            Partner::create($data);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertContact(array $row, Import $import, IngestResult $result, ?int $userId): void
    {
        $partnerName = trim($row['partner_name'] ?? '');
        if (! $partnerName) {
            throw new \InvalidArgumentException('Partner name is required for contacts.');
        }

        $partnerId = $this->resolvePartnerId($partnerName, $result, $userId);

        $email = $row['email'] ?? null;
        if (! $email) {
            throw new \InvalidArgumentException('Email is required for contacts.');
        }

        $contact = Contact::where('partner_id', $partnerId)->where('email', $email)->first();

        $data = [
            'partner_id' => $partnerId,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'email' => $email,
            'title' => $row['title'] ?? null,
            'phone' => $row['phone'] ?? null,
        ];

        if ($contact) {
            $contact->update($data);
            $result->updated++;
        } else {
            Contact::create($data);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertOpportunity(array $row, Import $import, IngestResult $result, ?int $userId, ?string $snapshotDate): void
    {
        $partnerName = trim($row['partner_name'] ?? '');
        if (! $partnerName) {
            throw new \InvalidArgumentException('Partner name is required for opportunities.');
        }

        $partnerId = $this->resolvePartnerId($partnerName, $result, $userId);

        $closeDate = ! empty($row['close_date']) ? $row['close_date'] : null;
        $fiscalPeriod = FiscalYearHelper::calculateFiscalPeriod($closeDate);

        $opportunityData = [
            'opportunity_name' => $row['opportunity_name'],
            'account_name' => $row['account_name'] ?? null,
            'partner_id' => $partnerId,
            'stage' => $row['stage'] ?? null,
            'amount' => $row['amount'] ?? null,
            'close_date' => $closeDate,
            'fiscal_quarter' => $fiscalPeriod['fiscal_quarter'],
            'fiscal_year' => $fiscalPeriod['fiscal_year'],
            'type' => $row['type'] ?? null,
            'revenue_type' => $row['revenue_type'] ?? null,
            'product_area' => $row['product_area'] ?? null,
            'probability' => $row['probability'] ?? null,
            'owner' => $row['owner'] ?? null,
            'lead_source' => $row['lead_source'] ?? null,
            'import_id' => $import->id,
        ];

        $matchField = ! empty($row['salesforce_id']) ? 'salesforce_id' : 'opportunity_name';
        $matchValue = ! empty($row['salesforce_id']) ? $row['salesforce_id'] : $row['opportunity_name'];

        $opportunity = Opportunity::where($matchField, $matchValue)->first();

        if ($opportunity) {
            $opportunityData['previous_stage'] = $opportunity->stage;
            $opportunityData['previous_close_date'] = $opportunity->close_date;
            if (! $opportunity->first_seen_at) {
                $opportunityData['first_seen_at'] = $opportunity->created_at;
            }
            $opportunity->update($opportunityData);
            $result->updated++;
        } else {
            if (! empty($row['salesforce_id'])) {
                $opportunityData['salesforce_id'] = $row['salesforce_id'];
            }
            $opportunityData['first_seen_at'] = now();
            $opportunity = Opportunity::create($opportunityData);
            $result->created++;
        }

        // Create snapshot
        if ($snapshotDate) {
            OpportunitySnapshot::create([
                'opportunity_id' => $opportunity->id,
                'import_id' => $import->id,
                'snapshot_date' => $snapshotDate,
                'opportunity_name' => $opportunity->opportunity_name,
                'account_name' => $opportunity->account_name,
                'partner_id' => $opportunity->partner_id,
                'stage' => $opportunity->stage,
                'amount' => $opportunity->amount,
                'close_date' => $opportunity->close_date,
                'fiscal_quarter' => $opportunity->fiscal_quarter,
                'fiscal_year' => $opportunity->fiscal_year,
                'type' => $opportunity->type,
                'lead_source' => $opportunity->lead_source,
                'revenue_type' => $opportunity->revenue_type,
                'product_area' => $opportunity->product_area,
                'probability' => $opportunity->probability,
                'owner' => $opportunity->owner,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertProject(array $row, IngestResult $result, ?int $userId): void
    {
        $partnerName = trim($row['partner_name'] ?? '');
        if (! $partnerName) {
            throw new \InvalidArgumentException('Partner name is required for projects.');
        }

        $partnerId = $this->resolvePartnerId($partnerName, $result, $userId);

        $name = $row['name'] ?? null;
        if (! $name) {
            throw new \InvalidArgumentException('Project name is required.');
        }

        $project = Project::where('partner_id', $partnerId)->where('name', $name)->first();

        $data = [
            'partner_id' => $partnerId,
            'name' => $name,
            'status' => $row['status'] ?? 'planning',
            'acv' => $row['acv'] ?? null,
            'start_date' => $row['start_date'] ?? null,
            'end_date' => $row['end_date'] ?? null,
            'percent_complete' => $row['percent_complete'] ?? 0,
            'client' => $row['client'] ?? null,
            'delivery_team' => $row['delivery_team'] ?? null,
            'initiative' => $row['initiative'] ?? null,
            'budget' => $row['budget'] ?? null,
        ];

        if ($project) {
            $project->update($data);
            $result->updated++;
        } else {
            Project::create($data);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertLead(array $row, IngestResult $result): void
    {
        $email = $row['email'] ?? null;
        if (! $email) {
            throw new \InvalidArgumentException('Email is required for leads.');
        }

        $lead = Lead::where('email', $email)->first();

        $data = [
            'name' => $row['name'] ?? null,
            'email' => $email,
            'phone' => $row['phone'] ?? null,
            'company' => $row['company'] ?? null,
            'title' => $row['title'] ?? null,
            'source' => $row['source'] ?? null,
            'status' => $row['status'] ?? 'new',
            'notes' => $row['notes'] ?? null,
        ];

        if (! empty($row['partner_name'])) {
            $partner = Partner::where('name', trim($row['partner_name']))->first();
            if ($partner) {
                $data['partner_id'] = $partner->id;
            }
        }

        if ($lead) {
            $lead->update($data);
            $result->updated++;
        } else {
            Lead::create($data);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertAccount(array $row, IngestResult $result): void
    {
        $name = trim($row['name'] ?? '');
        if (! $name) {
            throw new \InvalidArgumentException('Account name is required.');
        }

        $account = Account::where('name', $name)->first();

        if ($account) {
            $result->updated++;
        } else {
            Account::create(['name' => $name]);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertResource(array $row, Import $import, IngestResult $result, ?int $userId): void
    {
        $partnerName = trim($row['partner_name'] ?? '');
        if (! $partnerName) {
            throw new \InvalidArgumentException('Partner name is required for resources.');
        }

        $partnerId = $this->resolvePartnerId($partnerName, $result, $userId);

        $name = $row['name'] ?? null;
        $program = $row['program'] ?? null;
        if (! $name) {
            throw new \InvalidArgumentException('Resource name is required.');
        }

        $query = Resource::where('partner_id', $partnerId)->where('name', $name);
        if ($program) {
            $query->where('program', $program);
        }
        $resource = $query->first();

        $data = [
            'partner_id' => $partnerId,
            'name' => $name,
            'role' => $row['role'] ?? null,
            'program' => $program,
            'status' => $row['status'] ?? 'active',
            'date_certified' => $row['date_certified'] ?? null,
            'import_id' => $import->id,
        ];

        if ($resource) {
            $resource->update($data);
            $result->updated++;
        } else {
            Resource::create($data);
            $result->created++;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertDealRegistration(array $row, IngestResult $result, ?int $userId): void
    {
        $dealName = $row['deal_name'] ?? null;
        $customerName = $row['customer_name'] ?? null;
        if (! $dealName || ! $customerName) {
            throw new \InvalidArgumentException('Deal name and customer name are required.');
        }

        $data = [
            'deal_name' => $dealName,
            'customer_name' => $customerName,
            'customer_contact_email' => $row['customer_contact_email'] ?? null,
            'estimated_value' => $row['estimated_value'] ?? null,
            'expected_close_date' => $row['expected_close_date'] ?? null,
            'product_area' => $row['product_area'] ?? null,
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? 'draft',
        ];

        if (! empty($row['partner_name'])) {
            $partnerId = $this->resolvePartnerId(trim($row['partner_name']), $result, $userId);
            $data['partner_id'] = $partnerId;
        }

        $dealReg = DealRegistration::where('deal_name', $dealName)
            ->where('customer_name', $customerName)
            ->first();

        if ($dealReg) {
            $dealReg->update($data);
            $result->updated++;
        } else {
            DealRegistration::create($data);
            $result->created++;
        }
    }

    private function resolvePartnerId(string $partnerName, IngestResult $result, ?int $userId): int
    {
        if (isset($this->partnerCache[$partnerName])) {
            return $this->partnerCache[$partnerName];
        }

        $partner = Partner::firstOrCreate(
            ['name' => $partnerName],
            ['user_id' => $userId],
        );

        if ($partner->wasRecentlyCreated) {
            $result->partnersCreated++;
        }

        $this->partnerCache[$partnerName] = $partner->id;

        return $partner->id;
    }

    /**
     * Apply field mappings from a connection to transform incoming row keys.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyFieldMappings(array $rows, int $connectionId, string $targetModel): array
    {
        $mappings = FieldMapping::query()
            ->where('connection_id', $connectionId)
            ->where('target_model', $targetModel)
            ->get();

        if ($mappings->isEmpty()) {
            return $rows;
        }

        return array_map(function (array $row) use ($mappings) {
            $mapped = [];
            $mappedSourceKeys = [];

            foreach ($mappings as $mapping) {
                $sourceKey = $mapping->source_field;
                $targetKey = $mapping->target_field;

                if (array_key_exists($sourceKey, $row)) {
                    $value = $row[$sourceKey];

                    // Apply transform
                    if ($mapping->transform && $value !== null) {
                        $value = $this->applyTransform($value, $mapping->transform);
                    }

                    $mapped[$targetKey] = $value;
                    $mappedSourceKeys[] = $sourceKey;
                } elseif ($mapping->default_value !== null) {
                    $mapped[$targetKey] = $mapping->default_value;
                }
            }

            // Pass through unmapped keys
            foreach ($row as $key => $value) {
                if (! in_array($key, $mappedSourceKeys) && ! array_key_exists($key, $mapped)) {
                    $mapped[$key] = $value;
                }
            }

            return $mapped;
        }, $rows);
    }

    private function applyTransform(mixed $value, string $transform): mixed
    {
        return match ($transform) {
            'lowercase' => strtolower((string) $value),
            'uppercase' => strtoupper((string) $value),
            'trim' => trim((string) $value),
            'date_iso' => date('Y-m-d', strtotime((string) $value)) ?: $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => in_array(strtolower((string) $value), ['true', '1', 'yes']),
            default => $value,
        };
    }

    /**
     * Convert plural model slug to singular for Import column names.
     */
    private function singularModel(string $model): string
    {
        return match ($model) {
            'partners' => 'partners',
            'contacts' => 'contacts',
            'opportunities' => 'opportunities',
            'projects' => 'projects',
            'leads' => 'leads',
            'accounts' => 'accounts',
            'resources' => 'resources',
            'deal-registrations' => 'deal_registrations',
            default => $model,
        };
    }

    /**
     * Convert plural API slug to PascalCase model name for field mappings.
     */
    private function targetModelName(string $model): string
    {
        return match ($model) {
            'partners' => 'Partner',
            'contacts' => 'Contact',
            'opportunities' => 'Opportunity',
            'projects' => 'Project',
            'leads' => 'Lead',
            'accounts' => 'Account',
            'resources' => 'Resource',
            'deal-registrations' => 'DealRegistration',
            default => $model,
        };
    }

    /**
     * Field aliases for backwards compatibility when the data model evolves.
     *
     * When a column is renamed or restructured, add the old name as key and
     * the current name as value. This lets existing API clients and field
     * mappings continue to work without changes.
     *
     * Format: model slug => [old_field => current_field]
     *
     * Example — if Partner.geography was renamed to Partner.geo_region:
     *   'partners' => ['geography' => 'geo_region'],
     *
     * @return array<string, array<string, string>>
     */
    private function fieldAliases(): array
    {
        return [
            // 'partners' => [],
            // 'contacts' => [],
            // 'opportunities' => [],
            // 'projects' => [],
            // 'leads' => [],
            // 'accounts' => [],
            // 'resources' => [],
            // 'deal-registrations' => [],
        ];
    }

    /**
     * Resolve deprecated/aliased field names to their current equivalents.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyFieldAliases(string $model, array $rows): array
    {
        $aliases = $this->fieldAliases()[$model] ?? [];

        if (empty($aliases)) {
            return $rows;
        }

        return array_map(function (array $row) use ($aliases) {
            foreach ($aliases as $oldKey => $newKey) {
                if (array_key_exists($oldKey, $row) && ! array_key_exists($newKey, $row)) {
                    $row[$newKey] = $row[$oldKey];
                    unset($row[$oldKey]);
                }
            }

            return $row;
        }, $rows);
    }
}
