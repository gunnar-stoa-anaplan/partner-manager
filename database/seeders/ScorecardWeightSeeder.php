<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScorecardWeight;
use App\Models\User;

class ScorecardWeightSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->first()?->id;

        $weights = [
            ['dimension' => 'certification', 'weight' => 0.15],
            ['dimension' => 'pipeline', 'weight' => 0.20],
            ['dimension' => 'delivery', 'weight' => 0.20],
            ['dimension' => 'engagement', 'weight' => 0.15],
            ['dimension' => 'growth', 'weight' => 0.20],
            ['dimension' => 'self_sufficiency', 'weight' => 0.10],
        ];

        foreach ($weights as $weight) {
            ScorecardWeight::updateOrCreate(
                ['dimension' => $weight['dimension']],
                array_merge($weight, [
                    'is_active' => true,
                    'updated_by' => $adminId,
                ])
            );
        }
    }
}
