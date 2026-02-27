<?php

namespace Database\Seeders;

use App\Models\Role;
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
                'role_id' => Role::where('slug', 'integration')->value('id'),
            ]
        );

        $this->command->info('Created integration user (integration@example.com / password)');
    }
}
