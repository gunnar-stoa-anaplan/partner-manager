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
     * @return array{
     *   role: string, period: string, columns: array<int, string>,
     *   actions: array<int, array<string, mixed>>,
     *   unmatched_partners: array<int, string>,
     *   summary: array<string, int>
     * }
     */
    public function plan(string $filePath, string $role, string $period): array
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
        $matchedIds = [];
        $summary = ['create' => 0, 'update' => 0, 'skipped_zero' => 0, 'partners_matched' => 0];

        foreach (array_slice($rows, 2, null, true) as $r) {       // data rows (3+)
            $name = trim((string) ($r[0] ?? ''));
            if ($name === '') {
                continue;
            }
            $partner = $this->matchPartner($name, $partnersByName);
            if (! $partner) {
                $unmatched[] = $name;
                continue;
            }
            if (! $partner->user_id) {
                // Objectives require an owning user; a partner with no PSM can't
                // receive one. Report rather than fail the whole import.
                $noOwner[] = $partner->name;
                continue;
            }
            $summary['partners_matched']++;
            $matchedIds[] = $partner->id;

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
                [$verb, $existingId] = $this->resolveExisting($partner, $importKey);

                $summary[$verb]++;
                $actions[] = [
                    'verb' => $verb,                 // create | update | adopt
                    'existing_id' => $existingId,
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                    'user_id' => $partner->user_id,
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
            'actions' => $actions,
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
     * @return array{created: int, updated: int}
     */
    public function apply(array $plan, ?string $targetDate = null): array
    {
        $counts = ['created' => 0, 'updated' => 0];
        $targetDate ??= now()->endOfQuarter()->toDateString();

        foreach ($plan['actions'] as $a) {
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

            $objective->partner_id = $a['partner_id'];
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
