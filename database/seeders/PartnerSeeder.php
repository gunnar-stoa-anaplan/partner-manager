<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PartnerSeeder extends Seeder
{
    /**
     * PSM name normalization map: Excel name → GunnarSeeder name.
     * Handles minor spelling differences between data sources.
     */
    private const PSM_NAME_MAP = [
        'Gunnar Stoa' => 'Gunnar Stoa',
        'Rick Palmer' => 'Rick Palmer',
        'Nicole Demarey' => 'Nicole Demarey',
        'Ben Wilson' => 'Ben Wilson',
    ];

    public function run(): void
    {
        $this->command->info('Seeding partners from psm_mappings.xlsx...');

        $mappings = $this->loadPsmMappings();
        $this->command->info("  Found {$mappings->count()} partner mappings in Excel.");

        // Look up PSM users by name
        $psmRole = Role::where('slug', 'psm')->first();
        $psmUsers = User::where('role_id', $psmRole->id)->get()->keyBy('name');

        $created = 0;
        $updated = 0;

        foreach ($mappings as $row) {
            $partnerName = trim($row['Partner']);
            $psmName = trim($row['PSM'] ?? '');
            $geo = trim($row['Geo'] ?? '');
            $region = trim($row['Region'] ?? '');

            if (empty($partnerName)) {
                continue;
            }

            // Resolve PSM user
            $resolvedPsmName = self::PSM_NAME_MAP[$psmName] ?? $psmName;
            $psmUser = $psmUsers->get($resolvedPsmName);

            $partnerData = [
                'user_id' => $psmUser?->id,
                'geography' => $geo ?: null,
                'region' => $region ?: null,
                'psm' => $psmUser?->name,
            ];

            $partner = Partner::where('name', $partnerName)->first();

            if ($partner) {
                $partner->update($partnerData);
                $updated++;
            } else {
                $partner = Partner::create(array_merge($partnerData, [
                    'name' => $partnerName,
                    'salesforce_id' => '00Q'.Str::upper(Str::random(15)),
                ]));
                $created++;
            }

            // Ensure PSM pivot entry exists
            if ($psmUser && ! $partner->psms()->where('user_id', $psmUser->id)->exists()) {
                $partner->psms()->attach($psmUser->id, ['role' => 'psm']);
            }
        }

        $this->command->info("  Created {$created} partners, updated {$updated}.");

        // Assign PAMs round-robin
        $this->assignPamPivotEntries();

        $unassigned = Partner::whereNull('user_id')->count();
        if ($unassigned > 0) {
            $this->command->warn("  {$unassigned} partners have PSM = 'Unassigned' in Excel (no user_id set).");
        }

        $this->command->info('Done seeding partners from Excel.');
    }

    /**
     * Load PSM mappings from the most recent snapshot Excel file.
     *
     * @return \Illuminate\Support\Collection<int, array{Partner: string, Geo: string, Region: string, PSM: string}>
     */
    private function loadPsmMappings(): \Illuminate\Support\Collection
    {
        // Use the most recent snapshot (all 3 are identical for PSM mappings)
        $path = database_path('seeders/data/3-5-2026/psm_mappings.xlsx');

        if (! file_exists($path)) {
            $this->command->error("PSM mappings file not found: {$path}");

            return collect();
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // First row is headers
        $headers = array_shift($rows);

        return collect($rows)->map(function ($row) use ($headers) {
            $mapped = [];
            foreach ($headers as $col => $header) {
                $mapped[trim($header)] = $row[$col];
            }

            return $mapped;
        })->filter(fn ($row) => ! empty(trim($row['Partner'] ?? '')));
    }

    /**
     * Distribute PAM users across partners via user_partner pivot (round-robin).
     */
    private function assignPamPivotEntries(): void
    {
        $pamRole = Role::where('slug', 'pam')->first();
        $pams = User::where('role_id', $pamRole->id)->get();

        if ($pams->isEmpty()) {
            return;
        }

        $partners = Partner::orderBy('name')->get();
        $pamIndex = 0;

        foreach ($partners as $partner) {
            $pam = $pams[$pamIndex % $pams->count()];

            if (! $partner->assignedUsers()->where('user_id', $pam->id)->exists()) {
                $partner->assignedUsers()->attach($pam->id, ['role' => 'pam']);
            }

            $pamIndex++;
        }

        $this->command->info("  Assigned {$pams->count()} PAMs across {$partners->count()} partners (round-robin).");
    }
}
