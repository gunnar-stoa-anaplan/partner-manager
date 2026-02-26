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
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enablement_session_id')->unique()->constrained('enablement_sessions')->cascadeOnDelete();
            $table->string('zoom_meeting_id');
            $table->string('zoom_uuid')->nullable();
            $table->string('host_email');
            $table->string('join_url', 512);
            $table->text('start_url')->nullable();
            $table->string('password', 50)->nullable();
            $table->string('topic');
            $table->text('agenda')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->string('timezone', 64)->default('UTC');
            $table->string('status')->default('scheduled');
            $table->json('settings')->nullable();
            $table->string('recording_url', 512)->nullable();
            $table->string('recording_password', 50)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('participants_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('zoom_meeting_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};
