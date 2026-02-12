<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $partner = Partner::inRandomOrder()->first() ?? Partner::factory()->create();

        return [
            'partner_id' => $partner->id,
            'account_id' => Account::inRandomOrder()->first()?->id ?? Account::factory(),
            'opportunity_id' => Opportunity::factory()->stage('Closed Won')->forPartner($partner->id),
            'name' => $this->faker->catchPhrase,
            'start_date' => $this->faker->date('Y-m-d', 'now'),
            'cal_id' => Contact::inRandomOrder()->first()?->id ?? Contact::factory(),
            'tal_id' => Contact::inRandomOrder()->first()?->id ?? Contact::factory(),
            'ae_id' => Contact::inRandomOrder()->first()?->id ?? Contact::factory(),
            'acv' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement(['Draft', 'In Progress', 'Completed', 'On Hold']),
            'percent_complete' => $this->faker->numberBetween(0, 100),
            'total_sprints' => $this->faker->numberBetween(1, 10),
            'current_sprint' => $this->faker->numberBetween(1, 5),
            'delivery_team' => json_encode($this->faker->words(rand(1, 4))),
        ];
    }
}
