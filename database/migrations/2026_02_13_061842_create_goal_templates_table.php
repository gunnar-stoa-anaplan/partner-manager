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
        Schema::create('goal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // revenue, enablement, product_expertise, delivery, blueprint, beta_testing
            $table->string('metric_key'); // sourced_nnacv, influenced_nnacv, certification_mb, etc.
            $table->decimal('default_target', 14, 2)->nullable();
            $table->string('unit', 50)->default('count'); // currency, count, percentage
            $table->string('cadence', 50)->default('quarterly'); // weekly, monthly, quarterly, annual
            $table->boolean('is_common')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_templates');
    }
};
