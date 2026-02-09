<?php

namespace Database\Factories;

use App\Models\EnablementEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnablementEventFactory extends Factory
{
    protected $model = EnablementEvent::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(array_keys(EnablementEvent::TYPES));
        $category = $this->faker->randomElement(EnablementEvent::CATEGORIES);

        $allProductAreas = [];
        foreach (EnablementEvent::PRODUCT_AREAS as $areas) {
            $allProductAreas = array_merge($allProductAreas, array_keys($areas));
        }
        $productArea = $this->faker->randomElement($allProductAreas);

        return [
            'title' => $this->generateTitle($type, $productArea),
            'type' => $type,
            'category' => $category,
            'product_area' => $productArea,
            'description' => $this->faker->paragraph(3),
            'objectives' => $this->faker->optional(0.7)->paragraph(2),
            'prerequisites' => $this->faker->optional(0.5)->sentence(),
            'target_audience' => $this->faker->randomElement([
                'Model Builders', 'Solution Architects', 'Implementation Consultants',
                'Sales Engineers', 'Pre-Sales Consultants', 'Technical Architects',
                'Project Managers', 'Delivery Leads', 'Practice Leads',
            ]),
            'duration_hours' => $this->faker->randomElement([1, 2, 4, 6, 8, 16, 24, 40]),
            'max_participants' => $this->faker->optional(0.6)->randomElement([10, 15, 20, 25, 30, 50]),
            'materials_url' => null,
            'status' => 'published',
            'created_by' => User::where('role', 'admin')->first()?->id ?? 1,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn() => ['status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(fn() => ['status' => 'archived']);
    }

    private function generateTitle(string $type, string $productArea): string
    {
        $templates = [
            'workshop' => [
                '{product} Hands-On Workshop',
                '{product} Advanced Modeling Workshop',
                '{product} Best Practices Workshop',
                '{product} Solution Design Workshop',
            ],
            'training' => [
                '{product} Fundamentals Training',
                '{product} Advanced Training',
                '{product} Implementation Training',
                '{product} Configuration Training',
                '{product} Admin Training',
            ],
            'certification_exam' => [
                '{product} Certification Prep',
                '{product} Level 1 Certification',
                '{product} Level 2 Certification',
                '{product} Master Certification',
            ],
            'webinar' => [
                '{product} Product Update Webinar',
                '{product} New Features Overview',
                '{product} Customer Success Stories',
                '{product} Roadmap Briefing',
            ],
            'bootcamp' => [
                '{product} Bootcamp',
                '{product} Intensive Bootcamp',
                '{product} Delivery Readiness Bootcamp',
                '{product} Fast-Track Bootcamp',
            ],
            'onboarding' => [
                '{product} Partner Onboarding',
                '{product} New Consultant Onboarding',
                '{product} Getting Started with {product}',
            ],
            'hackathon' => [
                '{product} Innovation Hackathon',
                '{product} Solution Challenge',
                '{product} Build-a-thon',
            ],
            'conference' => [
                '{product} Partner Summit',
                '{product} Technical Conference',
                '{product} Community Day',
            ],
            'other' => [
                '{product} Knowledge Share',
                '{product} Office Hours',
                '{product} Deep Dive',
            ],
        ];

        $pool = $templates[$type] ?? $templates['other'];
        $template = $this->faker->randomElement($pool);

        return str_replace('{product}', $productArea, $template);
    }
}
