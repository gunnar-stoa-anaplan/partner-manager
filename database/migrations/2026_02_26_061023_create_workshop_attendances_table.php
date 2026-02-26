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
        Schema::create('workshop_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zoom_meeting_id')->constrained('zoom_meetings')->cascadeOnDelete();
            $table->foreignId('enablement_registration_id')->nullable()->constrained('enablement_registrations')->nullOnDelete();
            $table->string('participant_email')->nullable();
            $table->string('participant_name');
            $table->string('zoom_user_id')->nullable();
            $table->dateTime('join_time')->nullable();
            $table->dateTime('leave_time')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedTinyInteger('attentiveness_score')->nullable();
            $table->string('source')->default('webhook');
            $table->timestamps();

            $table->index(['zoom_meeting_id', 'participant_email']);
            $table->index('enablement_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_attendances');
    }
};
