<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->foreignId('journey_participant_id')->nullable()->constrained('journey_participants')->nullOnDelete();
            $table->string('level', 16)->default('info');
            $table->string('event', 64);
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['journey_id', 'created_at']);
            $table->index(['journey_participant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_agent_logs');
    }
};
