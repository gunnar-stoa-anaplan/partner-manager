<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_pending_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->foreignId('journey_participant_id')->nullable()->constrained('journey_participants')->cascadeOnDelete();
            $table->string('action', 64);
            $table->text('description');
            $table->string('assigned_to', 64)->default('agent');
            $table->enum('status', ['pending', 'in_progress', 'complete', 'cancelled'])->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['journey_id', 'status']);
            $table->index(['journey_participant_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_pending_actions');
    }
};
