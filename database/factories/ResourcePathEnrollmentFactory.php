<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\ResourcePath;
use App\Models\ResourcePathEnrollment;
use App\Models\ResourcePathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourcePathEnrollmentFactory extends Factory
{
    protected $model = ResourcePathEnrollment::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'partner_id' => Partner::factory(),
            'resource_path_id' => ResourcePath::factory(),
            'current_stage_id' => ResourcePathStage::factory(),
            'enrolled_at' => now()->subDays(30),
            'stage_entered_at' => now()->subDays(15),
            'status' => 'active',
        ];
    }
}
