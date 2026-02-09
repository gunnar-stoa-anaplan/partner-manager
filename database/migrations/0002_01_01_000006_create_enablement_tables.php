<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enablement_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['workshop', 'training', 'certification_exam', 'webinar', 'bootcamp', 'onboarding', 'hackathon', 'conference', 'other']);
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('prerequisites')->nullable();
            $table->string('target_audience')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->string('materials_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('enablement_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enablement_event_id')->constrained('enablement_events')->cascadeOnDelete();
            $table->string('title_override')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('timezone')->default('UTC');
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('instructor')->nullable();
            $table->string('geography')->nullable();
            $table->string('region')->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('summary')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['enablement_event_id', 'start_at']);
            $table->index(['start_at', 'end_at']);
            $table->index('status');
            $table->index('geography');
        });

        Schema::create('enablement_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enablement_session_id')->constrained('enablement_sessions')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('nominated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['nominated', 'registered', 'confirmed', 'attended', 'no_show', 'cancelled'])->default('nominated');
            $table->dateTime('nominated_at')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('attended_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['enablement_session_id', 'contact_id']);
            $table->index(['partner_id', 'status']);
            $table->index('nominated_by');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enablement_registrations');
        Schema::dropIfExists('enablement_sessions');
        Schema::dropIfExists('enablement_events');
    }
};
