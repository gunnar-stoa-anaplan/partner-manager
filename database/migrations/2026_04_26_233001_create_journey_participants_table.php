<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('external_id')->nullable();
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('last_active_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['journey_id', 'current_stage']);
            $table->index(['journey_id', 'last_active_at']);
            $table->unique(['journey_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_participants');
    }
};
