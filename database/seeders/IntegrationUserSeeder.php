<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class IntegrationUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'integration@example.com'],
            [
                'name' => 'Integration Hub',
                'password' => bcrypt('password'),
                'role' => 'integration',
            ]
        );

        $this->command->info('Created integration user (integration@example.com / password)');
    }
}
