<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Gunnar is already created by GunnarSeeder, so only create additional users
        User::factory()->count(4)->create();
    }
}