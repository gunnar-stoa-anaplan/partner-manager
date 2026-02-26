<?php

namespace Database\Seeders;

use App\Models\GoalTemplate;
use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoalTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        if (! $admin) {
            $this->command->warn('No admin user found. Skipping GoalTemplateSeeder.');

            return;
        }

        $templates = [
            // Revenue Growth
            ['name' => 'Sourced NNACV', 'description' => 'Net new annual contract value from partner-sourced deals', 'category' => 'revenue', 'metric_key' => 'sourced_nnacv', 'default_target' => 500000, 'unit' => 'currency', 'cadence' => 'annual'],
            ['name' => 'Influenced NNACV', 'description' => 'Net new annual contract value from partner-influenced deals', 'category' => 'revenue', 'metric_key' => 'influenced_nnacv', 'default_target' => 1000000, 'unit' => 'currency', 'cadence' => 'annual'],
            ['name' => 'Pipeline Value', 'description' => 'Total open pipeline value for partner-related opportunities', 'category' => 'revenue', 'metric_key' => 'total_pipeline', 'default_target' => 3000000, 'unit' => 'currency', 'cadence' => 'quarterly'],
            ['name' => 'Win Rate', 'description' => 'Closed Won / (Closed Won + Closed Lost) percentage', 'category' => 'revenue', 'metric_key' => 'win_rate', 'default_target' => 30, 'unit' => 'percentage', 'cadence' => 'quarterly'],

            // Enablement Growth
            ['name' => 'Certified Model Builders', 'description' => 'Number of active Model Builder certifications', 'category' => 'enablement', 'metric_key' => 'certification_mb', 'default_target' => 10, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'Certified Solution Architects', 'description' => 'Number of active Solution Architect certifications', 'category' => 'enablement', 'metric_key' => 'certification_sa', 'default_target' => 5, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'Enablement Sessions Attended', 'description' => 'Number of enablement events or training sessions attended by partner contacts', 'category' => 'enablement', 'metric_key' => 'enablement_sessions', 'default_target' => 20, 'unit' => 'count', 'cadence' => 'quarterly'],

            // Product Expertise
            ['name' => 'Product Areas Covered', 'description' => 'Distinct product areas with active pipeline or completed projects', 'category' => 'product_expertise', 'metric_key' => 'product_areas_covered', 'default_target' => 3, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'Solutions Registered', 'description' => 'Partner solutions registered in the Solution Lifecycle tracker', 'category' => 'product_expertise', 'metric_key' => 'solutions_registered', 'default_target' => 2, 'unit' => 'count', 'cadence' => 'annual'],

            // Delivery Readiness
            ['name' => 'Projects Delivered', 'description' => 'Number of completed partner delivery projects', 'category' => 'delivery', 'metric_key' => 'projects_delivered', 'default_target' => 8, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'On-Time Delivery Rate', 'description' => 'Percentage of projects delivered on or before schedule', 'category' => 'delivery', 'metric_key' => 'projects_ontime_pct', 'default_target' => 85, 'unit' => 'percentage', 'cadence' => 'quarterly'],
            ['name' => 'Customer Satisfaction Score', 'description' => 'Average CSAT score from completed projects', 'category' => 'delivery', 'metric_key' => 'customer_satisfaction', 'default_target' => 4, 'unit' => 'count', 'cadence' => 'quarterly'],

            // Blueprint Completion
            ['name' => 'Blueprints Completed', 'description' => 'Number of enablement blueprints fully completed by partner contacts', 'category' => 'blueprint', 'metric_key' => 'blueprints_completed', 'default_target' => 3, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'Blueprint Items Completed', 'description' => 'Total individual blueprint content items completed across all blueprints', 'category' => 'blueprint', 'metric_key' => 'blueprint_items_completed', 'default_target' => 50, 'unit' => 'count', 'cadence' => 'quarterly'],

            // Beta Testing
            ['name' => 'Beta Program Enrollments', 'description' => 'Partner contacts enrolled in beta or early access programs', 'category' => 'beta_testing', 'metric_key' => 'beta_enrollments', 'default_target' => 5, 'unit' => 'count', 'cadence' => 'annual'],
            ['name' => 'Beta Feedback Submitted', 'description' => 'Number of beta feedback reports submitted by partner contacts', 'category' => 'beta_testing', 'metric_key' => 'beta_feedback_submitted', 'default_target' => 10, 'unit' => 'count', 'cadence' => 'quarterly'],
        ];

        foreach ($templates as $t) {
            GoalTemplate::updateOrCreate(
                ['name' => $t['name'], 'category' => $t['category']],
                array_merge($t, ['is_common' => true, 'is_active' => true, 'created_by' => $admin->id])
            );
        }

        $this->command->info('Created '.count($templates).' goal templates.');

        // Assign some goals to partners for demo
        $this->assignDemoGoals($admin);
    }

    protected function assignDemoGoals(User $admin): void
    {
        $partners = Partner::take(5)->get();
        if ($partners->isEmpty()) {
            return;
        }

        $fy = 'FY27';
        $templates = GoalTemplate::all();
        $count = 0;

        foreach ($partners as $partner) {
            // Assign 4-6 random goals per partner
            $selected = $templates->random(min(rand(4, 6), $templates->count()));

            foreach ($selected as $template) {
                $existing = PartnerAssignedGoal::where('partner_id', $partner->id)
                    ->where('goal_template_id', $template->id)
                    ->where('fiscal_year', $fy)
                    ->exists();

                if ($existing) {
                    continue;
                }

                $target = $template->default_target ?: rand(1, 100);
                $actual = round($target * (rand(20, 120) / 100), 2);

                $statuses = ['on_track', 'at_risk', 'behind', 'exceeded'];
                $pct = $target > 0 ? ($actual / $target) * 100 : 0;
                $status = match (true) {
                    $pct >= 100 => 'exceeded',
                    $pct >= 70 => 'on_track',
                    $pct >= 40 => 'at_risk',
                    default => 'behind',
                };

                PartnerAssignedGoal::create([
                    'partner_id' => $partner->id,
                    'goal_template_id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category,
                    'metric_key' => $template->metric_key,
                    'unit' => $template->unit,
                    'cadence' => $template->cadence,
                    'fiscal_year' => $fy,
                    'target_value' => $target,
                    'actual_value' => $actual,
                    'status' => $status,
                    'assigned_by' => $admin->id,
                ]);

                $count++;
            }
        }

        $this->command->info("Assigned {$count} demo goals across ".$partners->count().' partners.');
    }
}
