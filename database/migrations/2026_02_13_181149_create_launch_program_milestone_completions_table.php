<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_program_milestone_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('launch_program_participant_id');
            $table->unsignedBigInteger('launch_program_milestone_id');
            $table->timestamp('completed_at');
            $table->string('evidence_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('launch_program_participant_id', 'lp_mc_participant_fk')
                ->references('id')->on('launch_program_participants')
                ->cascadeOnDelete();

            $table->foreign('launch_program_milestone_id', 'lp_mc_milestone_fk')
                ->references('id')->on('launch_program_milestones')
                ->cascadeOnDelete();

            $table->unique(
                ['launch_program_participant_id', 'launch_program_milestone_id'],
                'lp_milestone_completion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_program_milestone_completions');
    }
};
