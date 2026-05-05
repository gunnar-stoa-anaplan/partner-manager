<?php

namespace Database\Seeders;

use App\Models\DealRegistration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Laravel\Pennant\Feature;

class GunnarSeeder extends Seeder
{
    /**
     * All internal users to seed, grouped by role slug.
     *
     * @var array<string, list<array{email: string, name: string}>>
     */
    private const USERS = [
        'super_admin' => [
            ['email' => 'superadmin@example.com', 'name' => 'Alex Mercer'],
        ],
        'admin' => [
            ['email' => 'admin@example.com', 'name' => 'Jordan Mitchell'],
        ],
        'manager' => [
            ['email' => 'bikram.sen@example.com', 'name' => 'Bikram Sen'],
            ['email' => 'ray.curbelo@example.com', 'name' => 'Ray Curbelo'],
        ],
        'coe' => [
            ['email' => 'jennifer.blake@example.com', 'name' => 'Jennifer Blake'],
            ['email' => 'kevin.nakamura@example.com', 'name' => 'Kevin Nakamura'],
            ['email' => 'diana.costa@example.com', 'name' => 'Diana Costa'],
        ],
        'integration' => [
            ['email' => 'integration@example.com', 'name' => 'Integration Hub'],
        ],
        'journey_owner' => [
            ['email' => 'journey.owner@example.com', 'name' => 'Maya Chen'],
        ],
        'psm' => [
            ['email' => 'gunnar.stoa@gmail.com', 'name' => 'Gunnar Stoa'],
            ['email' => 'rick.palmer@example.com', 'name' => 'Rick Palmer'],
            ['email' => 'nicole.demarey@example.com', 'name' => 'Nicole Demarey'],
            ['email' => 'ben.wilson@example.com', 'name' => 'Ben Wilson'],
        ],
        'pam' => [
            ['email' => 'rachel.morrison@example.com', 'name' => 'Rachel Morrison'],
            ['email' => 'james.obrien@example.com', 'name' => "James O'Brien"],
            ['email' => 'priya.sharma@example.com', 'name' => 'Priya Sharma'],
            ['email' => 'michael.torres@example.com', 'name' => 'Michael Torres'],
            ['email' => 'lisa.wang@example.com', 'name' => 'Lisa Wang'],
            ['email' => 'chris.anderson@example.com', 'name' => 'Chris Anderson'],
            ['email' => 'nina.patel@example.com', 'name' => 'Nina Patel'],
            ['email' => 'robert.hayes@example.com', 'name' => 'Robert Hayes'],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Creating all internal users...');

        $roles = Role::internal()->pluck('id', 'slug');
        $createdUsers = [];

        foreach (self::USERS as $roleSlug => $users) {
            foreach ($users as $userData) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => bcrypt('password'),
                        'role_id' => $roles[$roleSlug],
                    ]
                );
                $createdUsers[$roleSlug][] = $user;
                $this->command->info("  ✓ {$userData['name']} ({$userData['email']}) → {$roleSlug}");
            }
        }

        // Assign reporting hierarchy: PSMs → Bikram, PAMs → Ray
        $bikram = $createdUsers['manager'][0];
        $ray = $createdUsers['manager'][1];

        foreach ($createdUsers['psm'] as $psm) {
            $psm->update(['manager_id' => $bikram->id]);
        }
        $this->command->info("  ✓ All PSMs report to {$bikram->name}");

        foreach ($createdUsers['pam'] as $pam) {
            $pam->update(['manager_id' => $ray->id]);
        }
        $this->command->info("  ✓ All PAMs report to {$ray->name}");

        // Assign the Journey Owner (Maya Chen) to all existing journeys.
        if (! empty($createdUsers['journey_owner'])) {
            $maya = $createdUsers['journey_owner'][0];
            $journeyIds = \App\Models\Journey::query()->pluck('id');
            foreach ($journeyIds as $journeyId) {
                \App\Models\JourneyAssignment::firstOrCreate([
                    'journey_id' => $journeyId,
                    'user_id' => $maya->id,
                ]);
            }
            $this->command->info("  ✓ Assigned {$maya->name} to {$journeyIds->count()} journey(s)");
        }

        // Seed deal registrations (partners are now created by PartnerSeeder from Excel data)
        if (DealRegistration::count() === 0) {
            DealRegistration::factory(30)->create();
            $this->command->info('  ✓ Seeded 30 deal registrations');
        }

        // Purge stale feature flags and activate all current Feature classes
        Feature::purge();

        $featureClasses = collect(File::files(app_path('Features')))
            ->map(fn ($file) => 'App\\Features\\'.pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($class) => class_exists($class));

        foreach ($featureClasses as $class) {
            Feature::for(null)->activate($class);
        }
        $this->command->info("  ✓ Activated {$featureClasses->count()} Pennant feature flags");

        // Seed directory sync connector + sample connection
        $this->call(DirectorySyncConnectorSeeder::class);

        // Summary
        $totalUsers = collect($createdUsers)->flatten()->count();
        $this->command->info("✅ Setup complete: {$totalUsers} users created across all roles");
    }
}
