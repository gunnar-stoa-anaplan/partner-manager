<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GunnarSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating admin and Gunnar users...');

        // Create Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
        $this->command->info('✓ Created/Updated Admin user (admin@example.com / password)');

        // Create role-specific test users for dev switcher
        $roleUsers = [
            ['email' => 'superadmin@example.com', 'name' => 'Super Admin', 'role' => 'super_admin'],
            ['email' => 'manager@example.com', 'name' => 'Manager User', 'role' => 'manager'],
            ['email' => 'coe@example.com', 'name' => 'CoE User', 'role' => 'coe'],
            ['email' => 'integration@example.com', 'name' => 'Integration User', 'role' => 'integration'],
        ];

        foreach ($roleUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'role' => $userData['role'],
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
                'role' => 'psm', // Set as PSM
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

        $this->command->info('✅ Gunnar setup complete!');
        $this->command->info("   Email: {$gunnar->email}");
        $this->command->info("   Role: {$gunnar->role}");
        $this->command->info('   Assigned Partners: '.$gunnar->psmPartners()->count());
    }
}
