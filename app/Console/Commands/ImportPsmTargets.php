<?php

namespace App\Console\Commands;

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

        $plan = $importer->plan($file, $role, $period);

        $this->line('');
        $this->info("PSM Target import — role: <comment>{$role}</comment>  period: <comment>{$period}</comment>");
        $this->line('Capabilities (columns): '.implode(', ', $plan['columns']));
        $s = $plan['summary'];
        $this->line(sprintf(
            'Partners matched: %d   objectives → create %d, update %d   (skipped %d zero cells)',
            $s['partners_matched'], $s['create'], $s['update'], $s['skipped_zero'],
        ));

        if ($plan['unmatched_partners']) {
            $this->warn('Unmatched partners ('.count($plan['unmatched_partners']).') — NOT imported, resolve names:');
            foreach ($plan['unmatched_partners'] as $u) {
                $this->line('   • '.$u);
            }
        }

        if (! empty($plan['partners_without_owner'])) {
            $this->warn('Partners with no owning PSM ('.count($plan['partners_without_owner']).') — NOT imported (objectives need an owner):');
            foreach ($plan['partners_without_owner'] as $p) {
                $this->line('   • '.$p);
            }
        }

        if ($plan['review_candidates']) {
            $this->line('');
            $this->warn('Review — pre-existing objectives that may be placeholders to retire ('.count($plan['review_candidates']).'):');
            foreach (array_slice($plan['review_candidates'], 0, 20) as $c) {
                $this->line(sprintf('   • #%d (partner %d) "%s" target %s', $c['id'], $c['partner_id'], $c['title'], $c['target'] ?? '—'));
            }
            if (count($plan['review_candidates']) > 20) {
                $this->line('   … +'.(count($plan['review_candidates']) - 20).' more');
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

        $counts = $importer->apply($plan, $this->option('target-date') ?: null);
        $this->line('');
        $this->info(sprintf('Applied: created %d, updated %d objectives.', $counts['created'], $counts['updated']));

        return self::SUCCESS;
    }
}
