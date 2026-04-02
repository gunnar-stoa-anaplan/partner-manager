<?php

namespace Database\Seeders;

use App\Models\ResourcePath;
use App\Models\ResourceTaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourceTaskTemplateSeeder extends Seeder
{
    /**
     * Task templates keyed by path name → stage number → list of templates.
     *
     * @var array<string, array<int, list<array>>>
     */
    private const TEMPLATES = [
        'Model Builder' => [
            1 => [
                ['title' => 'Complete partner resource profile', 'category' => 'enablement', 'description' => 'Fill out full profile in Partner Central including skills, experience, and availability.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 14],
                ['title' => 'Review Anaplan platform overview', 'category' => 'training', 'description' => 'Complete the self-paced platform overview course.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 21],
                ['title' => 'Enroll in Model Builder blueprint', 'category' => 'enablement', 'description' => 'Enroll in the core Model Builder learning blueprint.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 14],
            ],
            2 => [
                ['title' => 'Complete Model Builder certification prep', 'category' => 'certification', 'description' => 'Work through certification prep materials and practice exams.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 30],
                ['title' => 'Schedule and pass MB certification exam', 'category' => 'certification', 'description' => 'Register for and pass the Model Builder certification exam.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 45],
                ['title' => 'Complete first learning blueprint', 'category' => 'training', 'description' => 'Finish all modules in the assigned learning blueprint.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 60],
            ],
            3 => [
                ['title' => 'Shadow first delivery project', 'category' => 'project', 'description' => 'Observe and assist on an active project under senior guidance.', 'effort_size' => 'large', 'owner' => 'psm', 'due_days' => 30],
                ['title' => 'Complete application-specific blueprint', 'category' => 'training', 'description' => 'Finish a product-specific blueprint (IFP, SCM, etc.).', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 60],
            ],
            4 => [
                ['title' => 'Lead model build on client project', 'category' => 'delivery', 'description' => 'Take ownership of model building on a delivery engagement.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 90],
                ['title' => 'Obtain second certification', 'category' => 'certification', 'description' => 'Expand expertise by obtaining a second Anaplan certification.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 90],
                ['title' => 'Mentor a Stage 2 resource', 'category' => 'enablement', 'description' => 'Pair with a training-stage resource to share practical knowledge.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
            ],
        ],

        'Solution Architect' => [
            1 => [
                ['title' => 'Complete SA resource profile', 'category' => 'enablement', 'description' => 'Register profile with architecture background and experience.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 14],
                ['title' => 'Enroll in SA learning track', 'category' => 'training', 'description' => 'Enroll in Solution Architect blueprints and training modules.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 14],
            ],
            2 => [
                ['title' => 'Complete SA certification prep', 'category' => 'certification', 'description' => 'Work through SA-specific certification materials.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 45],
                ['title' => 'Pass SA certification exam', 'category' => 'certification', 'description' => 'Register for and pass the Solution Architect certification.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 60],
                ['title' => 'Complete solution design blueprints', 'category' => 'training', 'description' => 'Finish at least 2 solution design learning blueprints.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 75],
            ],
            3 => [
                ['title' => 'Lead solution design on project', 'category' => 'project', 'description' => 'Own the technical architecture and solution design for a delivery engagement.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 90],
                ['title' => 'Expand to second product area', 'category' => 'certification', 'description' => 'Obtain certification in a second product area for broader design capability.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 90],
            ],
            4 => [
                ['title' => 'Contribute to solution asset or IP', 'category' => 'delivery', 'description' => 'Create reusable solution template, accelerator, or methodology artifact.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Present at enablement session', 'category' => 'enablement', 'description' => 'Share architecture best practices at a partner or Anaplan enablement session.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
            ],
        ],

        'Master Anaplanner' => [
            1 => [
                ['title' => 'Verify prerequisite certifications', 'category' => 'assessment', 'description' => 'Confirm MB and SA certifications are active and current.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 14],
                ['title' => 'Submit Master Anaplanner application', 'category' => 'assessment', 'description' => 'Complete the nomination form with project portfolio and references.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 30],
            ],
            2 => [
                ['title' => 'Build delivery portfolio documentation', 'category' => 'assessment', 'description' => 'Document project history with outcomes, complexity, and client feedback.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 60],
                ['title' => 'Expand product area coverage', 'category' => 'certification', 'description' => 'Obtain certification in additional product areas to demonstrate breadth.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 90],
            ],
            3 => [
                ['title' => 'Complete Master Anaplanner assessment', 'category' => 'certification', 'description' => 'Pass the Master Anaplanner assessment exam.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 60],
                ['title' => 'Deliver complex multi-product project', 'category' => 'delivery', 'description' => 'Lead a project spanning multiple Anaplan product areas.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
            ],
            4 => [
                ['title' => 'Present at Anaplan community event', 'category' => 'enablement', 'description' => 'Share expertise at a community event, webinar, or partner summit.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Create reusable solution asset', 'category' => 'delivery', 'description' => 'Build a solution accelerator, template, or methodology guide.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
            ],
        ],

        'Sales' => [
            1 => [
                ['title' => 'Complete sales onboarding materials', 'category' => 'training', 'description' => 'Review Anaplan product overview, pricing guide, and competitive positioning.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 14],
                ['title' => 'Attend sales enablement kickoff', 'category' => 'enablement', 'description' => 'Attend initial sales enablement session with PSM.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 21],
            ],
            2 => [
                ['title' => 'Register first deal', 'category' => 'enablement', 'description' => 'Submit first deal registration through the partner portal.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 45],
                ['title' => 'Complete value selling workshop', 'category' => 'training', 'description' => 'Attend Anaplan value selling methodology workshop.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 60],
            ],
            3 => [
                ['title' => 'Build qualified pipeline to target', 'category' => 'enablement', 'description' => 'Develop and maintain pipeline of qualified Anaplan opportunities.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Close first Anaplan deal', 'category' => 'enablement', 'description' => 'Win first deal registration to Closed Won.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 90],
            ],
            4 => [
                ['title' => 'Expand into multi-product selling', 'category' => 'training', 'description' => 'Learn to position multiple Anaplan products in a single deal.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Mentor a new partner sales resource', 'category' => 'enablement', 'description' => 'Guide a newly registered partner sales rep through the enablement process.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
            ],
        ],

        'Pre-Sales' => [
            1 => [
                ['title' => 'Request demo environment access', 'category' => 'enablement', 'description' => 'Submit request for partner demo environment credentials.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 7],
                ['title' => 'Review standard demo scripts', 'category' => 'training', 'description' => 'Review standard demo talk tracks and environment walkthroughs.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 14],
            ],
            2 => [
                ['title' => 'Complete demo certification', 'category' => 'certification', 'description' => 'Pass the demo delivery qualification assessment.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 30],
                ['title' => 'Deliver supervised demo', 'category' => 'assessment', 'description' => 'Deliver a full product demo under PSM or SE observation.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 45],
                ['title' => 'Complete MB certification', 'category' => 'certification', 'description' => 'Obtain Model Builder certification for technical credibility.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 60],
            ],
            3 => [
                ['title' => 'Run independent pre-sales engagement', 'category' => 'project', 'description' => 'Lead a full pre-sales cycle: discovery, demo, POC scoping.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => 60],
                ['title' => 'Expand demo capability to second product', 'category' => 'training', 'description' => 'Learn and certify on demo for a second product area.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 90],
            ],
            4 => [
                ['title' => 'Build custom demo environment', 'category' => 'delivery', 'description' => 'Create a custom demo for a specific industry or use case.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Contribute to pre-sales playbook', 'category' => 'enablement', 'description' => 'Document discovery frameworks or objection handling for the team.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
            ],
        ],

        'Champion' => [
            1 => [
                ['title' => 'Schedule introductory alignment call', 'category' => 'enablement', 'description' => 'Meet with PSM to align on partnership goals and champion role.', 'effort_size' => 'small', 'owner' => 'psm', 'due_days' => 14],
                ['title' => 'Review partnership overview materials', 'category' => 'training', 'description' => 'Review Anaplan partnership framework and mutual value proposition.', 'effort_size' => 'small', 'owner' => 'resource', 'due_days' => 21],
            ],
            2 => [
                ['title' => 'Attend quarterly partner advisory board', 'category' => 'enablement', 'description' => 'Participate in partner advisory board or planning session.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 90],
                ['title' => 'Facilitate internal Anaplan awareness session', 'category' => 'enablement', 'description' => 'Run internal session introducing Anaplan opportunity to practice team.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => 60],
            ],
            3 => [
                ['title' => 'Drive internal resource onboarding', 'category' => 'enablement', 'description' => 'Champion the enrollment of new resources into Anaplan training paths.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Co-develop joint business plan', 'category' => 'enablement', 'description' => 'Work with PSM to develop annual joint business plan.', 'effort_size' => 'large', 'owner' => 'joint', 'due_days' => null],
            ],
            4 => [
                ['title' => 'Sponsor executive-level engagement', 'category' => 'enablement', 'description' => 'Facilitate executive-to-executive connection between partner and Anaplan leadership.', 'effort_size' => 'medium', 'owner' => 'resource', 'due_days' => null],
                ['title' => 'Present at Anaplan partner summit', 'category' => 'enablement', 'description' => 'Represent the partnership at an Anaplan partner event.', 'effort_size' => 'large', 'owner' => 'resource', 'due_days' => null],
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding resource task templates...');

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();
        $templateCount = 0;

        foreach (self::TEMPLATES as $pathName => $stageTemplates) {
            $path = ResourcePath::where('name', $pathName)->first();
            if (! $path) {
                $this->command->warn("  Path '{$pathName}' not found — skipping templates.");

                continue;
            }

            $stages = $path->stages()->orderBy('sort_order')->get()->keyBy('stage_number');

            foreach ($stageTemplates as $stageNumber => $templates) {
                $stage = $stages->get($stageNumber);
                if (! $stage) {
                    continue;
                }

                foreach ($templates as $sortOrder => $tpl) {
                    ResourceTaskTemplate::create([
                        'resource_path_id' => $path->id,
                        'stage_id' => $stage->id,
                        'category' => $tpl['category'],
                        'requirement_level' => 'required',
                        'title' => $tpl['title'],
                        'description' => $tpl['description'],
                        'effort_size' => $tpl['effort_size'],
                        'default_owner' => $tpl['owner'],
                        'due_days_offset' => $tpl['due_days'],
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
