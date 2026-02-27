<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Database\Factories\ContactFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    protected static array $geoRegionMap = [
        'Americas' => ['North America', 'Latin America', 'Canada'],
        'EMEA' => ['Northern Europe', 'Southern Europe', 'Middle East & Africa', 'UK & Ireland'],
        'APAC' => ['ANZ', 'Japan', 'Southeast Asia', 'India'],
    ];

    public function run(): void
    {
        $existingNames = Partner::pluck('name')->toArray();

        // Get PSM users to assign as primary owners
        $psmRole = Role::where('slug', 'psm')->first();
        $psms = User::where('role_id', $psmRole->id)->get();

        // Use known partner names, skipping any already seeded (e.g. by GunnarSeeder)
        $knownNames = array_keys(ContactFactory::KNOWN_PARTNERS);
        $namesToCreate = collect($knownNames)
            ->reject(fn (string $name) => in_array($name, $existingNames))
            ->values();

        // Round-robin assign remaining partners to PSMs (skip index 0 = Gunnar, who already has 3)
        $psmIndex = 1;
        foreach ($namesToCreate as $name) {
            $geography = array_rand(self::$geoRegionMap);
            $region = self::$geoRegionMap[$geography][array_rand(self::$geoRegionMap[$geography])];
            $owner = $psms[$psmIndex % $psms->count()];

            Partner::factory()->create([
                'name' => $name,
                'user_id' => $owner->id,
                'salesforce_id' => '00Q'.Str::upper(Str::random(15)),
                'geography' => $geography,
                'region' => $region,
            ]);

            $psmIndex++;
        }

        // Backfill any existing partners missing geography/region
        Partner::whereNull('geography')->orWhereNull('region')->each(function ($partner) {
            $geos = array_keys(self::$geoRegionMap);
            $geography = $geos[array_rand($geos)];
            $regions = self::$geoRegionMap[$geography];
            $region = $regions[array_rand($regions)];

            $partner->update([
                'geography' => $geography,
                'region' => $region,
            ]);
        });

        // Assign PSMs and PAMs via user_partner pivot
        $this->assignPsmPivotEntries($psms);
        $this->assignPamPivotEntries();
    }

    /**
     * Create user_partner pivot entries for PSM assignments.
     */
    private function assignPsmPivotEntries($psms): void
    {
        $partners = Partner::orderBy('name')->get();

        foreach ($partners as $partner) {
            // The owner (user_id) should also have a PSM pivot entry
            $owner = $psms->firstWhere('id', $partner->user_id);
            if ($owner && ! $partner->psms()->where('user_id', $owner->id)->exists()) {
                $partner->psms()->attach($owner->id, ['role' => 'psm']);
            }
        }
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
    }
}
