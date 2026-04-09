<?php

namespace Database\Seeders;

use App\Models\ResourcePath;
use App\Models\ResourceTaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourceTaskTemplateSeeder extends Seeder
{
    /**
     * Task templates by role type, keyed by stage number.
     * {context} is replaced with the path name at seed time.
     *
     * @var array<string, array<int, list<array>>>
     */
    private const ROLE_TEMPLATES = [
        'delivery' => [
            1 => [
                ['title' => 'Complete resource profile', 'category' => 'enablement', 'description' => 'Fill out profile in Partner Central including skills, experience, and availability for {context}.', 'effort' => 'small', 'owner' => 'resource', 'days' => 14],
                ['title' => 'Review product overview', 'category' => 'training', 'description' => 'Complete the self-paced {context} product overview course.', 'effort' => 'small', 'owner' => 'resource', 'days' => 21],
                ['title' => 'Enroll in learning blueprint', 'category' => 'enablement', 'description' => 'Enroll in the {context} learning blueprint.', 'effort' => 'small', 'owner' => 'psm', 'days' => 14],
            ],
            2 => [
                ['title' => 'Complete certification prep', 'category' => 'certification', 'description' => 'Work through {context} certification prep materials and practice exams.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 30],
                ['title' => 'Pass certification exam', 'category' => 'certification', 'description' => 'Register for and pass the {context} certification exam.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 45],
                ['title' => 'Complete learning blueprint', 'category' => 'training', 'description' => 'Finish all modules in the assigned {context} blueprint.', 'effort' => 'large', 'owner' => 'resource', 'days' => 60],
            ],
            3 => [
                ['title' => 'Shadow first project', 'category' => 'project', 'description' => 'Observe and assist on an active {context} project under senior guidance.', 'effort' => 'large', 'owner' => 'psm', 'days' => 30],
                ['title' => 'Complete product-specific blueprint', 'category' => 'training', 'description' => 'Finish a {context}-specific advanced blueprint.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 60],
            ],
            4 => [
                ['title' => 'Lead delivery on project', 'category' => 'delivery', 'description' => 'Take ownership of {context} delivery on a client engagement.', 'effort' => 'large', 'owner' => 'resource', 'days' => 90],
                ['title' => 'Obtain additional certification', 'category' => 'certification', 'description' => 'Expand expertise by obtaining an additional {context}-related certification.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 90],
                ['title' => 'Mentor a junior resource', 'category' => 'enablement', 'description' => 'Pair with a training-stage resource to share practical {context} knowledge.', 'effort' => 'medium', 'owner' => 'resource'],
            ],
        ],

        'sales' => [
            1 => [
                ['title' => 'Complete sales onboarding', 'category' => 'training', 'description' => 'Review {context} product overview, pricing guide, and competitive positioning.', 'effort' => 'small', 'owner' => 'resource', 'days' => 14],
                ['title' => 'Attend sales enablement kickoff', 'category' => 'enablement', 'description' => 'Attend initial {context} sales enablement session with PSM.', 'effort' => 'small', 'owner' => 'psm', 'days' => 21],
            ],
            2 => [
                ['title' => 'Register first deal', 'category' => 'enablement', 'description' => 'Submit first {context} deal registration through the partner portal.', 'effort' => 'small', 'owner' => 'resource', 'days' => 45],
                ['title' => 'Complete value selling workshop', 'category' => 'training', 'description' => 'Attend {context} value selling methodology workshop.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 60],
            ],
            3 => [
                ['title' => 'Build qualified pipeline', 'category' => 'enablement', 'description' => 'Develop and maintain pipeline of qualified {context} opportunities.', 'effort' => 'large', 'owner' => 'resource'],
                ['title' => 'Close first deal', 'category' => 'enablement', 'description' => 'Win first {context} deal registration to Closed Won.', 'effort' => 'large', 'owner' => 'resource', 'days' => 90],
            ],
            4 => [
                ['title' => 'Expand into multi-product selling', 'category' => 'training', 'description' => 'Learn to position {context} alongside other Anaplan products in a single deal.', 'effort' => 'medium', 'owner' => 'resource'],
                ['title' => 'Mentor a new sales resource', 'category' => 'enablement', 'description' => 'Guide a newly registered partner sales rep through {context} enablement.', 'effort' => 'medium', 'owner' => 'resource'],
            ],
        ],

        'pre-sales' => [
            1 => [
                ['title' => 'Request demo environment access', 'category' => 'enablement', 'description' => 'Submit request for {context} demo environment credentials.', 'effort' => 'small', 'owner' => 'psm', 'days' => 7],
                ['title' => 'Review standard demo scripts', 'category' => 'training', 'description' => 'Review standard {context} demo talk tracks and environment walkthroughs.', 'effort' => 'small', 'owner' => 'resource', 'days' => 14],
            ],
            2 => [
                ['title' => 'Complete demo certification', 'category' => 'certification', 'description' => 'Pass the {context} demo delivery qualification assessment.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 30],
                ['title' => 'Deliver supervised demo', 'category' => 'assessment', 'description' => 'Deliver a full {context} product demo under PSM or SE observation.', 'effort' => 'medium', 'owner' => 'resource', 'days' => 45],
            ],
            3 => [
                ['title' => 'Run independent pre-sales engagement', 'category' => 'project', 'description' => 'Lead a full {context} pre-sales cycle: discovery, demo, POC scoping.', 'effort' => 'large', 'owner' => 'resource', 'days' => 60],
            ],
            4 => [
                ['title' => 'Build custom demo environment', 'category' => 'delivery', 'description' => 'Create a custom {context} demo for a specific industry or use case.', 'effort' => 'large', 'owner' => 'resource'],
                ['title' => 'Contribute to pre-sales playbook', 'category' => 'enablement', 'description' => 'Document {context} discovery frameworks or objection handling for the team.', 'effort' => 'medium', 'owner' => 'resource'],
            ],
        ],
    ];

    /**
     * Detect which role type a path uses based on its name suffix.
     */
    private function detectRoleType(string $pathName): ?string
    {
        $lower = strtolower($pathName);

        // Exact matches for general paths
        if (in_array($pathName, ['Sales', 'Pre-Sales'])) {
            return $pathName === 'Sales' ? 'sales' : 'pre-sales';
        }

        // Suffix-based detection for product paths
        if (str_ends_with($lower, ': delivery') || str_ends_with($lower, ' delivery')) {
            return 'delivery';
        }
        if (str_ends_with($lower, ': sales')) {
            return 'sales';
        }
        if (str_ends_with($lower, ': pre-sales')) {
            return 'pre-sales';
        }

        // General technical paths use delivery templates
        if (in_array($pathName, ['Model Builder', 'Solution Architect'])) {
            return 'delivery';
        }

        return null;
    }

    public function run(): void
    {
        $this->command->info('Seeding resource task templates...');

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();
        $templateCount = 0;

        $paths = ResourcePath::with(['stages' => fn ($q) => $q->orderBy('sort_order')])->get();

        foreach ($paths as $path) {
            $roleType = $this->detectRoleType($path->name);
            if (! $roleType || ! isset(self::ROLE_TEMPLATES[$roleType])) {
                continue;
            }

            $stages = $path->stages->keyBy('stage_number');
            $templates = self::ROLE_TEMPLATES[$roleType];

            // Use the path name as context, stripping the role suffix for cleaner descriptions
            $context = preg_replace('/:\s*(Delivery|Sales|Pre-Sales)$/i', '', $path->name);

            foreach ($templates as $stageNumber => $stageTemplates) {
                $stage = $stages->get($stageNumber);
                if (! $stage) {
                    continue;
                }

                foreach ($stageTemplates as $sortOrder => $tpl) {
                    ResourceTaskTemplate::create([
                        'resource_path_id' => $path->id,
                        'stage_id' => $stage->id,
                        'category' => $tpl['category'],
                        'requirement_level' => 'required',
                        'title' => str_replace('{context}', $context, $tpl['title']),
                        'description' => str_replace('{context}', $context, $tpl['description']),
                        'effort_size' => $tpl['effort'],
                        'default_owner' => $tpl['owner'],
                        'due_days_offset' => $tpl['days'] ?? null,
                        'evidence_type' => 'psm_attestation',
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                        'created_by' => $admin->id,
                    ]);
                    $templateCount++;
                }
            }
        }

        $this->command->info("  Created {$templateCount} resource task templates.");
    }
}
