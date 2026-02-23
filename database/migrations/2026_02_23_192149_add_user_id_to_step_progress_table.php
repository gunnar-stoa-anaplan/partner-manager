<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate existing instance-level progress — must be re-seeded per-user
        DB::table('step_progress')->truncate();

        Schema::table('step_progress', function (Blueprint $table) {
            // Must drop the FK before the unique index it depends on
            $table->dropForeign('step_progress_activation_step_id_foreign');
            $table->dropUnique('step_progress_activation_step_id_unique');
        });

        Schema::table('step_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->after('activation_step_id')
                ->constrained()->cascadeOnDelete();

            // Re-add FK + composite unique
            $table->foreign('activation_step_id')
                ->references('id')->on('activation_steps')
                ->cascadeOnDelete();
            $table->unique(['activation_step_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('step_progress', function (Blueprint $table) {
            $table->dropUnique(['activation_step_id', 'user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique('activation_step_id');
        });
    }
};
