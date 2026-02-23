<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Populate phase values on activation steps that have null phase.
     *
     * Maps step_type to phase using the Tell/Show/Do methodology:
     * - training_completed → tell
     * - data_populated, feature_usage → show
     * - usage_threshold, metric_threshold, time_elapsed → do
     */
    public function up(): void
    {
        DB::table('activation_steps')
            ->whereNull('phase')
            ->where('step_type', 'training_completed')
            ->update(['phase' => 'tell']);

        DB::table('activation_steps')
            ->whereNull('phase')
            ->whereIn('step_type', ['data_populated', 'feature_usage'])
            ->update(['phase' => 'show']);

        DB::table('activation_steps')
            ->whereNull('phase')
            ->whereIn('step_type', ['usage_threshold', 'metric_threshold', 'time_elapsed'])
            ->update(['phase' => 'do']);
    }

    /**
     * Reverse is not applicable — phases were missing, no meaningful rollback.
     */
    public function down(): void
    {
        // No rollback — this fixes missing data
    }
};
