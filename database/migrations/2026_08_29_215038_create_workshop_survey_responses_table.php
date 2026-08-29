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
        Schema::create('workshop_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_survey_template_id')->constrained('workshop_survey_templates')->cascadeOnDelete();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->json('responses_json');
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->dateTime('submitted_at');
            $table->timestamps();

            $table->index(['workshop_survey_template_id', 'contact_id'], 'workshop_survey_responses_template_contact_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_survey_responses');
    }
};
