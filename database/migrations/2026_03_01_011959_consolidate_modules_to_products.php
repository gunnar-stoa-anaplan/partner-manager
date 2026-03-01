<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidate 21 modules into 13 products.
 *
 * Remaps permission slugs, Pennant feature flags, activation paths,
 * mapping templates, and cleans up orphaned module rows.
 */
return new class extends Migration
{
    /** @var array<string, string> Old permission slug → new permission slug */
    private const PERMISSION_MAP = [
        'analytics.analysis' => 'partner_management.analytics',
        'provisioning.provisioning' => 'partner_management.provisioning',
        'blueprints.catalog' => 'enablement.blueprints',
        'blueprints.administration' => 'enablement.blueprint_admin',
        'workshops.labs' => 'enablement.workshops',
        'workshops.administration' => 'enablement.workshop_admin',
        'zoom_chat.chat' => 'enablement.zoom_chat',
        'action_plans.plans' => 'launch_programs.action_plans',
        'project_health.projects' => 'launch_programs.projects',
        'directory_sync.sync' => 'integration_hub.directory_sync',
    ];

    /** @var array<string, string> Absorbed module slug → parent product slug */
    private const PATH_MERGE_MAP = [
        'analytics' => 'partner_management',
        'provisioning' => 'partner_management',
        'blueprints' => 'enablement',
        'workshops' => 'enablement',
        'zoom_chat' => 'enablement',
        'action_plans' => 'launch_programs',
        'project_health' => 'launch_programs',
        'directory_sync' => 'integration_hub',
    ];

    /** @var array<string, string> Mapping template required_feature remap */
    private const TEMPLATE_MAP = [
        'analytics' => 'partner_management',
        'directory_sync.sync' => 'integration_hub.directory_sync',
    ];

    public function up(): void
    {
        // 1a. Remap role_permissions.module_slug
        foreach (self::PERMISSION_MAP as $old => $new) {
            DB::table('role_permissions')
                ->where('module_slug', $old)
                ->update(['module_slug' => $new]);
        }

        // 1b. Delete absorbed Pennant flags
        if (Schema::hasTable('features')) {
            DB::table('features')
                ->whereIn('name', array_keys(self::PATH_MERGE_MAP))
                ->delete();
        }

        // 1c. Merge activation paths for absorbed modules
        foreach (self::PATH_MERGE_MAP as $absorbedSlug => $parentSlug) {
            $absorbed = DB::table('activation_paths')
                ->where('feature_slug', $absorbedSlug)
                ->first();

            $parent = DB::table('activation_paths')
                ->where('feature_slug', $parentSlug)
                ->first();

            if (! $absorbed || ! $parent) {
                continue;
            }

            // Get the current max order in the parent path
            $maxOrder = DB::table('activation_steps')
                ->where('activation_path_id', $parent->id)
                ->max('order') ?? 0;

            // Move steps from absorbed path to parent path
            $steps = DB::table('activation_steps')
                ->where('activation_path_id', $absorbed->id)
                ->orderBy('order')
                ->get();

            foreach ($steps as $step) {
                DB::table('activation_steps')
                    ->where('id', $step->id)
                    ->update([
                        'activation_path_id' => $parent->id,
                        'order' => ++$maxOrder,
                    ]);
            }

            // Transfer user assignments (skip if already assigned to parent)
            $existingUserIds = DB::table('activation_path_user')
                ->where('activation_path_id', $parent->id)
                ->pluck('user_id')
                ->all();

            DB::table('activation_path_user')
                ->where('activation_path_id', $absorbed->id)
                ->whereNotIn('user_id', $existingUserIds)
                ->update(['activation_path_id' => $parent->id]);

            // Delete remaining duplicate pivot rows, then the absorbed path
            DB::table('activation_path_user')
                ->where('activation_path_id', $absorbed->id)
                ->delete();

            DB::table('activation_paths')
                ->where('id', $absorbed->id)
                ->delete();
        }

        // 1d. Remap mapping_templates.required_feature
        if (Schema::hasTable('mapping_templates')) {
            foreach (self::TEMPLATE_MAP as $old => $new) {
                DB::table('mapping_templates')
                    ->where('required_feature', $old)
                    ->update(['required_feature' => $new]);
            }
        }

        // 1e. Delete orphaned module rows
        DB::table('modules')
            ->whereIn('slug', array_keys(self::PATH_MERGE_MAP))
            ->delete();
    }

    public function down(): void
    {
        // Reverse permission slug remap
        foreach (self::PERMISSION_MAP as $old => $new) {
            DB::table('role_permissions')
                ->where('module_slug', $new)
                ->update(['module_slug' => $old]);
        }

        // Reverse mapping template remap
        if (Schema::hasTable('mapping_templates')) {
            foreach (self::TEMPLATE_MAP as $old => $new) {
                DB::table('mapping_templates')
                    ->where('required_feature', $new)
                    ->update(['required_feature' => $old]);
            }
        }

        // Note: Pennant flags, activation path merges, and deleted module rows
        // must be restored by re-running seeders:
        //   php artisan db:seed --class=ModuleSeeder
        //   php artisan db:seed --class=ActivationPathSeeder
    }
};
