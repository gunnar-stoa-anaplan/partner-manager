<?php

namespace App\Services\PsmTargetImport;

use App\Models\Partner;
use App\Models\PsmObjective;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Imports per-partner enablement targets from a "PSM Target Tracking" workbook.
 * The sheet is a matrix: rows = partners, columns = capabilities, cells = the
 * target number of resources for a given role (delivery / sales / pre-sales)
 * and period (e.g. Q3-FY27). Each cell becomes a PsmObjective.
 *
 * Safe by design: plan() computes what WOULD happen (a dry run); apply() writes
 * only what plan() produced. Idempotent on (partner, capability, role, period)
 * via metric_config.import_key, and it can adopt a matching pre-existing
 * objective (e.g. an IFP placeholder) instead of duplicating it.
 */
class PsmTargetImporter
{
    /** Capability keyword => certification product_area (for adoption/tracking). */
    private const PRODUCT_AREAS = ['IFP', 'OWP', 'Platform', 'Polaris', 'SCM', 'SPM'];

    /**
     * @param  int|null  $fallbackOwnerId  owner assigned to objectives whose partner
     *                                      has no PSM (and to partners created here);
     *                                      when null, ownerless partners are skipped.
     * @param  bool  $createMissing  create a Partner for each unmatched name (owned by
     *                               the fallback owner) instead of skipping it.
     * @return array{
     *   role: string, period: string, columns: array<int, string>,
     *   fallback_owner_id: int|null,
     *   actions: array<int, array<string, mixed>>,
     *   partners_to_create: array<int, string>,
     *   owner_assigned_fallback: array<int, string>,
     *   unmatched_partners: array<int, string>,
     *   partners_without_owner: array<int, string>,
     *   review_candidates: array<int, array<string, mixed>>,
     *   summary: array<string, int>
     * }
     */
    public function plan(string $filePath, string $role, string $period, ?int $fallbackOwnerId = null, bool $createMissing = false): array
    {
        $rows = $this->readSheet($filePath);
        $headerRow = $rows[1] ?? [];               // row 2 = headers
        $columns = [];
        foreach ($headerRow as $col => $raw) {
            if ($col === 0 || trim((string) $raw) === '') {
                continue;                          // col A = partner name
            }
            $columns[$col] = trim((string) $raw);
        }

        $partnersByName = $this->partnerLookup();
        $actions = [];
        $unmatched = [];
        $noOwner = [];
        $toCreate = [];              // normalized => original name
        $ownerAssigned = [];         // existing partners whose objectives fall back to $fallbackOwnerId
        $matchedIds = [];
        $summary = ['create' => 0, 'update' => 0, 'skipped_zero' => 0, 'partners_matched' => 0, 'partners_to_create' => 0];

        foreach (array_slice($rows, 2, null, true) as $r) {       // data rows (3+)
            $name = trim((string) ($r[0] ?? ''));
            if ($name === '') {
                continue;
            }

            $partner = $this->matchPartner($name, $partnersByName);
            $createPartnerName = null;

            if (! $partner) {
                if (! $createMissing) {
                    $unmatched[] = $name;

                    continue;
                }
                // Deferred to apply(): the partner is created there, owned by the
                // fallback owner. plan() only records the intent.
                $createPartnerName = $name;
                $ownerId = $fallbackOwnerId;
                $partnerId = null;
                $partnerName = $name;
                $norm = $this->normalize($name);
                if (! isset($toCreate[$norm])) {
                    $toCreate[$norm] = $name;
                    $summary['partners_to_create']++;
                }
            } else {
                if (! $partner->user_id) {
                    // Objectives require an owning user. With a fallback owner we
                    // assign the objective to them (the partner itself keeps no PSM
                    // — that data gap is surfaced, not silently reassigned).
                    if ($fallbackOwnerId === null) {
                        $noOwner[] = $partner->name;

                        continue;
                    }
                    $ownerAssigned[] = $partner->name;
                }
                $ownerId = $partner->user_id ?: $fallbackOwnerId;
                $partnerId = $partner->id;
                $partnerName = $partner->name;
                $summary['partners_matched']++;
                $matchedIds[] = $partner->id;
            }

            foreach ($columns as $col => $capabilityRaw) {
                $value = $r[$col] ?? null;
                if ($value === null || $value === '' || ! is_numeric($value)) {
                    continue;
                }
                $target = (int) round((float) $value);
                if ($target <= 0) {
                    $summary['skipped_zero']++;
                    continue;
                }

                $capability = $this->cleanCapability($capabilityRaw);
                $productArea = $this->productAreaFor($capability);
                $importKey = $this->importKey($role, $period, $capability);
                [$verb, $existingId] = $partner
                    ? $this->resolveExisting($partner, $importKey)
                    : ['create', null];       // new partner → always create

                $summary[$verb]++;
                $actions[] = [
                    'verb' => $verb,                 // create | update
                    'existing_id' => $existingId,
                    'partner_id' => $partnerId,      // null when the partner is created in apply()
                    'create_partner_name' => $createPartnerName,
                    'partner_name' => $partnerName,
                    'user_id' => $ownerId,
                    'capability' => $capability,
                    'product_area' => $productArea,
                    'target' => $target,
                    'import_key' => $importKey,
                    'column' => $capabilityRaw,
                ];
            }
        }

        return [
            'role' => $role,
            'period' => $period,
            'columns' => array_values($columns),
            'fallback_owner_id' => $fallbackOwnerId,
            'actions' => $actions,
            'partners_to_create' => array_values($toCreate),
            'owner_assigned_fallback' => array_values(array_unique($ownerAssigned)),
            'unmatched_partners' => array_values(array_unique($unmatched)),
            'partners_without_owner' => array_values(array_unique($noOwner)),
            'review_candidates' => $this->reviewCandidates(array_values(array_unique($matchedIds))),
            'summary' => $summary,
        ];
    }

