<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PsmTargetImport\PsmTargetImporter;
use Illuminate\Console\Command;

/**
 * Import per-partner enablement targets from a PSM Target Tracking workbook into
 * PsmObjectives. Dry-run by default: it prints exactly what it WOULD do; pass
 * --commit to write. Idempotent on (partner, capability, role, period).
 */
class ImportPsmTargets extends Command
{
    protected $signature = 'enablement:import-targets
        {file : Path to the .xlsx workbook}
        {--role= : delivery | sales | pre-sales (which resource role this file is for)}
        {--period=Q3-FY27 : the target period, e.g. Q3-FY27}
        {--target-date= : due date for created objectives (default: end of current quarter); period label is authoritative in metric_config}
        {--create-missing-partners : create a Partner (owned by the fallback owner) for each unmatched name}
        {--fallback-owner=gunnar.stoa@gmail.com : email or id of the owner for ownerless/created partners}
        {--retire-placeholders : cancel the surfaced placeholder objectives}
        {--commit : Write the changes (otherwise dry-run only)}';

    protected $description = 'Import per-partner enablement targets (PSM Target Tracking) into objectives';

    public function handle(PsmTargetImporter $importer): int
    {
        $file = $this->argument('file');
        $role = (string) $this->option('role');
        $period = (string) $this->option('period');

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }
        if (! in_array($role, ['delivery', 'sales', 'pre-sales'], true)) {
            $this->error('--role must be one of: delivery, sales, pre-sales');

            return self::FAILURE;
        }

        $createMissing = (bool) $this->option('create-missing-partners');
        $fallbackOwnerId = null;
        if ($createMissing || $this->option('fallback-owner')) {
            $ref = (string) $this->option('fallback-owner');
            $owner = is_numeric($ref)
                ? User::find((int) $ref)
                : User::where('email', $ref)->first();
            if (! $owner) {
                $this->error("--fallback-owner not found: {$ref}");

                return self::FAILURE;
            }
            $fallbackOwnerId = $owner->id;
            $this->line("Fallback owner: <comment>{$owner->name}</comment> (#{$owner->id})");
        }

        $plan = $importer->plan($file, $role, $period, $fallbackOwnerId, $createMissing);

        $this->line('');
        $this->info("PSM Target import — role: <comment>{$role}</comment>  period: <comment>{$period}</comment>");
        $this->line('Capabilities (columns): '.implode(', ', $plan['columns']));
        $s = $plan['summary'];
        $this->line(sprintf(
            'Partners matched: %d   objectives → create %d, update %d   (skipped %d zero cells)',
            $s['partners_matched'], $s['create'], $s['update'], $s['skipped_zero'],
        ));

        if (! empty($plan['partners_to_create'])) {
            $this->warn('Partners to CREATE ('.count($plan['partners_to_create']).') — owned by the fallback owner:');
            foreach ($plan['partners_to_create'] as $p) {
                $this->line('   + '.$p);
            }
        }

        if ($plan['unmatched_partners']) {
            $this->warn('Unmatched partners ('.count($plan['unmatched_partners']).') — NOT imported, resolve names:');
            foreach ($plan['unmatched_partners'] as $u) {
                $this->line('   • '.$u);
            }
        }

        if (! empty($plan['owner_assigned_fallback'])) {
            $this->warn('Ownerless partners → objectives assigned to fallback owner ('.count($plan['owner_assigned_fallback']).') — the partner itself still has no PSM:');
            foreach ($plan['owner_assigned_fallback'] as $p) {
                $this->line('   • '.$p);
            }
        }

        if (! empty($plan['partners_without_owner'])) {
            $this->warn('Partners with no owning PSM ('.count($plan['partners_without_owner']).') — NOT imported (no fallback owner given):');
            foreach ($plan['partners_without_owner'] as $p) {
                $this->line('   • '.$p);
            }
        }

        if ($plan['review_candidates']) {
            $this->line('');
            $retiring = (bool) $this->option('retire-placeholders');
            $verb = $retiring ? 'will be RETIRED (status → cancelled)' : 'may be placeholders to retire';
            $this->warn('Placeholder objectives — '.$verb.' ('.count($plan['review_candidates']).'):');
            foreach (array_slice($plan['review_candidates'], 0, 30) as $c) {
                $this->line(sprintf('   • #%d (partner %d) "%s" target %s', $c['id'], $c['partner_id'], $c['title'], $c['target'] ?? '—'));
            }
            if (count($plan['review_candidates']) > 30) {
                $this->line('   … +'.(count($plan['review_candidates']) - 30).' more');
            }
        }

        $this->line('');
        $sample = array_slice($plan['actions'], 0, 12);
        foreach ($sample as $a) {
            $this->line(sprintf('   [%s] %s — %s → %d', strtoupper($a['verb']), $a['partner_name'], $a['capability'], $a['target']));
        }
        if (count($plan['actions']) > count($sample)) {
            $this->line('   … +'.(count($plan['actions']) - count($sample)).' more');
        }

        if (! $this->option('commit')) {
            $this->line('');
            $this->warn('DRY RUN — nothing written. Re-run with --commit to apply.');

            return self::SUCCESS;
        }

        $counts = $importer->apply(
            $plan,
            $this->option('target-date') ?: null,
            (bool) $this->option('retire-placeholders'),
        );
        $this->line('');
        $this->info(sprintf(
            'Applied: %d partners created, %d objectives created, %d updated, %d retired.',
            $counts['partners_created'], $counts['created'], $counts['updated'], $counts['retired'],
        ));

        return self::SUCCESS;
    }
}
