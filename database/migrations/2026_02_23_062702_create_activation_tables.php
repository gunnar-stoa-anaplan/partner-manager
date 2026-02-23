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
        Schema::create('activation_paths', function (Blueprint $table) {
            $table->id();
            $table->string('feature_slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activation_path_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('step_type');
            $table->json('criteria');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['activation_path_id', 'order']);
        });

        Schema::create('step_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activation_step_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_value', 10, 2)->default(0);
            $table->decimal('target_value', 10, 2)->default(1);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('activation_step_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_progress');
        Schema::dropIfExists('activation_steps');
        Schema::dropIfExists('activation_paths');
    }
};
