<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategy_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('fiscal_year');
            $table->string('period_type');
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('committed_at')->nullable();
            $table->foreignId('committed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_plan_id')->nullable()->constrained('strategy_plans')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('strategy_plan_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_plan_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('metric_key');
            $table->string('metric_label');
            $table->string('unit');
            $table->decimal('target_value', 15, 2)->default(0);
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->decimal('q1_target', 15, 2)->default(0);
            $table->decimal('q2_target', 15, 2)->default(0);
            $table->decimal('q3_target', 15, 2)->default(0);
            $table->decimal('q4_target', 15, 2)->default(0);
            $table->decimal('q1_actual', 15, 2)->nullable();
            $table->decimal('q2_actual', 15, 2)->nullable();
            $table->decimal('q3_actual', 15, 2)->nullable();
            $table->decimal('q4_actual', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_plan_assumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_plan_id')->constrained()->cascadeOnDelete();
            $table->string('assumption_key');
            $table->string('label');
            $table->decimal('planned_value', 10, 4);
            $table->decimal('actual_value', 10, 4)->nullable();
            $table->string('unit');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_plan_initiatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_plan_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('status')->default('planned');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('expected_impact')->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_plan_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_plan_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('label');
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->decimal('q1_planned', 15, 2)->default(0);
            $table->decimal('q2_planned', 15, 2)->default(0);
            $table->decimal('q3_planned', 15, 2)->default(0);
            $table->decimal('q4_planned', 15, 2)->default(0);
            $table->decimal('q1_actual', 15, 2)->nullable();
            $table->decimal('q2_actual', 15, 2)->nullable();
            $table->decimal('q3_actual', 15, 2)->nullable();
            $table->decimal('q4_actual', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('base_plan_id')->constrained('strategy_plans')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('strategy_scenario_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_scenario_id')->constrained()->cascadeOnDelete();
            $table->string('override_type');
            $table->unsignedBigInteger('reference_id');
            $table->decimal('original_value', 15, 4);
            $table->decimal('override_value', 15, 4);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_plan_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_plan_id')->constrained()->cascadeOnDelete();
            $table->string('review_type');
            $table->date('review_date');
            $table->text('summary')->nullable();
            $table->foreignId('reviewed_by')->constrained('users');
            $table->string('status')->default('scheduled');
            $table->json('findings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategy_plan_reviews');
        Schema::dropIfExists('strategy_scenario_overrides');
        Schema::dropIfExists('strategy_scenarios');
        Schema::dropIfExists('strategy_plan_budgets');
        Schema::dropIfExists('strategy_plan_initiatives');
        Schema::dropIfExists('strategy_plan_assumptions');
        Schema::dropIfExists('strategy_plan_targets');
        Schema::dropIfExists('strategy_plans');
    }
};
