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
        Schema::create('lab_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_model_id')->nullable()->constrained('anaplan_models')->nullOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_points_earned')->default(0);
            $table->unsignedInteger('total_points_possible')->default(0);
            $table->foreignId('enrolled_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lab_id', 'contact_id']);
            $table->index(['lab_id', 'status']);
            $table->index('partner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_participants');
    }
};
