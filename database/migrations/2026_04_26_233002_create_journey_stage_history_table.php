<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_participant_id')->constrained('journey_participants')->cascadeOnDelete();
            $table->unsignedTinyInteger('from_stage')->nullable();
            $table->unsignedTinyInteger('to_stage');
            $table->string('changed_by', 64);
            $table->text('note')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index('changed_at');
            $table->index(['journey_participant_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_stage_history');
    }
};
