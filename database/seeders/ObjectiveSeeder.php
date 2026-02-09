<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Objective;

class ObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        Objective::factory()->count(20)->create();
    }
}