    /**
     * Apply a plan produced by plan(). Returns counts actually written.
     *
     * @param  array<string, mixed>  $plan
     * @param  bool  $retirePlaceholders  cancel the surfaced review_candidates
     * @return array{created: int, updated: int, partners_created: int, retired: int}
     */
    public function apply(array $plan, ?string $targetDate = null, bool $retirePlaceholders = false): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'partners_created' => 0, 'retired' => 0];
        $targetDate ??= now()->endOfQuarter()->toDateString();

        // 1. Create the missing partners first so their objectives can reference
        //    the new ids. Owned by the fallback owner; idempotent because a second
        //    run finds them already present and matches instead of re-creating.
        $createdPartnerIds = [];   // normalized name => id
        foreach ($plan['partners_to_create'] as $name) {
            $norm = $this->normalize($name);
            $partner = Partner::withoutGlobalScopes()
                ->whereRaw('lower(name) = ?', [Str::lower($name)])
                ->first();
            if (! $partner) {
                $partner = Partner::create([
                    'name' => $name,
                    'user_id' => $plan['fallback_owner_id'],
                ]);
                $counts['partners_created']++;
            }
            $createdPartnerIds[$norm] = $partner->id;
        }

        // 2. Objectives.
        foreach ($plan['actions'] as $a) {
            $partnerId = $a['partner_id']
                ?? ($a['create_partner_name'] !== null
                    ? ($createdPartnerIds[$this->normalize($a['create_partner_name'])] ?? null)
                    : null);
            if ($partnerId === null) {
                continue; // defensive: partner could not be resolved
            }

            $objective = $a['existing_id']
                ? PsmObjective::find($a['existing_id'])
                : new PsmObjective;

            $isNew = ! $objective->exists;
            $config = $objective->metric_config ?? [];
            $config = array_merge($config, [
                'source' => 'psm_target_import',
                'import_key' => $a['import_key'],
                'capability' => $a['capability'],
                'role' => $plan['role'],
                'period' => $plan['period'],
                'product_area' => $a['product_area'],
                'column' => $a['column'],
            ]);

            $objective->partner_id = $partnerId;
            if ($isNew) {
                // Only set these on create; adoption preserves the existing
                // owner/title/metric_type so we never downgrade a tracked goal.
                $objective->user_id = $a['user_id'];
                $objective->title = sprintf('%s — %s enablement (%s)', $a['capability'], $plan['role'], $plan['period']);
                $objective->category = 'enablement';
                $objective->metric_type = 'manual';
                $objective->status = 'active';
                $objective->target_date = $targetDate;
            }
            $objective->description = sprintf(
                'Target: %d %s resources enabled on %s for %s. Imported from PSM Target Tracking.',
                $a['target'], $plan['role'], $a['capability'], $plan['period'],
            );
            $objective->target_value = $a['target'];
            $objective->metric_config = $config;
            $objective->save();

            $counts[$isNew ? 'created' : 'updated']++;
        }

        // 3. Retire the surfaced placeholder objectives (status → cancelled).
        //    Reversible and idempotent: an already-cancelled objective is skipped.
        if ($retirePlaceholders) {
            foreach ($plan['review_candidates'] as $c) {
                $objective = PsmObjective::withoutGlobalScopes()->find($c['id']);
                if (! $objective || $objective->status === 'cancelled') {
                    continue;
                }
                $config = $objective->metric_config ?? [];
                $config['retired_by'] = 'psm_target_import';
                $config['retired_at'] = now()->toIso8601String();
                $config['retired_reason'] = sprintf('Superseded by imported %s targets (%s).', $plan['role'], $plan['period']);
                $objective->status = 'cancelled';
                $objective->metric_config = $config;
                $objective->save();
                $counts['retired']++;
            }
        }

        return $counts;
    }

    /** @return array<int, array<int, mixed>> zero-indexed rows of the first sheet */
    private function readSheet(string $filePath): array
    {
        $sheet = IOFactory::load($filePath)->getActiveSheet();
        $rows = [];
        foreach ($sheet->toArray(null, true, false, false) as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /** @return array<string, Partner> normalized name => partner */
    private function partnerLookup(): array
    {
        $map = [];
        Partner::query()->withoutGlobalScopes()->get(['id', 'name', 'user_id'])
            ->each(function (Partner $p) use (&$map) {
                $map[$this->normalize($p->name)] = $p;
            });

        return $map;
    }

    /** @param  array<string, Partner>  $lookup */
    private function matchPartner(string $name, array $lookup): ?Partner
    {
        return $lookup[$this->normalize($name)] ?? null;
    }

    private function normalize(string $name): string
    {
        $n = Str::lower($name);
        $n = preg_replace('/\(.*?\)/', '', $n);                 // drop "(Partner)"
        $n = preg_replace('/[,.]/', ' ', $n);
        $n = preg_replace('/\b(llc|inc|ltd|llp|corp|corporation|company|co|partners|partner|the)\b/', ' ', $n);
        $n = preg_replace('/[^a-z0-9]+/', ' ', $n);

        return trim(preg_replace('/\s+/', ' ', $n));
    }

    private function cleanCapability(string $raw): string
    {
        $c = preg_replace('/\s+(delivery\s+)?targets?$/i', '', trim($raw));

        return trim($c) ?: trim($raw);
    }

    private function productAreaFor(string $capability): ?string
    {
        foreach (self::PRODUCT_AREAS as $pa) {
            if (stripos($capability, $pa) !== false) {
                return $pa;
            }
        }

        return null;
    }

    private function importKey(string $role, string $period, string $capability): string
    {
        return sprintf('psm_target:%s:%s:%s', Str::slug($role), Str::slug($period), Str::slug($capability));
    }

    /**
     * @return array{0: string, 1: int|null} [verb, existing objective id]
     */
    private function resolveExisting(Partner $partner, string $importKey): array
    {
        // Idempotent on (partner, capability, role, period) via import_key only.
        // We deliberately do NOT auto-adopt/overwrite pre-existing objectives by
        // fuzzy title matching — that risks silently changing the wrong record.
        // Overlapping placeholders are surfaced in the review list instead.
        $own = PsmObjective::withoutGlobalScopes()
            ->where('partner_id', $partner->id)
            ->where('metric_config->import_key', $importKey)
            ->first();

        return $own ? ['update', $own->id] : ['create', null];
    }

    /**
     * Pre-existing objectives (not created by this importer) for the matched
     * partners that reference a capability product_area — likely placeholders the
     * user may want to retire once the imported targets land. Surfaced for review,
     * never modified automatically.
     *
     * @param  array<int, int>  $partnerIds
     * @return array<int, array{id: int, partner_id: int, title: string, target: float|null, product_area: ?string}>
     */
    private function reviewCandidates(array $partnerIds): array
    {
        if ($partnerIds === []) {
            return [];
        }

        return PsmObjective::withoutGlobalScopes()
            ->whereIn('partner_id', $partnerIds)
            ->whereNull('metric_config->import_key')
            ->where(function ($q) {
                foreach (self::PRODUCT_AREAS as $pa) {
                    $q->orWhere('metric_config->product_area', $pa)
                        ->orWhere('title', 'ilike', "%{$pa}%");
                }
            })
            ->get(['id', 'partner_id', 'title', 'target_value', 'metric_config'])
            ->map(fn (PsmObjective $o) => [
                'id' => $o->id,
                'partner_id' => $o->partner_id,
                'title' => (string) $o->title,
                'target' => $o->target_value !== null ? (float) $o->target_value : null,
                'product_area' => $o->metric_config['product_area'] ?? null,
            ])
            ->all();
    }
}
