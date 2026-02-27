<?php

namespace Database\Seeders;

use App\Models\DealRegistration;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
        'psm' => [
            ['email' => 'gunnar.stoa@gmail.com', 'name' => 'Gunnar Stoa'],
            ['email' => 'sarah.chen@example.com', 'name' => 'Sarah Chen'],
            ['email' => 'marcus.johnson@example.com', 'name' => 'Marcus Johnson'],
            ['email' => 'emily.rodriguez@example.com', 'name' => 'Emily Rodriguez'],
            ['email' => 'david.kim@example.com', 'name' => 'David Kim'],
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

    /**
     * Partners explicitly assigned to Gunnar (created before PartnerSeeder).
     *
     * @var list<array{name: string, website: string, industry: string}>
     */
    private const GUNNAR_PARTNERS = [
        ['name' => 'Accenture', 'website' => 'https://www.accenture.com', 'industry' => 'Consulting'],
        ['name' => 'Deloitte', 'website' => 'https://www.deloitte.com', 'industry' => 'Consulting'],
        ['name' => 'PwC', 'website' => 'https://www.pwc.com', 'industry' => 'Consulting'],
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

        // Create Gunnar's explicit partners
        $gunnar = $createdUsers['psm'][0];

        foreach (self::GUNNAR_PARTNERS as $data) {
            $partner = Partner::updateOrCreate(
                ['name' => $data['name']],
                [
                    'user_id' => $gunnar->id,
                    'salesforce_id' => '00Q'.Str::upper(Str::random(15)),
                    'website' => $data['website'],
                    'industry' => $data['industry'],
                ]
            );

            if (! $partner->psms()->where('user_id', $gunnar->id)->exists()) {
                $partner->psms()->attach($gunnar->id, ['role' => 'psm']);
            }

            $this->command->info("  ✓ {$partner->name} → assigned to Gunnar");
        }

        // Seed deal registrations
        if (DealRegistration::count() === 0) {
            DealRegistration::factory(30)->create();
            $this->command->info('  ✓ Seeded 30 deal registrations');
        }

        // Activate all Pennant feature flags by discovering Feature classes
        $featureClasses = collect(File::files(app_path('Features')))
            ->map(fn ($file) => 'App\\Features\\'.pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($class) => class_exists($class));

        $activated = 0;
        foreach ($featureClasses as $class) {
            Feature::for(null)->activate($class);
            $activated++;
        }
        $this->command->info("  ✓ Activated {$activated} Pennant feature flags");

        // Seed directory sync connector + sample connection
        $this->call(DirectorySyncConnectorSeeder::class);

        // Summary
        $totalUsers = collect($createdUsers)->flatten()->count();
        $this->command->info("✅ Setup complete: {$totalUsers} users created across all roles");
    }
}
