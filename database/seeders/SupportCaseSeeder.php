<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Project;
use App\Models\SupportCase;
use Illuminate\Database\Seeder;

class SupportCaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating support cases...');

        $partners = Partner::all();
        $count = 0;

        $categories = [
            'Implementation Issue',
            'Model Performance',
            'Data Integration',
            'User Access',
            'Configuration Error',
            'Migration Issue',
            'Custom Module',
            'Platform Bug',
        ];

        $subjectTemplates = [
            'low' => [
                'Minor formatting issue in {area} report',
                'Question about {area} configuration option',
                'Documentation clarification for {area}',
            ],
            'medium' => [
                'Data sync delay in {area} module',
                'Calculation discrepancy in {area} model',
                '{area} dashboard performance degradation',
                'Import validation error in {area}',
            ],
            'high' => [
                '{area} module not loading for multiple users',
                'Incorrect data in {area} — customer-impacting',
                'Scheduled {area} job failures',
                'Permission issue blocking {area} access',
            ],
            'critical' => [
                '{area} module completely down — production',
                'Data loss in {area} — urgent recovery needed',
                'Customer-facing {area} outage — SLA at risk',
            ],
        ];

        $areas = ['IFP', 'SCM', 'SPM', 'Revenue Planning', 'Workforce Planning', 'Consolidation', 'Polaris', 'Platform'];

        foreach ($partners as $partner) {
            // Each partner has 3-10 support cases
            $caseCount = fake()->numberBetween(3, 10);
            $projects = Project::where('partner_id', $partner->id)->get();

            for ($i = 0; $i < $caseCount; $i++) {
                // Weighted severity — most cases medium/low
                $severity = fake()->randomElement(array_merge(
                    array_fill(0, 4, 'low'),
                    array_fill(0, 5, 'medium'),
                    array_fill(0, 2, 'high'),
                    array_fill(0, 1, 'critical'),
                ));

                $area = fake()->randomElement($areas);
                $subject = str_replace('{area}', $area, fake()->randomElement($subjectTemplates[$severity]));

                $openedDate = fake()->dateTimeBetween('-12 months', 'now');
                $isResolved = fake()->boolean($severity === 'critical' ? 90 : 70);

                $resolvedDate = $isResolved
                    ? fake()->dateTimeBetween($openedDate, 'now')
                    : null;

                $resolutionDays = $resolvedDate
                    ? (int) $openedDate->diff($resolvedDate)->days
                    : null;

                $escalated = $severity === 'critical'
                    ? fake()->boolean(80)
                    : ($severity === 'high' ? fake()->boolean(30) : fake()->boolean(5));

                $status = match (true) {
                    $isResolved && fake()->boolean(70) => 'closed',
                    $isResolved => 'resolved',
                    $escalated => 'escalated',
                    fake()->boolean(40) => 'in_progress',
                    default => 'open',
                };

                SupportCase::create([
                    'partner_id' => $partner->id,
                    'project_id' => $projects->isNotEmpty() ? $projects->random()->id : null,
                    'account_name' => fake()->company(),
                    'case_number' => 'CS-' . fake()->unique()->numerify('######'),
                    'subject' => $subject,
                    'severity' => $severity,
                    'status' => $status,
                    'category' => fake()->randomElement($categories),
                    'opened_date' => $openedDate,
                    'resolved_date' => $resolvedDate,
                    'escalated' => $escalated,
                    'escalation_reason' => $escalated ? fake()->randomElement([
                        'Customer SLA breach risk',
                        'Multiple users impacted',
                        'Data integrity concern',
                        'Executive visibility required',
                        'Repeated occurrence — systemic issue',
                    ]) : null,
                    'resolution_notes' => $isResolved ? fake()->sentence(8) : null,
                    'resolution_days' => $resolutionDays,
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} support cases.");
    }
}
