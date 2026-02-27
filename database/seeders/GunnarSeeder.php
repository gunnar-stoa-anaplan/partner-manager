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
    public function run(): void
    {
        $this->command->info('Creating admin and Gunnar users...');

        // Pre-resolve internal role IDs
        $roles = Role::internal()->pluck('id', 'slug');

        // Create Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => $roles['admin'],
            ]
        );
        $this->command->info('✓ Created/Updated Admin user (admin@example.com / password)');

        // Create role-specific test users for dev switcher
        $roleUsers = [
            ['email' => 'superadmin@example.com', 'name' => 'Super Admin', 'role_slug' => 'super_admin'],
            ['email' => 'manager@example.com', 'name' => 'Manager User', 'role_slug' => 'manager'],
            ['email' => 'coe@example.com', 'name' => 'CoE User', 'role_slug' => 'coe'],
            ['email' => 'integration@example.com', 'name' => 'Integration User', 'role_slug' => 'integration'],
        ];

        foreach ($roleUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'role_id' => $roles[$userData['role_slug']],
                ]
            );
            $this->command->info("✓ Created/Updated {$userData['name']} ({$userData['email']} / password)");
        }

        // Create Gunnar Stoa as PSM user
        $gunnar = User::updateOrCreate(
            ['email' => 'gunnar.stoa@gmail.com'],
            [
                'name' => 'Gunnar Stoa',
                'password' => bcrypt('password'),
                'role_id' => $roles['psm'],
            ]
        );

        // Create specific partners assigned to Gunnar
        $partnerData = [
            [
                'name' => 'Accenture',
                'website' => 'https://www.accenture.com',
                'industry' => 'Consulting',
            ],
            [
                'name' => 'Deloitte',
                'website' => 'https://www.deloitte.com',
                'industry' => 'Consulting',
            ],
            [
                'name' => 'PwC',
                'website' => 'https://www.pwc.com',
                'industry' => 'Consulting',
            ],
        ];

        foreach ($partnerData as $data) {
            $partner = Partner::updateOrCreate(
                ['name' => $data['name']],
                [
                    'user_id' => $gunnar->id,
                    'salesforce_id' => '00Q'.Str::upper(Str::random(15)),
                    'website' => $data['website'],
                    'industry' => $data['industry'],
                ]
            );

            // Assign partner to Gunnar via user_partner pivot table (many-to-many)
            if (! $partner->psms()->where('user_id', $gunnar->id)->exists()) {
                $partner->psms()->attach($gunnar->id, ['role' => 'psm']);
            }

            $this->command->info("✓ Created/Updated {$partner->name} and assigned to Gunnar");
        }

        // Seed deal registrations
        if (DealRegistration::count() === 0) {
            DealRegistration::factory(30)->create();
            $this->command->info('✓ Seeded 30 deal registrations');
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
        $this->command->info("✓ Activated all {$activated} Pennant feature flags");

        // Seed directory sync connector + sample connection
        $this->call(DirectorySyncConnectorSeeder::class);

        $this->command->info('✅ Gunnar setup complete!');
        $this->command->info("   Email: {$gunnar->email}");
        $this->command->info("   Role: {$gunnar->role?->display_name}");
        $this->command->info('   Assigned Partners: '.$gunnar->psmPartners()->count());
    }
}
