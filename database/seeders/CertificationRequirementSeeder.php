<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CertificationRequirement;
use App\Models\PartnerPath;

class CertificationRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $path = PartnerPath::where('is_default', true)->first();
        if (!$path) {
            return;
        }

        $stages = $path->stages()->pluck('id', 'stage_number');

        // Stage 2 (Enabled) requirements
        if ($stages->has(2)) {
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[2],
                'certification_type' => 'model_builder',
                'minimum_count' => 2,
                'description' => 'At least 2 certified Model Builders required for Enabled stage',
            ]);
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[2],
                'certification_type' => 'solution_architect',
                'minimum_count' => 1,
                'description' => 'At least 1 certified Solution Architect required for Enabled stage',
            ]);
        }

        // Stage 3 (Performing) requirements
        if ($stages->has(3)) {
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[3],
                'certification_type' => 'model_builder',
                'minimum_count' => 3,
                'description' => 'At least 3 certified Model Builders for Performing stage',
            ]);
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[3],
                'certification_type' => 'solution_architect',
                'minimum_count' => 1,
                'description' => 'At least 1 certified Solution Architect for Performing stage',
            ]);
        }

        // Stage 4 (Accelerating) requirements
        if ($stages->has(4)) {
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[4],
                'certification_type' => 'model_builder',
                'minimum_count' => 5,
                'description' => 'At least 5 certified Model Builders for Accelerating stage',
            ]);
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[4],
                'certification_type' => 'solution_architect',
                'minimum_count' => 3,
                'description' => 'At least 3 certified Solution Architects for Accelerating stage',
            ]);
        }

        // Stage 5 (Strategic) requirements
        if ($stages->has(5)) {
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[5],
                'certification_type' => 'model_builder',
                'minimum_count' => 10,
                'description' => 'At least 10 certified Model Builders for Strategic stage',
            ]);
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[5],
                'certification_type' => 'solution_architect',
                'minimum_count' => 5,
                'description' => 'At least 5 certified Solution Architects for Strategic stage',
            ]);
            CertificationRequirement::create([
                'partner_path_id' => $path->id,
                'stage_id' => $stages[5],
                'certification_type' => 'master_anaplanner',
                'minimum_count' => 1,
                'description' => 'At least 1 Master Anaplanner for Strategic stage',
            ]);
        }
    }
}
