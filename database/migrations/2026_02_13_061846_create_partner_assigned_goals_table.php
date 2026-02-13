<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partner_assigned_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_template_id')->nullable()->constrained('goal_templates')->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->string('metric_key');
            $table->string('unit', 50)->default('count');
            $table->string('cadence', 50)->default('quarterly');
            $table->string('fiscal_year', 10);
            $table->decimal('target_value', 14, 2)->default(0);
            $table->decimal('actual_value', 14, 2)->default(0);
            $table->decimal('q1_target', 14, 2)->nullable();
            $table->decimal('q2_target', 14, 2)->nullable();
            $table->decimal('q3_target', 14, 2)->nullable();
            $table->decimal('q4_target', 14, 2)->nullable();
            $table->decimal('q1_actual', 14, 2)->nullable();
            $table->decimal('q2_actual', 14, 2)->nullable();
            $table->decimal('q3_actual', 14, 2)->nullable();
            $table->decimal('q4_actual', 14, 2)->nullable();
            $table->string('status', 50)->default('on_track'); // on_track, at_risk, behind, exceeded, completed
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['partner_id', 'goal_template_id', 'fiscal_year'], 'pag_partner_template_fy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_assigned_goals');
    }
};
