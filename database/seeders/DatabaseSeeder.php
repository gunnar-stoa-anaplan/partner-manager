<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Contact;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call([
            GunnarSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            ProjectSeeder::class,
            PartnerSeeder::class,
            ContactSeeder::class,
            ObjectiveSeeder::class,
            LeadSeeder::class,
            AccentureSeeder::class,
        ]);
                
    }
}
